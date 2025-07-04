<?php

namespace App\DTO;

use App\Entity\Hangout;
use App\Entity\Spot;

class UpdateHangoutDTO
{
    public Hangout $hangout;
    public Spot $spot;
}
