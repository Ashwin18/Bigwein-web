<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropertyAttributeMasterSeeder extends Seeder
{
    public function run(): void
    {
        $masters = [
            'villa_type' => ['Villa Type', 'category', ['Independent Villa', 'Gated Community Villa', 'Duplex Villa', 'Triplex Villa', 'Luxury Villa', 'Farm Villa']],
            'townhouse_type' => ['Townhouse Type', 'category', ['Independent Townhouse', 'Gated Townhouse', 'Duplex Townhouse', 'Row House', 'Luxury Townhouse']],
            'plot_type' => ['Plot Type', 'category', ['Residential Plot', 'Agricultural Land', 'Commercial Plot', 'Industrial Plot', 'Farm Land']],
            'commercial_type' => ['Commercial Type', 'category', ['Office Space', 'Shop / Showroom', 'Warehouse', 'Co-working Space', 'Factory / Industrial', 'Restaurant', 'Hotel']],
            'pg_type' => ['PG / Co-Living Type', 'category', ['Boys PG', 'Girls PG', 'Co-Living', 'Student Hostel', 'Working Professional PG']],
            'bhk_type' => ['BHK Type', 'global', ['1 BHK', '2 BHK', '3 BHK', '4 BHK', '5+ BHK']],
            'property_status' => ['Property Status', 'global', ['Ready to Move', 'Under Construction', 'New Launch']],
        ];

        DB::transaction(function () use ($masters) {
            $now = now();

            foreach (array_values(array_keys($masters)) as $groupOrder => $code) {
                [$name, $scope, $options] = $masters[$code];
                DB::table('property_attribute_groups')->insertOrIgnore([[
                    'code' => $code,
                    'name' => $name,
                    'input_type' => 'single_select',
                    'scope' => $scope,
                    'is_active' => true,
                    'sort_order' => ($groupOrder + 1) * 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]]);
                $groupId = DB::table('property_attribute_groups')->where('code', $code)->value('id');
                foreach ($options as $optionOrder => $optionName) {
                    $value = Str::of($optionName)->lower()->replace('+', ' plus ')->slug('_')->toString();
                    DB::table('property_attribute_options')->insertOrIgnore([[
                        'group_id' => $groupId,
                        'value' => $value,
                        'name' => $optionName,
                        'is_active' => true,
                        'sort_order' => ($optionOrder + 1) * 10,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]]);
                }
            }

            $this->seedCategoryMappings($now);
        });
    }

    private function seedCategoryMappings($now): void
    {
        $groups = DB::table('property_attribute_groups')->pluck('id', 'code');
        $matchedProfiles = [];

        foreach (DB::table('categories')->select('id', 'category')->get() as $category) {
            $profile = $this->categoryProfile((string) $category->category);
            $codes = match ($profile) {
                'villa' => ['villa_type', 'bhk_type', 'property_status'],
                'townhouse' => ['townhouse_type', 'bhk_type', 'property_status'],
                'plot' => ['plot_type', 'property_status'],
                'commercial' => ['commercial_type', 'property_status'],
                'pg' => ['pg_type', 'property_status'],
                default => [],
            };

            if (!$codes) continue;
            $matchedProfiles[$profile] = true;

            foreach ($codes as $order => $code) {
                if (!$groups->has($code)) continue;
                DB::table('property_attribute_category_map')->insertOrIgnore([[
                    'group_id' => $groups[$code],
                    'category_id' => $category->id,
                    'is_required' => true,
                    'sort_order' => ($order + 1) * 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]]);
            }
        }

        foreach (['villa', 'townhouse', 'plot', 'commercial', 'pg'] as $profile) {
            if (!isset($matchedProfiles[$profile])) {
                $this->command?->warn("Property Attribute Masters: no category matched the {$profile} profile; mapping skipped.");
            }
        }
    }

    private function categoryProfile(string $name): ?string
    {
        $name = Str::of($name)->lower()->replace(['_', '-'], ' ')->squish()->toString();
        if (Str::contains($name, ['townhouse', 'town house', 'row house'])) return 'townhouse';
        if (Str::contains($name, ['villa'])) return 'villa';
        if (Str::contains($name, ['plot', 'land', 'agricultural'])) return 'plot';
        if (Str::contains($name, ['commercial', 'office', 'shop', 'showroom', 'warehouse', 'industrial', 'factory'])) return 'commercial';
        if (Str::contains($name, ['pg house', 'pg', 'hostel', 'co-living', 'coliving'])) return 'pg';
        return null;
    }
}
