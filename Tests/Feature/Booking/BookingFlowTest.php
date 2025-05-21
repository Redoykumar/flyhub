<?php

namespace Tests\Feature\Booking;

use Illuminate\Support\Str;
use Tests\TestCase;
use Redoy\FlyHub\Facades\FlyHub;

class BookingFlowTest extends TestCase
{
    public function test_full_booking_flow()
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

        // Step 3: Booking
        $start = microtime(true);
        $bookingResponse = FlyHub::book([
            'price_id' => $priceId,
            'passengers' => [
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'gender' => 'M',
                    'dob' => '1990-01-01',
                    'passport_number' => 'A12345678',
                    'passport_expiry' => '2030-12-31',
                    'nationality' => 'BD',
                    'passport_issued_country' => 'BD',
                    'type' => 'ADT',
                ],
            ],
            'contact' => [
                'email' => 'john.doe@example.com',
                'phone' => '+8801712345678',
            ],
        ]);
        $analytics['booking_duration'] = microtime(true) - $start;
        fwrite(STDOUT, "Booking response size: " . strlen(json_encode($bookingResponse)) . " bytes\n");

        $this->assertArrayHasKey('data', $bookingResponse);
        $bookingId = $bookingResponse['data']['id'] ?? null;
        $this->assertNotNull($bookingId);


        // Print summary analytics to STDOUT
        fwrite(STDOUT, "\n--- Booking Flow Analytics ---\n");
        foreach ($analytics as $step => $duration) {
            fwrite(STDOUT, ucfirst(str_replace('_', ' ', $step)) . ": " . number_format($duration, 3) . " seconds\n");
        }
        fwrite(STDOUT, "-----------------------------\n\n");
    }
}
