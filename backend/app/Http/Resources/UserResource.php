<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'active' => $this->active,
            'role' => $this->role,
            'permissions' => $this->getAllPermissions()->pluck('name')->values(),
            'department_id' => $this->department_id,
            'establishment_id' => $this->establishment_id,
            'department' => $this->whenLoaded('department', fn () => $this->department === null ? null : [
                'id' => $this->department->id,
                'code' => $this->department->code,
                'name_fr' => $this->department->name_fr,
                'name_ar' => $this->department->name_ar,
            ]),
            'establishment' => $this->whenLoaded('establishment', fn () => $this->establishment === null ? null : [
                'id' => $this->establishment->id,
                'code' => $this->establishment->code,
                'name_fr' => $this->establishment->name_fr,
                'name_ar' => $this->establishment->name_ar,
            ]),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
        ];
    }
}
