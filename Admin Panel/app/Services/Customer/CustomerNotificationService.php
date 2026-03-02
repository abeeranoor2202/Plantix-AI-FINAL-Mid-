<?php

namespace App\Services\Customer;

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\Customer\CustomerAppointmentCompletedNotification;
use App\Notifications\Customer\CustomerAppointmentScheduledNotification;
use App\Notifications\Customer\CustomerCropHealthNotification;
use App\Notifications\Customer\CustomerWeatherAlertNotification;
use Illuminate\Support\Facades\Log;

/**
 * CustomerNotificationService
 * 
 * Centralized service for sending email notifications to customers.
 * Handles: Appointments, Crop Health Alerts, Weather Alerts
 */
class CustomerNotificationService
{
    /**
     * Send appointment scheduled notification
     * 
     * @param User $customer
     * @param Appointment $appointment
     */
    public static function notifyAppointmentScheduled(User $customer, Appointment $appointment): void
    {
        try {
            $customer->notify(new CustomerAppointmentScheduledNotification($appointment));
            Log::info("Appointment scheduled notification sent", [
                'customer_id' => $customer->id,
                'appointment_id' => $appointment->id,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send appointment scheduled notification", [
                'customer_id' => $customer->id,
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send appointment completed notification
     * 
     * @param User $customer
     * @param Appointment $appointment
     * @param string|null $expertNotes
     */
    public static function notifyAppointmentCompleted(User $customer, Appointment $appointment, ?string $expertNotes = null): void
    {
        try {
            $customer->notify(new CustomerAppointmentCompletedNotification($appointment, $expertNotes));
            Log::info("Appointment completed notification sent", [
                'customer_id' => $customer->id,
                'appointment_id' => $appointment->id,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send appointment completed notification", [
                'customer_id' => $customer->id,
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send crop health alert notification
     * 
     * Usage examples:
     *   CustomerNotificationService::notifyCropHealthAlert($user, 'disease', 'Wheat', 'high', 
     *       'Apply fungicide immediately', 'Leaf Rust');
     *   CustomerNotificationService::notifyCropHealthAlert($user, 'pest', 'Rice', 'medium',
     *       'Deploy pest control measures', 'Stem Borer Infestation');
     * 
     * @param User $customer
     * @param string $alertType 'disease'|'pest'|'nutrient'|'weather'
     * @param string $cropName
     * @param string $severity 'low'|'medium'|'high'|'critical'
     * @param string $recommendation
     * @param string|null $detectedIssue
     */
    public static function notifyCropHealthAlert(
        User $customer,
        string $alertType,
        string $cropName,
        string $severity,
        string $recommendation,
        ?string $detectedIssue = null,
    ): void {
        try {
            $customer->notify(new CustomerCropHealthNotification(
                alertType:      $alertType,
                cropName:       $cropName,
                severity:       $severity,
                recommendation: $recommendation,
                detectedIssue:  $detectedIssue,
            ));
            Log::info("Crop health alert notification sent", [
                'customer_id' => $customer->id,
                'alert_type' => $alertType,
                'crop' => $cropName,
                'severity' => $severity,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send crop health alert notification", [
                'customer_id' => $customer->id,
                'alert_type' => $alertType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send weather alert notification
     * 
     * Usage examples:
     *   CustomerNotificationService::notifyWeatherAlert($user, 'rainfall', 'alert',
     *       'Heavy rainfall expected in the next 48 hours',
     *       'Ensure proper drainage...',
     *       'Lahore, Punjab');
     *   CustomerNotificationService::notifyWeatherAlert($user, 'temperature', 'warning',
     *       'Unusual temperature drop expected',
     *       'Protect sensitive crops with covers...',
     *       'Multan');
     * 
     * @param User $customer
     * @param string $weatherType 'rainfall'|'temperature'|'humidity'|'wind'
     * @param string $severity 'advisory'|'warning'|'alert'
     * @param string $description
     * @param string|null $recommendation
     * @param string|null $affectedArea
     */
    public static function notifyWeatherAlert(
        User $customer,
        string $weatherType,
        string $severity,
        string $description,
        ?string $recommendation = null,
        ?string $affectedArea = null,
    ): void {
        try {
            $customer->notify(new CustomerWeatherAlertNotification(
                weatherType:    $weatherType,
                severity:       $severity,
                description:    $description,
                recommendation: $recommendation,
                affectedArea:   $affectedArea,
            ));
            Log::info("Weather alert notification sent", [
                'customer_id' => $customer->id,
                'weather_type' => $weatherType,
                'severity' => $severity,
                'area' => $affectedArea,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send weather alert notification", [
                'customer_id' => $customer->id,
                'weather_type' => $weatherType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Bulk notification: Send crop health alert to multiple customers
     * Useful for broadcasting alerts to all users in a region
     */
    public static function notifyMultipleCropHealthAlert(
        array $customerIds,
        string $alertType,
        string $cropName,
        string $severity,
        string $recommendation,
        ?string $detectedIssue = null,
    ): int {
        $sent = 0;
        foreach ($customerIds as $customerId) {
            $customer = User::find($customerId);
            if ($customer) {
                self::notifyCropHealthAlert($customer, $alertType, $cropName, $severity, $recommendation, $detectedIssue);
                $sent++;
            }
        }
        Log::info("Bulk crop health alert sent", ['total_recipients' => $sent, 'alert_type' => $alertType]);
        return $sent;
    }

    /**
     * Bulk notification: Send weather alert to multiple customers
     */
    public static function notifyMultipleWeatherAlert(
        array $customerIds,
        string $weatherType,
        string $severity,
        string $description,
        ?string $recommendation = null,
        ?string $affectedArea = null,
    ): int {
        $sent = 0;
        foreach ($customerIds as $customerId) {
            $customer = User::find($customerId);
            if ($customer) {
                self::notifyWeatherAlert($customer, $weatherType, $severity, $description, $recommendation, $affectedArea);
                $sent++;
            }
        }
        Log::info("Bulk weather alert sent", ['total_recipients' => $sent, 'weather_type' => $weatherType]);
        return $sent;
    }
}
