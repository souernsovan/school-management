<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamType extends Model
{
    protected $fillable = ['name', 'sort_order'];

    // Tailwind palette — one entry per slot, cycling for extra types
    private static array $twPalette = [
        ['single' => 'bg-purple-100 text-purple-700', 'bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'ring' => 'ring-purple-200',  'badge_bg' => 'bg-purple-100',  'badge_text' => 'text-purple-700'],
        ['single' => 'bg-blue-100 text-blue-700',     'bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'ring' => 'ring-blue-200',    'badge_bg' => 'bg-blue-100',    'badge_text' => 'text-blue-700'],
        ['single' => 'bg-amber-100 text-amber-700',   'bg' => 'bg-amber-100',  'text' => 'text-amber-700',  'ring' => 'ring-amber-200',   'badge_bg' => 'bg-amber-100',   'badge_text' => 'text-amber-700'],
        ['single' => 'bg-red-100 text-red-700',       'bg' => 'bg-red-100',    'text' => 'text-red-700',    'ring' => 'ring-red-200',     'badge_bg' => 'bg-red-100',     'badge_text' => 'text-red-700'],
        ['single' => 'bg-teal-100 text-teal-700',     'bg' => 'bg-teal-100',   'text' => 'text-teal-700',   'ring' => 'ring-teal-200',    'badge_bg' => 'bg-teal-100',    'badge_text' => 'text-teal-700'],
        ['single' => 'bg-pink-100 text-pink-700',     'bg' => 'bg-pink-100',   'text' => 'text-pink-700',   'ring' => 'ring-pink-200',    'badge_bg' => 'bg-pink-100',    'badge_text' => 'text-pink-700'],
        ['single' => 'bg-indigo-100 text-indigo-700', 'bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'ring' => 'ring-indigo-200',  'badge_bg' => 'bg-indigo-100',  'badge_text' => 'text-indigo-700'],
        ['single' => 'bg-emerald-100 text-emerald-700','bg'=> 'bg-emerald-100','text' => 'text-emerald-700','ring' => 'ring-emerald-200', 'badge_bg' => 'bg-emerald-100', 'badge_text' => 'text-emerald-700'],
    ];

    // Hex palette for contexts that need raw CSS colours (e.g. SVG, inline style)
    private static array $hexPalette = [
        ['bg' => '#6366f1', 'light' => '#eef2ff', 'text' => '#4338ca'],
        ['bg' => '#0ea5e9', 'light' => '#e0f2fe', 'text' => '#0369a1'],
        ['bg' => '#f59e0b', 'light' => '#fef3c7', 'text' => '#b45309'],
        ['bg' => '#ef4444', 'light' => '#fee2e2', 'text' => '#b91c1c'],
        ['bg' => '#10b981', 'light' => '#d1fae5', 'text' => '#065f46'],
        ['bg' => '#ec4899', 'light' => '#fce7f3', 'text' => '#9d174d'],
        ['bg' => '#8b5cf6', 'light' => '#ede9fe', 'text' => '#5b21b6'],
        ['bg' => '#14b8a6', 'light' => '#ccfbf1', 'text' => '#0f766e'],
    ];

    // Returns [ 'Type Name' => [...palette slot] ] from DB order
    public static function tailwindMap(): array
    {
        try {
            $names = static::orderBy('sort_order')->orderBy('name')->pluck('name');
        } catch (\Throwable) {
            $names = collect();
        }
        $map = [];
        foreach ($names as $i => $name) {
            $map[$name] = self::$twPalette[$i % count(self::$twPalette)];
        }
        return $map;
    }

    public static function hexMap(): array
    {
        try {
            $names = static::orderBy('sort_order')->orderBy('name')->pluck('name');
        } catch (\Throwable) {
            $names = collect();
        }
        $map = [];
        foreach ($names as $i => $name) {
            $map[$name] = self::$hexPalette[$i % count(self::$hexPalette)];
        }
        return $map;
    }

    private static array $twDefault  = ['single' => 'bg-slate-100 text-slate-700', 'bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'ring' => 'ring-slate-200', 'badge_bg' => 'bg-slate-100', 'badge_text' => 'text-slate-600'];
    private static array $hexDefault = ['bg' => '#64748b', 'light' => '#f1f5f9', 'text' => '#334155'];

    public static function twFor(string $name): array
    {
        return static::tailwindMap()[$name] ?? self::$twDefault;
    }

    public static function hexFor(string $name): array
    {
        return static::hexMap()[$name] ?? self::$hexDefault;
    }

    protected static function booted(): void
    {
        static::creating(function (ExamType $type) {
            if (!$type->sort_order) {
                $type->sort_order = (static::max('sort_order') ?? 0) + 1;
            }
        });
    }
}
