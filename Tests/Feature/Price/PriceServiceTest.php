<?php

namespace Tests\Feature\Price;

use Redoy\FlyHub\Facades\FlyHub;
use Tests\TestCase;
use Mockery;

class PriceServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Cache::flush();
    }

    public function test_basic_functionality()
    {
        // TODO: Implement test
        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
