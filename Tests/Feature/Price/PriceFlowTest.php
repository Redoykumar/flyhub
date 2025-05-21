<?php

namespace Tests\Feature\Price;

use Redoy\FlyHub\Facades\FlyHub;
use Tests\TestCase;
use Mockery;

class PriceFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Cache::flush();
    }

    public function test_basic_functionality()
    {
        $analytics = [];

        // Step 1: Flight Search
        $searchData = [
            'trip_type' => 'one-way',
            'passengers' => [
                'adults' => 1,
                'children' => 0,
                'infants' => 0,
            ],
            'segments' => [
                [
                    'date' => now()->addDays(30)->toDateString(),
                    'from' => 'DAC',
                    'to' => 'DXB',
                ]
            ],
        ];

        $start = microtime(true);
        $searchResponse = FlyHub::search($searchData)->filters()->sort()->get();
        $analytics['search_duration'] = microtime(true) - $start;
        fwrite(STDOUT, "Search response size: " . strlen(json_encode($searchResponse)) . " bytes\n");

        $this->assertArrayHasKey('meta', $searchResponse);
        $this->assertArrayHasKey('data', $searchResponse);
        $this->assertNotEmpty($searchResponse['data']);

        $searchId = $searchResponse['meta']['search_id'];
        $offerId = $searchResponse['data'][0]['id'];

        // Step 2: Price Check
        $start = microtime(true);
        $priceResponse = FlyHub::price([
            'search_id' => $searchId,
            'offer_id' => $offerId,
        ])->display();
        $analytics['price_check_duration'] = microtime(true) - $start;
        fwrite(STDOUT, "Price response size: " . strlen(json_encode($priceResponse)) . " bytes\n");

        $this->assertNotEmpty($priceResponse['data']);
        $priceId = $priceResponse['data'][0]['id'];

      
        // Print summary analytics to STDOUT
        fwrite(STDOUT, "\n--- Booking Flow Analytics ---\n");
        foreach ($analytics as $step => $duration) {
            fwrite(STDOUT, ucfirst(str_replace('_', ' ', $step)) . ": " . number_format($duration, 3) . " seconds\n");
        }
        fwrite(STDOUT, "-----------------------------\n\n");
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
