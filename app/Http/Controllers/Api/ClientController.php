<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        try {
            $clients = Client::with(['userClients', 'projects'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Clients retrieved successfully',
                'data' => ClientResource::collection($clients)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving clients',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Client $client)
    {
        try {
            $client->load(['userClients', 'projects']);
            
            return response()->json([
                'success' => true,
                'message' => 'Client details retrieved successfully',
                'data' => new ClientResource($client)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving client details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
