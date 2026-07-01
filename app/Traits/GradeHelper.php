<?php

namespace App\Traits;

trait GradeHelper
{
    /**
     * Canonical grade scale used everywhere in the system.
     * A+ ≥ 95 | A ≥ 90 | B+ ≥ 85 | B ≥ 80 | C ≥ 70 | D ≥ 60 | E ≥ 50 | F < 50
     */
    public static function gradeFromPct(float|null $pct): string
    {
        if ($pct === null) return '—';
        return match (true) {
            $pct >= 95 => 'A+',
            $pct >= 90 => 'A',
            $pct >= 85 => 'B+',
            $pct >= 80 => 'B',
            $pct >= 70 => 'C',
            $pct >= 60 => 'D',
            $pct >= 50 => 'E',
            default    => 'F',
        };
    }

    public static function gradeColor(string $grade): string
    {
        return match ($grade) {
            'A+', 'A' => 'emerald',
            'B+', 'B' => 'blue',
            'C'       => 'teal',
            'D'       => 'yellow',
            'E'       => 'orange',
            'F'       => 'red',
            default   => 'slate',
        };
    }

    public static function gradeTailwind(string $grade): string
    {
        return match ($grade) {
            'A+', 'A' => 'bg-emerald-100 text-emerald-700',
            'B+', 'B' => 'bg-blue-100 text-blue-700',
            'C'       => 'bg-teal-100 text-teal-700',
            'D'       => 'bg-yellow-100 text-yellow-700',
            'E'       => 'bg-orange-100 text-orange-700',
            'F'       => 'bg-red-100 text-red-700',
            default   => 'bg-slate-100 text-slate-600',
        };
    }
}
