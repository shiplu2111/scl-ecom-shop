<?php

namespace App\Services\Location;

use App\Repositories\Location\DivisionRepository;
use App\Repositories\Location\DistrictRepository;
use App\Repositories\Location\ThanaRepository;

class LocationService
{
    protected $divisionRepo;
    protected $districtRepo;
    protected $thanaRepo;

    public function __construct(
        DivisionRepository $divisionRepo,
        DistrictRepository $districtRepo,
        ThanaRepository $thanaRepo
    ) {
        $this->divisionRepo = $divisionRepo;
        $this->districtRepo = $districtRepo;
        $this->thanaRepo = $thanaRepo;
    }

    // --- Divisions ---
    public function getAllDivisions() {
        return $this->divisionRepo->all();
    }

    public function getDivision($id) {
        return $this->divisionRepo->find($id);
    }

    public function createDivision(array $data) {
        return $this->divisionRepo->create($data);
    }

    public function updateDivision($id, array $data) {
        return $this->divisionRepo->update($id, $data);
    }

    public function deleteDivision($id) {
        $division = $this->divisionRepo->find($id);
        if ($division->districts()->count() > 0) {
            throw new \RuntimeException("Cannot delete division: It contains districts. Delete the districts first.");
        }
        return $division->delete();
    }

    // --- Districts ---
    public function getAllDistricts($divisionId = null) {
        if ($divisionId) {
            return $this->districtRepo->getByDivision($divisionId);
        }
        return $this->districtRepo->all();
    }

    public function getMinimalDistricts($divisionId = null) {
        if (!$divisionId) {
            return collect([]);
        }
        return $this->districtRepo->getByDivisionMinimal($divisionId);
    }

    public function getDistrict($id) {
        return $this->districtRepo->find($id);
    }

    public function createDistrict(array $data) {
        return $this->districtRepo->create($data);
    }

    public function updateDistrict($id, array $data) {
        return $this->districtRepo->update($id, $data);
    }

    public function deleteDistrict($id) {
        $district = $this->districtRepo->find($id);
        if ($district->thanas()->count() > 0) {
            throw new \RuntimeException("Cannot delete district: It contains thanas. Delete the thanas first.");
        }
        return $district->delete();
    }

    // --- Thanas ---
    public function getAllThanas($districtId = null) {
        if ($districtId) {
            return $this->thanaRepo->getByDistrict($districtId);
        }
        return $this->thanaRepo->all();
    }

    public function getMinimalThanas($districtId = null) {
        if (!$districtId) {
            return collect([]);
        }
        return $this->thanaRepo->getByDistrictMinimal($districtId);
    }

    public function getThana($id) {
        return $this->thanaRepo->find($id);
    }

    public function createThana(array $data) {
        return $this->thanaRepo->create($data);
    }

    public function updateThana($id, array $data) {
        return $this->thanaRepo->update($id, $data);
    }

    public function deleteThana($id) {
        return $this->thanaRepo->delete($id);
    }
}
