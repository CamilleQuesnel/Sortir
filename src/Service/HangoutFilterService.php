<?php

namespace App\Service;

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

    public function filterCampus(Campus|null $campus)
    {

            // Trouver le campus par son nom
        if($campus) {
            $campusFind = $this->campusRepository->findOneBy(['id' => $campus]);
            if ($campusFind) {
                $this->hangoutRepository->
            }

        }
        if (!$campus) {
            dd("Campus not found");
            return [];
        }

        // Récupérer les hangouts liés à ce campus
        return $this->hangoutRepository->findBy(['campus' => $campus]);
    }
}

