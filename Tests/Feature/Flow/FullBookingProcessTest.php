<?php

namespace Tests\Feature\Flow;

use Tests\TestCase;
use Redoy\FlyHub\Facades\FlyHub;

class FullBookingProcessTest extends TestCase
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
        $totalDuration = 0;
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
        $totalDuration += $duration;

        $this->assertArrayHasKey('meta', $searchResponse, '❌ Missing meta in search response');
        $this->assertNotEmpty($searchResponse['data'], '❌ Search response data is empty');
        $searchId = $searchResponse['meta']['search_id'];
        $offerId = $searchResponse['data'][0]['id'];
        echo "✅ Search done in {$duration}s | Offers Found: " . count($searchResponse['data']) . "\n";
        echo "🔢 Search ID: {$searchId}, First Offer ID: {$offerId}\n\n";

        // STEP 2: Price Check
        $start = microtime(true);
        echo "💰 STEP 2: Price Check\n";
        $priceResponse = FlyHub::price([
            'search_id' => $searchId,
            'offer_id' => $offerId,
        ])->display();
        $duration = round(microtime(true) - $start, 2);
        $totalDuration += $duration;

        $this->assertNotEmpty($priceResponse['data'], '❌ Price response data is empty');
        $priceId = $priceResponse['data'][0]['id'];
        $priceAmount = $priceResponse['data'][0]['price']['amount'] ?? 'N/A';
        echo "✅ Price fetched in {$duration}s | Price ID: {$priceId}, Amount: {$priceAmount}\n\n";

        // STEP 3: Booking
        $start = microtime(true);
        echo "🧾 STEP 3: Booking\n";
        $bookingResponse = FlyHub::book([
            'price_id' => $priceId,
            'passengers' => $data['passenger_details'],
            'contact' => [
                'email' => 'john.doe@example.com',
                'phone' => '+8801712345678',
            ],
        ]);
        $duration = round(microtime(true) - $start, 2);
        $totalDuration += $duration;

        $this->assertArrayHasKey('data', $bookingResponse, '❌ Booking response missing data');
        $bookingId = $bookingResponse['data']['id'] ?? null;
        $this->assertNotNull($bookingId, '❌ Booking ID is null');
        echo "✅ Booking completed in {$duration}s | Booking ID: {$bookingId}\n\n";

        // STEP 4: Payment
        $start = microtime(true);
        echo "💳 STEP 4: Payment\n";
        $paymentResponse = FlyHub::pay([
            'booking_id' => $bookingId,
            'payment_method' => 'card',
            'payment_descriptions' => 'Test payment',
        ]);
        $duration = round(microtime(true) - $start, 2);
        $totalDuration += $duration;

        $this->assertArrayHasKey('status', $paymentResponse, '❌ Payment response missing status');
        $this->assertEquals('success', $paymentResponse['status'], '❌ Payment status is not success');
        echo "✅ Payment processed in {$duration}s | Status: {$paymentResponse['status']}\n";

        echo "🎉 Test passed successfully for Booking ID: {$bookingId}\n";
        echo "===============================\n\n";
        echo "⏱️ Total test duration: {$totalDuration}s\n";
    }


}
