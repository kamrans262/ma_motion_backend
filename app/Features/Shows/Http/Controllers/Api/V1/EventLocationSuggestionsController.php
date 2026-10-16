<?php

namespace App\Features\Shows\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

final class EventLocationSuggestionsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'input' => ['required', 'string', 'min:2', 'max:120'],
        ]);

        $apiKey = trim((string) config('services.google_places.api_key', ''));
        if ($apiKey === '') {
            return ApiResponse::error('Event location suggestions are unavailable.', 503, code: 'PLACES_NOT_CONFIGURED');
        }

        try {
            $response = Http::acceptJson()
                ->timeout(6)
                ->withHeaders([
                    'X-Goog-Api-Key' => $apiKey,
                    'X-Goog-FieldMask' => 'suggestions.placePrediction.text.text',
                ])
                ->post('https://places.googleapis.com/v1/places:autocomplete', [
                    'input' => trim($validated['input']),
                    'includeQueryPredictions' => false,
                ]);
        } catch (ConnectionException $exception) {
            return ApiResponse::error('Location suggestions are temporarily unavailable.', 502);
        }

        if (! $response->successful()) {
            return ApiResponse::error('Location suggestions are temporarily unavailable.', 502);
        }

        $suggestions = [];
        foreach ($response->json('suggestions', []) as $suggestion) {
            $description = data_get($suggestion, 'placePrediction.text.text');
            if (is_string($description) && trim($description) !== '') {
                $suggestions[] = ['description' => trim($description)];
            }
        }

        return ApiResponse::success(
            data: array_slice($suggestions, 0, 5),
            message: 'Event location suggestions retrieved successfully.',
        );
    }
}
