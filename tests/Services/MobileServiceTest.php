<?php

namespace App\Tests\Services;

use App\Services\MobileService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class MobileServiceTest extends TestCase
{
    private MobileService $mobileService;

    protected function setUp(): void
    {
        $this->mobileService = new MobileService();
    }

    public function testIsMobileReturnsTrueForMobileUserAgent(): void
    {
        $request = new Request([], [], [], [], [], ['HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_1_2 like Mac OS X)']);
        self::assertTrue($this->mobileService->isMobile($request));
    }

    public function testIsMobileReturnsFalseForDesktopUserAgent(): void
    {
        $request = new Request([], [], [], [], [], ['HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)']);
        self::assertFalse($this->mobileService->isMobile($request));
    }
}
