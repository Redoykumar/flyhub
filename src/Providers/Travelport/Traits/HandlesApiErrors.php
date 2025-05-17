<?php

namespace Redoy\Flyhub\Providers\Travelport\Traits;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use RuntimeException;

trait HandlesApiErrors
{
    protected function handleResponseErrors(Response $response): void
    {
        Log::debug('Travelport raw response', ['body' => $response->json()]);

        if (!$response->successful()) {
            throw new RuntimeException("HTTP Error: {$response->status()} - {$response->body()}", $response->status());
        }

        $body = $response->json();

        foreach ($body as $parentKey => $content) {
            if (
                isset($content['Result']['Error']) &&
                is_array($content['Result']['Error']) &&
                !empty($content['Result']['Error'][0]['Message'])
            ) {
                $error = $content['Result']['Error'][0];

                $message = $error['Message'] ?? 'Unknown error';
                $source = $error['SourceID'] ?? 'Unknown Source';
                $code = $error['SourceCode'] ?? '0000';

                throw new RuntimeException("API Error: {$message} (Source: {$source}, Code: {$code})", 200);
            }
        }
    }


    protected function safeExecute(callable $callback): Response
    {
        try {
            $response = $callback();

            $this->handleResponseErrors($response);

            return $response;
        } catch (\Throwable $e) {
            Log::error('Travelport API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
