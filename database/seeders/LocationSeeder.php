<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $divisionsData = $this->getJsonData('divisions.json');
        $districtsData = $this->getJsonData('districts.json');
        $thanasData = $this->getJsonData('upazilas.json');

        if (empty($divisionsData)) {
            $this->command->warn('Divisions JSON data not found or invalid.');
            return;
        }

        DB::transaction(function () use ($divisionsData, $districtsData, $thanasData) {
            
            // 1. Seed Divisions
            $divisionsMap = [];
            foreach ($divisionsData as $div) {
                $division = Division::firstOrCreate([
                    'id' => $div['id']
                ], [
                    'name' => trim($div['name'])
                ]);
                $divisionsMap[$div['id']] = $division->id;
            }

            // 2. Seed Districts
            $districtsMap = [];
            foreach ($districtsData as $dist) {
                if (!isset($divisionsMap[$dist['division_id']])) continue;

                $distName = trim($dist['name']);
                $deliveryCharge = 120; // default others
                
                if (strtolower($distName) === 'dhaka') {
                    $deliveryCharge = 60;
                } elseif (strtolower($distName) === 'kushtia') {
                    $deliveryCharge = 70;
                }

                $district = District::updateOrCreate([
                    'id' => $dist['id']
                ], [
                    'division_id' => $divisionsMap[$dist['division_id']],
                    'name' => $distName,
                    'delivery_charge' => $deliveryCharge,
                ]);
                $districtsMap[$dist['id']] = $district->id;
            }

            // 3. Seed Thanas
            foreach ($thanasData as $thana) {
                if (!isset($districtsMap[$thana['district_id']])) continue;

                // We don't have postal codes in JSON, so we leave it as null
                Thana::updateOrCreate([
                    'id' => $thana['id']
                ], [
                    'district_id' => $districtsMap[$thana['district_id']],
                    'name' => trim($thana['name']),
                    'postal_code' => null
                ]);
            }
            
        });
        
        $this->command->info('Bangladesh locations seeded successfully!');
    }

    private function getJsonData($filename)
    {
        $path = database_path("seeders/data/bd-locations/" . $filename);
        if (!File::exists($path)) {
            return [];
        }

        $json = json_decode(File::get($path), true);
        
        if (is_array($json)) {
            foreach ($json as $item) {
                if (isset($item['type']) && $item['type'] === 'table' && isset($item['data'])) {
                    return $item['data'];
                }
            }
        }
        
        return [];
    }
}
