<?php

namespace App\Repositories\Location;

use App\Models\District;

class DistrictRepository
{
    public function all()
    {
        return District::with('division')->get();
    }

    public function getByDivision($divisionId)
    {
        return District::with('division')->where('division_id', $divisionId)->get();
    }

    public function getByDivisionMinimal($divisionId)
    {
        return District::select('id', 'name', 'division_id', 'delivery_charge')
            ->where('division_id', $divisionId)
            ->get();
    }

    public function find($id)
    {
        return District::with('division')->findOrFail($id);
    }

    public function create(array $data)
    {
        return District::create($data);
    }

    public function update($id, array $data)
    {
        $district = $this->find($id);
        $district->update($data);
        return $district;
    }

    public function delete($id)
    {
        return $this->find($id)->delete();
    }
}
