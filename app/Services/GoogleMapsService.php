<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://maps.googleapis.com/maps/api/distancematrix/json';

    public function __construct()
    {
        $this->apiKey = config('services.google.maps_key', env('GOOGLE_MAPS_API_KEY', ''));
    }

    /**
     * Get distance between two points in kilometers
     */
    public function getDistance(float $lat1, float $lng1, float $lat2, float $lng2): ?float
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            $response = Http::get($this->baseUrl, [
                'origins'      => "{$lat1},{$lng1}",
                'destinations' => "{$lat2},{$lng2}",
                'key'          => $this->apiKey,
                'mode'         => 'driving',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'OK' && isset($data['rows'][0]['elements'][0]['distance']['value'])) {
                    // distance value is in meters, convert to km
                    return $data['rows'][0]['elements'][0]['distance']['value'] / 1000;
                }
            }
            
            Log::warning('Google Maps Distance Matrix API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Google Maps Service Exception', ['message' => $e->getMessage()]);
        }

        return null;
    }
}
