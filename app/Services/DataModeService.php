<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * BigWein global dataset selector.
 *
 * demo_mode_enabled = 1  => demo_seed records ONLY
 * demo_mode_enabled = 0  => non-demo/live records ONLY
 */
class DataModeService
{
    public const DEMO_TAG = 'demo_seed';

    public static function isDemo(): bool
    {
        try {
            $value = DB::table('settings')->where('type', 'demo_mode_enabled')->value('data');
            return (string) $value === '1';
        } catch (\Throwable $e) {
            // Production-safe default: never expose seed data when the setting cannot be read.
            return false;
        }
    }

    public static function mode(): string
    {
        return self::isDemo() ? 'demo' : 'live';
    }

    public static function label(): string
    {
        return self::isDemo() ? 'DEMO SEED DATA' : 'LIVE DATA';
    }

    /** Apply the current global mode to a meta_keywords column. */
    public static function applySeedScope($query, string $column = 'meta_keywords')
    {
        if (self::isDemo()) {
            return $query->where($column, self::DEMO_TAG);
        }

        return $query->where(function ($q) use ($column) {
            $q->whereNull($column)
              ->orWhere($column, '!=', self::DEMO_TAG);
        });
    }

    /** Scope a property query and optionally keep only normal property listings. */
    public static function applyPropertyScope($query, string $alias = 'propertys', bool $propertyOnly = false)
    {
        self::applySeedScope($query, $alias . '.meta_keywords');

        if ($propertyOnly) {
            $query->where(function ($q) use ($alias) {
                $q->whereNull($alias . '.listing_type')
                  ->orWhere($alias . '.listing_type', 'property');
            });
        }

        return $query;
    }

    public static function applyProjectScope($query, string $alias = 'projects')
    {
        return self::applySeedScope($query, $alias . '.meta_keywords');
    }

    /** Customer IDs connected to properties in the currently selected dataset. */
    public static function ownerIdsForCurrentMode(): array
    {
        $q = DB::table('propertys')
            ->where('added_by', '!=', 0)
            ->where(function ($x) {
                $x->whereNull('listing_type')->orWhere('listing_type', 'property');
            });

        self::applySeedScope($q, 'meta_keywords');

        return $q->distinct()->pluck('added_by')->filter()->map(fn ($id) => (int) $id)->values()->all();
    }
}
