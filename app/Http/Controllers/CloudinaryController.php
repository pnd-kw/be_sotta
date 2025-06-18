<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CloudinaryController extends Controller
{
    public function getCloudinaryUsage()
    {
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

        $response = Http::withBasicAuth($apiKey, $apiSecret)
            ->get("https://api.cloudinary.com/v1_1/{$cloudName}/usage");

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Failed to fetch usage'], 500);
    }
}
