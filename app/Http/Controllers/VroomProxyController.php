<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VroomProxyController extends Controller
{
    public function optimize(Request $request)
    {
        try {
            $vroomEndpoint = 'http://147.135.252.51:3000';

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($vroomEndpoint, $request->all());

            return response()->json($response->json(), $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to connect to Vroom API',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
