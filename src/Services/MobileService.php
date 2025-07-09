<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;

class MobileService
{
    public function isMobile(Request $request): bool
    {
        $userAgent = $request->headers->get('User-Agent', '');
       $isMobile = stripos($userAgent, 'Mobile') !== false
            || stripos($userAgent, 'Android') !== false
            || stripos($userAgent, 'iPhone') !== false
            || stripos($userAgent, 'iPad') !== false;
//       return $isMobile;
        return true; //TODO Simulation du mobile
    }
}
