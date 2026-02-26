<?php

namespace App\Policies;

use App\Models\CropRecommendation;
use App\Models\User;

class CropRecommendationPolicy
{
    /**
     * Admins can view/manage any. Users can only view their own.
     */
    public function view(User $user, CropRecommendation $recommendation): bool
    {
        return $user->isAdmin() || $user->id === $recommendation->user_id;
    }

    public function delete(User $user, CropRecommendation $recommendation): bool
    {
        return $user->isAdmin() || $user->id === $recommendation->user_id;
    }
}
