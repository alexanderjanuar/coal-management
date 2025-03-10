<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index()
    {
        try {
            $activities = Activity::with('causer')->latest()->get();

            return response()->json([
                'success' => true,
                'message' => 'Activities retrieved successfully',
                'data' => ActivityResource::collection($activities)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving activities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Activity $activity)
    {
        try {
            $activity->load('causer');
            
            return response()->json([
                'success' => true,
                'message' => 'Activity details retrieved successfully',
                'data' => new ActivityResource($activity)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving activity details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
