<?php

namespace Tests\Unit;

use App\Models\Appointment;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests — no DB, no HTTP. Validates the state-machine map.
 */
class AppointmentStateMachineTest extends TestCase
{
    private function makeAppointment(string $status): Appointment
    {
        $appt = new Appointment();
        $appt->status = $status;
        return $appt;
    }

    // ── assertCanTransitionTo ─────────────────────────────────────────────────

    /** @test */
    public function draft_can_move_to_pending_payment(): void
    {
        $appt = $this->makeAppointment(Appointment::STATUS_DRAFT);
        // No exception expected
        $appt->assertCanTransitionTo(Appointment::STATUS_PENDING_PAYMENT);
        $this->assertTrue(true);
    }

    /** @test */
    public function draft_cannot_jump_to_confirmed(): void
    {
        $this->expectException(\DomainException::class);
        $appt = $this->makeAppointment(Appointment::STATUS_DRAFT);
        $appt->assertCanTransitionTo(Appointment::STATUS_CONFIRMED);
    }

    /** @test */
    public function pending_payment_can_move_to_pending_expert_approval(): void
    {
        $appt = $this->makeAppointment(Appointment::STATUS_PENDING_PAYMENT);
        $appt->assertCanTransitionTo(Appointment::STATUS_PENDING_EXPERT_APPROVAL);
        $this->assertTrue(true);
    }

    /** @test */
    public function pending_payment_can_move_to_payment_failed(): void
    {
        $appt = $this->makeAppointment(Appointment::STATUS_PENDING_PAYMENT);
        $appt->assertCanTransitionTo(Appointment::STATUS_PAYMENT_FAILED);
        $this->assertTrue(true);
    }

    /** @test */
    public function confirmed_can_move_to_reschedule_requested(): void
    {
        $appt = $this->makeAppointment(Appointment::STATUS_CONFIRMED);
        $appt->assertCanTransitionTo(Appointment::STATUS_RESCHEDULE_REQUESTED);
        $this->assertTrue(true);
    }

    /** @test */
    public function completed_is_terminal_and_cannot_transition(): void
    {
        $this->expectException(\DomainException::class);
        $appt = $this->makeAppointment(Appointment::STATUS_COMPLETED);
        $appt->assertCanTransitionTo(Appointment::STATUS_CONFIRMED);
    }

    /** @test */
    public function cancelled_is_terminal_and_cannot_transition(): void
    {
        $this->expectException(\DomainException::class);
        $appt = $this->makeAppointment(Appointment::STATUS_CANCELLED);
        $appt->assertCanTransitionTo(Appointment::STATUS_CONFIRMED);
    }

    // ── helper methods ─────────────────────────────────────────────────────────

    /** @test */
    public function can_be_cancelled_by_customer_only_in_valid_states(): void
    {
        $cancelable = [
            Appointment::STATUS_DRAFT,
            Appointment::STATUS_PENDING_PAYMENT,
            Appointment::STATUS_PAYMENT_FAILED,
            Appointment::STATUS_PENDING_EXPERT_APPROVAL,
            Appointment::STATUS_CONFIRMED,
        ];

        foreach ($cancelable as $status) {
            $appt = $this->makeAppointment($status);
            $this->assertTrue($appt->canBeCancelledByCustomer(), "Expected status '{$status}' to allow customer cancel.");
        }

        $non_cancelable = [
            Appointment::STATUS_COMPLETED,
            Appointment::STATUS_CANCELLED,
            Appointment::STATUS_REJECTED,
        ];

        foreach ($non_cancelable as $status) {
            $appt = $this->makeAppointment($status);
            $this->assertFalse($appt->canBeCancelledByCustomer(), "Expected status '{$status}' to block customer cancel.");
        }
    }

    /** @test */
    public function admin_can_force_cancel_any_non_terminal_status(): void
    {
        $appt = $this->makeAppointment(Appointment::STATUS_CONFIRMED);
        // Admin bypass: isAdmin=true should not throw
        $appt->assertCanTransitionTo(Appointment::STATUS_CANCELLED, true);
        $this->assertTrue(true);
    }
}
