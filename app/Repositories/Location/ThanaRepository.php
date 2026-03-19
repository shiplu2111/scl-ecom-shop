<?php

namespace App\Repositories\Location;

use App\Models\Thana;

class ThanaRepository
{
    public function all()
    {
        return Thana::with('district', 'district.division')->get();
    }

    public function getByDistrict($districtId)
    {
        return Thana::with('district', 'district.division')->where('district_id', $districtId)->get();
    }

    public function getByDistrictMinimal($districtId)
    {
        return Thana::select('id', 'name', 'district_id', 'postal_code')
            ->where('district_id', $districtId)
            ->get();
    }

    public function find($id)
    {
        return Thana::with('district', 'district.division')->findOrFail($id);
    }

    public function create(array $data)
    {
        return Thana::create($data);
    }

    public function update($id, array $data)
    {
        $thana = $this->find($id);
        $thana->update($data);
        return $thana;
    }

    public function delete($id)
    {
        return $this->find($id)->delete();
    }
}
