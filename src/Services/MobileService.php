<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;

class MobileService
{
    public function isMobile(Request $request): bool
    {
        $userAgent = $request->headers->get('User-Agent', '');
        return stripos($userAgent, 'Mobile') !== false;
//        return true; //TODO Simulation du mobile
    }
}
