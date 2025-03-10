<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        try {
            $projects = Project::with(['client', 'userProject'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Projects retrieved successfully',
                'data' => ProjectResource::collection($projects)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving projects',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Project $project)
    {
        try {
            $project->load(['client', 'userProject']);
            
            return response()->json([
                'success' => true,
                'message' => 'Project details retrieved successfully',
                'data' => new ProjectResource($project)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
