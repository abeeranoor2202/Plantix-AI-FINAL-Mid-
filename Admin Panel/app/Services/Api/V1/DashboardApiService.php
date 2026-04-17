<?php

namespace App\Services\Api\V1;

use App\Models\Appointment;
use App\Models\ForumThread;
use App\Models\Order;
use App\Models\PlatformActivity;
use App\Models\Product;
use App\Models\User;

class DashboardApiService
{
    public function summary(User $actor): array
    {
        $role = $actor->role;

        if ($role === 'admin') {
            return [
                'orders_total' => Order::count(),
                'appointments_total' => Appointment::count(),
                'users_total' => User::where('role', 'user')->count(),
                'vendors_total' => User::where('role', 'vendor')->count(),
                'threads_total' => ForumThread::count(),
                'recent_activity' => PlatformActivity::latest()->limit(10)->get(),
            ];
        }

        if ($role === 'vendor') {
            $vendorId = (int) optional($actor->vendor)->id;
            return [
                'products_total' => Product::where('vendor_id', $vendorId)->count(),
                'orders_total' => Order::forVendor($vendorId)->count(),
                'orders_pending' => Order::forVendor($vendorId)->where('status', Order::STATUS_PENDING)->count(),
                'orders_processing' => Order::forVendor($vendorId)->where('status', Order::STATUS_PROCESSING)->count(),
            ];
        }

        if ($role === 'expert' || $role === 'agency_expert') {
            $expertId = (int) optional($actor->expert)->id;
            return [
                'appointments_total' => Appointment::where('expert_id', $expertId)->count(),
                'appointments_confirmed' => Appointment::where('expert_id', $expertId)->where('status', Appointment::STATUS_CONFIRMED)->count(),
                'appointments_completed' => Appointment::where('expert_id', $expertId)->where('status', Appointment::STATUS_COMPLETED)->count(),
                'forum_threads_total' => ForumThread::count(),
            ];
        }

        return [
            'orders_total' => Order::forCustomer($actor->id)->count(),
            'appointments_total' => Appointment::where('user_id', $actor->id)->count(),
            'forum_threads_total' => ForumThread::where('user_id', $actor->id)->count(),
        ];
    }
}
