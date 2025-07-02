<?php

namespace App\DTO;

use App\Entity\Campus;
use DateTimeInterface;

class HangoutFilterDTO
{
    public ?Campus $campus = null;
    public ?string $outputNameContains = null;
    public ?DateTimeInterface $dateFrom = null;
    public ?DateTimeInterface $dateTo = null;
    public bool $isOrganizer = false;
    public bool $isRegistered = false;
    public bool $isNotRegistered = false;
    public bool $isPast = false;

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): void
    {
        $this->campus = $campus;
    }

    public function getOutputNameContains(): ?string
    {
        return $this->outputNameContains;
    }

    public function setOutputNameContains(?string $outputNameContains): void
    {
        $this->outputNameContains = $outputNameContains;
    }

    public function getDateFrom(): ?DateTimeInterface
    {
        return $this->dateFrom;
    }

    public function setDateFrom(?DateTimeInterface $dateFrom): void
    {
        $this->dateFrom = $dateFrom;
    }

    public function getDateTo(): ?DateTimeInterface
    {
        return $this->dateTo;
    }

    public function setDateTo(?DateTimeInterface $dateTo): void
    {
        $this->dateTo = $dateTo;
    }

    public function isOrganizer(): bool
    {
        return $this->isOrganizer;
    }

    public function setIsOrganizer(bool $isOrganizer): void
    {
        $this->isOrganizer = $isOrganizer;
    }

    public function isRegistered(): bool
    {
        return $this->isRegistered;
    }

    public function setIsRegistered(bool $isRegistered): void
    {
        $this->isRegistered = $isRegistered;
    }

    public function isNotRegistered(): bool
    {
        return $this->isNotRegistered;
    }

    public function setIsNotRegistered(bool $isNotRegistered): void
    {
        $this->isNotRegistered = $isNotRegistered;
    }

    public function isPast(): bool
    {
        return $this->isPast;
    }

    public function setIsPast(bool $isPast): void
    {
        $this->isPast = $isPast;
    }



}
