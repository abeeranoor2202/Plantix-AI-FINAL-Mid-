<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AdminVendorsListController extends Controller
{
    /**
     * Get vendors list with filtering
     */
    public function index(Request $request)
    {
        try {
            $type = $request->get('type', 'all'); // all, pending, approved, rejected, suspended
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $search = $request->get('search', '');
            $orderBy = $request->get('orderBy', 'created_at');
            $orderDir = $request->get('orderDir', 'desc');

            $query = User::where('role', 'vendor');

            // Filter by verification status
            if ($type === 'pending') {
                $query->where('status', 'pending');
            } elseif ($type === 'approved') {
                $query->where('status', 'approved');
            } elseif ($type === 'rejected') {
                $query->where('status', 'rejected');
            } elseif ($type === 'suspended') {
                $query->where('status', 'suspended');
            }

            // Search functionality
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%$search%")
                      ->orWhere('last_name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%")
                      ->orWhere('phone_number', 'like', "%$search%");
                });
            }

            $total = $query->count();
            $vendors = $query->orderBy($orderBy, $orderDir)
                            ->skip(($page - 1) * $limit)
                            ->take($limit)
                            ->get()
                            ->map(function ($vendor) {
                                return [
                                    'id' => $vendor->id,
                                    'first_name' => $vendor->first_name,
                                    'last_name' => $vendor->last_name,
                                    'email' => $vendor->email,
                                    'phone_number' => $vendor->phone_number,
                                    'profile_picture_url' => $vendor->profile_picture_url ?? asset('images/placeholder.png'),
                                    'status' => $vendor->status ?? 'pending',
                                    'is_document_verified' => $vendor->is_document_verified ?? false,
                                    'is_active' => $vendor->is_active ?? true,
                                    'created_at' => $vendor->created_at,
                                    'role' => $vendor->role,
                                ];
                            });

            return response()->json([
                'success' => true,
                'data' => $vendors,
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching vendors: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update vendor active status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $vendor = User::where('role', 'vendor')->find($id);
            
            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor not found'
                ], 404);
            }

            $validated = $request->validate([
                'is_active' => 'required|boolean',
            ]);

            $vendor->is_active = $validated['is_active'];
            $vendor->active = $validated['is_active'];
            $vendor->status = $validated['is_active'] ? 'approved' : 'suspended';
            $vendor->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $vendor->id,
                    'is_active' => $vendor->is_active,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating vendor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete vendor
     */
    public function destroy($id)
    {
        try {
            $vendor = User::where('role', 'vendor')->find($id);
            
            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor not found'
                ], 404);
            }

            $vendor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vendor deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting vendor: ' . $e->getMessage()
            ], 500);
        }
    }
}
