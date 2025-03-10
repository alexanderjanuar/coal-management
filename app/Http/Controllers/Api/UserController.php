<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request)
    {
        try {
            $users = User::with([
                'roles', 
                'userClients.client', 
                'userProjects.project', 
                'submittedDocuments', 
                'comments',
                'activities'
            ])->get();

            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No users found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => UserResource::collection($users)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user data',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function detail(User $user)
    {
        try {
            $user->load(['roles', 'userClients.client', 'userProjects.project', 'submittedDocuments', 'comments','activities']);
            
            return response()->json([
                'success' => true,
                'message' => 'User details retrieved successfully',
                'data' => new UserResource($user)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user details',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
