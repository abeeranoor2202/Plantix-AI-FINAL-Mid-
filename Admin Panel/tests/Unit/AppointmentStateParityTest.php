<?php

namespace Tests\Unit;

use App\Models\Appointment;
use PHPUnit\Framework\TestCase;

class AppointmentStateParityTest extends TestCase
{
    /** @test */
    public function appointment_can_only_be_accepted_from_pending_expert_approval_state(): void
    {
        $pendingApproval = new Appointment(['status' => Appointment::STATUS_PENDING_EXPERT_APPROVAL]);
        $pending = new Appointment(['status' => Appointment::STATUS_PENDING]);
        $confirmed = new Appointment(['status' => Appointment::STATUS_CONFIRMED]);

        $this->assertTrue($pendingApproval->canBeAccepted());
        $this->assertFalse($pending->canBeAccepted());
        $this->assertFalse($confirmed->canBeAccepted());
    }
}
