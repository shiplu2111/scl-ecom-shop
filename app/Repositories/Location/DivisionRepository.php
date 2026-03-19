<?php

namespace App\Repositories\Location;

use App\Models\Division;

class DivisionRepository
{
    public function all()
    {
        return Division::all();
    }

    public function find($id)
    {
        return Division::findOrFail($id);
    }

    public function create(array $data)
    {
        return Division::create($data);
    }

    public function update($id, array $data)
    {
        $division = $this->find($id);
        $division->update($data);
        return $division;
    }

    public function delete($id)
    {
        return $this->find($id)->delete();
    }
}
