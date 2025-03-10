<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->whenLoaded('roles', fn() => $this->roles->pluck('name')),
            'projects' => $this->whenLoaded('userProjects',
                fn() => ProjectResource::collection($this->userProjects->pluck('project'))),
            'recent_activities' => $this->whenLoaded('activities', function() {
                return $this->activities()
                    ->get()
                    ->map(function($activity) {
                        return [
                            'description' => $activity->description,
                            'subject_type' => $activity->subject_type,
                            'created_at' => $activity->created_at->diffForHumans(),
                            'properties' => $activity->properties
                        ];
                    });
            }),
            'statistics' => [
                'total_projects' => $this->userProjects->count() ?? 0,
                'total_clients' => $this->userClients->count() ?? 0,
                'total_documents' => $this->submittedDocuments->count() ?? 0,
                'total_comments' => $this->comments->count() ?? 0,
                'total_activities' => $this->activities()->count() ?? 0,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
