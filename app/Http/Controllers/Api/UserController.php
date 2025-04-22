<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $request->headers->set('Accept', 'application/json');
            return $next($request);
        });
    }

    public function show(Request $request)
    {
        try {
            // Validate date parameters if provided
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $query = User::with([
                'roles', 
                'userClients.client', 
                'userProjects.project', 
                'submittedDocuments', 
                'comments',
            ]);

            // Add activities with date filtering
            $query->with(['activities' => function($query) use ($request) {
                if ($request->has('start_date')) {
                    $query->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->has('end_date')) {
                    $query->whereDate('created_at', '<=', $request->end_date);
                }
                $query->latest(); // Order by most recent first
            }]);

            $users = $query->get();

            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No users found',
                    'data' => []
                ], 404)->header('Content-Type', 'application/json');
            }

            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => UserResource::collection($users)
            ])->header('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user data',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    public function detail(Request $request, User $user = null)
    {
        try {
            // Validate parameters
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'per_page' => 'nullable|integer|min:1|max:100',
                'email' => 'nullable|email'
            ]);

            // If email is provided, try to find user by email
            if ($request->has('email')) {
                $user = User::where('email', $request->email)->first();
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found with provided email',
                        'data' => null
                    ], 404)->header('Content-Type', 'application/json');
                }
            }

            // Ensure we have a valid user
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null
                ], 404)->header('Content-Type', 'application/json');
            }

            // Load basic relationships
            $user->load(['roles', 'userClients.client', 'userProjects.project', 'submittedDocuments', 'comments']);
            
            // Load activities with date filtering and causer_id filter
            $user->load(['activities' => function($query) use ($request, $user) {
                $query->where('causer_id', $user->id)  // Only get activities performed by this user
                      ->where('causer_type', 'App\Models\User');
                      
                if ($request->has('start_date')) {
                    $query->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->has('end_date')) {
                    $query->whereDate('created_at', '<=', $request->end_date);
                }
                $query->latest();
            }]);
            
            return response()->json([
                'success' => true,
                'message' => 'User details retrieved successfully',
                'data' => new UserResource($user)
            ])->header('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user details',
                'error' => $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }
}
