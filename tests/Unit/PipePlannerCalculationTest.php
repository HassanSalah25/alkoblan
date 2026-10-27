<?php

namespace Tests\Unit;

use App\Services\PipePlanner\AnalysisSchema;
use App\Services\PipePlanner\QuantityCalculator;
use App\Services\PipePlanner\UnitConverter;
use Tests\TestCase;

class PipePlannerCalculationTest extends TestCase
{
    public function test_unit_conversion(): void
    {
        $this->assertSame(4.5, UnitConverter::toMeters(4500, 'mm'));
        $this->assertSame(4.5, UnitConverter::toMeters(450, 'cm'));
        $this->assertSame(3.048, UnitConverter::toMeters(10, 'ft'));
        $this->assertSame(0.254, UnitConverter::toMeters(10, 'in'));
        $this->assertNull(UnitConverter::toMeters(-1, 'm'));
        $this->assertNull(UnitConverter::toMeters(1, 'yard'));
        $this->assertSame(25, UnitConverter::parseDiameterMm('25mm'));
        $this->assertNull(UnitConverter::parseDiameterMm('1 inch'));
        $this->assertSame(4.0, UnitConverter::parseLengthMeters('4m / coil'));
    }

    public function test_waste_and_stock_lengths(): void
    {
        $c = new QuantityCalculator;
        $this->assertSame(10.0, $c->applyWaste(10.0, null));
        $this->assertSame(11.5, $c->applyWaste(10.0, 15));
        $this->assertNull($c->applyWaste(null, 10));
        $this->assertSame(3, $c->stockLengths(8.01, 4));
        $this->assertSame(2, $c->stockLengths(8.0, 4));
        $this->assertNull($c->stockLengths(8.0, null));
    }

    protected function seg(string $id, ?float $len, string $src, ?string $cid = null, ?int $dia = 20): array
    {
        return ['id' => $id, 'from' => 'a', 'to' => 'b', 'line' => 'branch', 'water' => 'cold', 'nominal_diameter_mm' => $dia,
            'length_m' => $len, 'length_source' => $src, 'customer_distance_id' => $cid, 'status' => 'proposed'];
    }

    public function test_pipe_quantity_statuses(): void
    {
        $c = new QuantityCalculator;
        $items = $c->requirements([
            'routing_options' => [['segments' => [
                $this->seg('s1', 99, 'customer', 'd1'),
                $this->seg('s2', 99, 'customer', 'missing-id'), // unverifiable claim → estimate
                $this->seg('s3', null, 'unknown', null, 25),
            ]]],
            'fittings' => [],
        ], [['id' => 'd1', 'value' => 1234, 'unit' => 'mm']], 'm', null);

        $d20 = collect($items)->firstWhere('diameter_mm', 20);
        $this->assertSame(100.23, $d20['base_quantity']); // 1.234 + 99
        $this->assertSame('preliminary_estimate', $d20['quantity_status']);
        $d25 = collect($items)->firstWhere('diameter_mm', 25);
        $this->assertNull($d25['quantity']);
        $this->assertSame('cannot_calculate', $d25['quantity_status']);
    }

    public function test_partial_and_calculated_statuses(): void
    {
        $c = new QuantityCalculator;
        $items = $c->requirements([
            'routing_options' => [['segments' => [$this->seg('s1', null, 'customer', 'd1'), $this->seg('s2', null, 'unknown')]]],
            'fittings' => [['category' => 'elbow', 'water' => null, 'nominal_diameter_mm' => 20, 'secondary_diameter_mm' => null, 'quantity' => null, 'basis' => 'assumption', 'notes' => '']],
        ], [['id' => 'd1', 'value' => 2, 'unit' => 'm']], 'm', 10);

        $this->assertSame('partial', $items[0]['quantity_status']);
        $this->assertSame(2.2, $items[0]['quantity']);
        $this->assertSame('cannot_calculate', $items[1]['quantity_status']);
    }

    public function test_schema_rejects_incomplete_or_invalid_output(): void
    {
        $this->assertNull(AnalysisSchema::validate('not json')['data']);
        $this->assertNull(AnalysisSchema::validate('{"observations":[]}')['data']);
        $result = AnalysisSchema::validate(json_encode([
            'observations' => [], 'routing_options' => [], 'fittings' => [], 'product_suggestions' => [],
            'assumptions' => [], 'missing_information' => [], 'contradictions' => [], 'clarification_questions' => [],
            'safety_flags' => [], 'readiness' => 'certified_design',
        ]));
        $this->assertNull($result['data']);
    }

    public function test_schema_accepts_minimal_output_and_strips_extra_keys(): void
    {
        $result = AnalysisSchema::validate("```json\n".json_encode([
            'observations' => [], 'routing_options' => [], 'fittings' => [], 'product_suggestions' => [],
            'assumptions' => [], 'missing_information' => [], 'contradictions' => [], 'clarification_questions' => [],
            'safety_flags' => [], 'readiness' => 'needs_more_information', 'run_sql' => 'DROP TABLE users',
        ])."\n```");
        $this->assertNotNull($result['data']);
        $this->assertArrayNotHasKey('run_sql', $result['data']);
    }
}
