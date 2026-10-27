<?php

namespace App\Services\PipePlanner;

/**
 * All bill-of-materials arithmetic lives here, never in the model.
 *
 * Quantity statuses:
 *  - calculated            every input length came from a customer measurement
 *  - preliminary_estimate  at least one input is a model estimate or a route-derived count
 *  - partial               some segments have no length; the total is a lower bound
 *  - assumption            count is based on an assumption, not a derived route
 *  - cannot_calculate      not enough data for any quantity
 */
class QuantityCalculator
{
    /**
     * @param  array  $analysis  validated AnalysisSchema data
     * @param  array  $distances  customer distances: [['id'=>..., 'value'=>?float, 'unit'=>...], ...]
     * @return array<int, array> component requirements (no catalog matching yet)
     */
    public function requirements(array $analysis, array $distances, string $defaultUnit, ?float $wastePercent): array
    {
        $distanceMeters = [];
        foreach ($distances as $d) {
            if (! empty($d['id']) && isset($d['value']) && is_numeric($d['value'])) {
                $distanceMeters[$d['id']] = UnitConverter::toMeters((float) $d['value'], $d['unit'] ?? $defaultUnit);
            }
        }

        $items = [];
        $option = $analysis['routing_options'][0] ?? null;

        $groups = [];
        foreach ($option['segments'] ?? [] as $seg) {
            $key = $seg['water'].'|'.($seg['nominal_diameter_mm'] ?? 'x');
            $groups[$key] ??= ['water' => $seg['water'], 'diameter_mm' => $seg['nominal_diameter_mm'] ?? null, 'segments' => []];

            $customerId = $seg['customer_distance_id'] ?? null;
            if ($customerId !== null && isset($distanceMeters[$customerId])) {
                $length = $distanceMeters[$customerId];
                $source = 'customer';
            } elseif ($seg['length_source'] !== 'unknown' && $seg['length_m'] !== null) {
                // A "customer" claim without a valid reference is downgraded to an estimate.
                $length = round((float) $seg['length_m'], 3);
                $source = 'estimated';
            } else {
                $length = null;
                $source = 'unknown';
            }
            $groups[$key]['segments'][] = ['id' => $seg['id'], 'from' => $seg['from'], 'to' => $seg['to'], 'length_m' => $length, 'source' => $source];
        }

        foreach ($groups as $g) {
            $known = array_filter($g['segments'], fn ($s) => $s['length_m'] !== null);
            $unknownCount = count($g['segments']) - count($known);
            $base = count($known) ? round(array_sum(array_column($known, 'length_m')), 2) : null;

            if ($base === null) {
                $status = 'cannot_calculate';
            } elseif ($unknownCount > 0) {
                $status = 'partial';
            } elseif (collect($known)->contains('source', 'estimated')) {
                $status = 'preliminary_estimate';
            } else {
                $status = 'calculated';
            }

            $items[] = [
                'category' => 'pipe',
                'water' => $g['water'],
                'diameter_mm' => $g['diameter_mm'],
                'secondary_diameter_mm' => null,
                'base_quantity' => $base,
                'quantity' => $this->applyWaste($base, $wastePercent),
                'unit' => 'm',
                'quantity_status' => $status,
                'source' => $base === null ? 'insufficient_data'
                    : (collect($known)->contains('source', 'estimated') ? 'ai_estimate' : 'customer_measurement'),
                'derivation' => [
                    'segments' => $g['segments'],
                    'unknown_segments' => $unknownCount,
                    'waste_allowance_percent' => $wastePercent,
                ],
                'notes' => null,
            ];
        }

        $fittingGroups = [];
        foreach ($analysis['fittings'] ?? [] as $f) {
            $key = implode('|', [$f['category'], $f['water'] ?? '-', $f['nominal_diameter_mm'] ?? 'x', $f['secondary_diameter_mm'] ?? 'x', $f['basis']]);
            if (! isset($fittingGroups[$key])) {
                $fittingGroups[$key] = $f + ['notes_list' => []];
                $fittingGroups[$key]['quantity'] = $f['quantity'];
            } elseif ($f['quantity'] !== null) {
                $fittingGroups[$key]['quantity'] = ($fittingGroups[$key]['quantity'] ?? 0) + $f['quantity'];
            }
            if (! empty($f['notes'])) {
                $fittingGroups[$key]['notes_list'][] = $f['notes'];
            }
        }

        foreach ($fittingGroups as $f) {
            $qty = $f['quantity'] === null ? null : (float) $f['quantity'];
            $items[] = [
                'category' => $f['category'],
                'water' => $f['water'] ?? null,
                'diameter_mm' => $f['nominal_diameter_mm'],
                'secondary_diameter_mm' => $f['secondary_diameter_mm'],
                'base_quantity' => $qty,
                // Waste allowance applies to cut pipe lengths only; fittings are counted units.
                'quantity' => $qty,
                'unit' => 'pcs',
                'quantity_status' => $qty === null ? 'cannot_calculate' : ($f['basis'] === 'assumption' ? 'assumption' : 'preliminary_estimate'),
                'source' => $f['basis'] === 'assumption' ? 'ai_assumption' : 'ai_route_count',
                'derivation' => ['basis' => $f['basis']],
                'notes' => implode(' · ', array_unique($f['notes_list'])) ?: null,
            ];
        }

        return $items;
    }

    public function applyWaste(?float $base, ?float $wastePercent): ?float
    {
        if ($base === null) {
            return null;
        }
        if (! $wastePercent) {
            return $base;
        }

        return round($base * (1 + $wastePercent / 100), 2);
    }

    /** Number of whole stock lengths needed, when the catalog states the stock length. */
    public function stockLengths(?float $meters, ?float $stockLengthM): ?int
    {
        if ($meters === null || ! $stockLengthM || $stockLengthM <= 0) {
            return null;
        }

        return (int) ceil(round($meters / $stockLengthM, 6));
    }
}
