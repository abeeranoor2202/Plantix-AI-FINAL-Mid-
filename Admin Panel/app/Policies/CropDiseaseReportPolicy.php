<?php

namespace App\Policies;

use App\Models\CropDiseaseReport;
use App\Models\User;

class CropDiseaseReportPolicy
{
    public function view(User $user, CropDiseaseReport $report): bool
    {
        return $user->isAdmin() || $user->id === $report->user_id;
    }

    /**
     * Only admins/experts can assign diseases.
     */
    public function assignDisease(User $user, CropDiseaseReport $report): bool
    {
        return $user->isAdmin() || ($user->expert()->exists());
    }
}
