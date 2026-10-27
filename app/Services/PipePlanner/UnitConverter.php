<?php

namespace App\Services\PipePlanner;

class UnitConverter
{
    protected const TO_METERS = [
        'mm' => 0.001,
        'cm' => 0.01,
        'm' => 1.0,
        'in' => 0.0254,
        'ft' => 0.3048,
    ];

    public static function toMeters(?float $value, string $unit): ?float
    {
        if ($value === null || ! isset(self::TO_METERS[$unit]) || $value < 0) {
            return null;
        }

        return round($value * self::TO_METERS[$unit], 4);
    }

    /** Parse a catalog diameter label such as "25mm" / "25 mm" / "Ø25" into millimeters. */
    public static function parseDiameterMm(?string $label): ?int
    {
        if ($label === null) {
            return null;
        }
        if (preg_match('/(\d{2,3})\s*mm\b/i', $label, $m) || preg_match('/^\D{0,2}(\d{2,3})$/', trim($label), $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /** Parse a stock length spec such as "4m" or "4 m / coil" into meters. Ambiguous values return null. */
    public static function parseLengthMeters(?string $label): ?float
    {
        if ($label === null || ! preg_match('/(\d+(?:\.\d+)?)\s*m\b/i', $label, $m)) {
            return null;
        }

        return (float) $m[1];
    }
}
