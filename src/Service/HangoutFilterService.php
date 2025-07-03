<?php

namespace App\Service;

use App\DTO\HangoutFilterDTO;
use App\Entity\Campus;
use App\Repository\CampusRepository;
use App\Repository\HangoutRepository;

class HangoutFilterService
{

    private CampusRepository $campusRepository;
    private HangoutRepository $hangoutRepository;


    public function __construct(CampusRepository $campusRepository, HangoutRepository $hangoutRepository)
    {
        $this->campusRepository = $campusRepository;
        $this->hangoutRepository = $hangoutRepository;

    }

    public function filterHangouts(HangoutFilterDTO $filters, $user)
    {
        $test =$this->hangoutRepository->findByFilters($filters, $user);
//        dd($test);
        return  $test;
    }

}

