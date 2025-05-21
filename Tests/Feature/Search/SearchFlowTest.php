<?php

namespace Tests\Feature\Search;

use Redoy\FlyHub\Facades\FlyHub;
use Tests\TestCase;
use Mockery;

class SearchFlowTest extends TestCase
{
    public static function bookingDataProvider(): array
    {
        $jsonPath = __DIR__ . '/../../data/booking_test_data.json';

        if (!file_exists($jsonPath)) {
            fwrite(STDERR, "❌ Test data file not found at: {$jsonPath}\n");
            return [];
        }

        $jsonData = file_get_contents($jsonPath);
        $dataSets = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            fwrite(STDERR, "❌ JSON decode error: " . json_last_error_msg() . "\n");
            return [];
        }

        $result = [];
        foreach ($dataSets as $index => $data) {
            $result["case_{$index}"] = [$data];
        }

        return $result;
    }

    /**
     * @dataProvider bookingDataProvider
     */
    public function test_full_booking_flow(array $data): void
    {
        echo "\n===============================\n";
        echo "🚀 Starting Full Booking Flow Test\n";
        echo "From: {$data['segments'][0]['from']} → To: {$data['segments'][0]['to']}\n";
        echo "Trip Type: {$data['trip_type']}, Passengers: " . json_encode($data['passengers']) . "\n";
        echo "-------------------------------\n";

        // STEP 1: Flight Search
        $start = microtime(true);
        echo "🔎 STEP 1: Flight Search\n";
        $searchResponse = FlyHub::search([
            'trip_type' => $data['trip_type'],
            'passengers' => $data['passengers'],
            'segments' => $data['segments'],
        ])->filters()->sort()->get();
        $duration = round(microtime(true) - $start, 2);

        $this->assertArrayHasKey('meta', $searchResponse, '❌ Missing meta in search response');
        $this->assertNotEmpty($searchResponse['data'], '❌ Search response data is empty');
        $searchId = $searchResponse['meta']['search_id'];
        $offerId = $searchResponse['data'][0]['id'];
        echo "✅ Search done in {$duration}s | Offers Found: " . count($searchResponse['data']) . "\n";
        echo "🔢 Search ID: {$searchId}, First Offer ID: {$offerId}\n\n";

       
        echo "🎉 Test passed successfully for Booking ID: {$bookingId}\n";
        echo "===============================\n\n";
    }
}
