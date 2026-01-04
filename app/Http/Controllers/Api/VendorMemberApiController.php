<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use App\Models\VendorMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VendorMemberApiController extends Controller
{
    /**
     * Get store members for a vendor by their Sandbox ID
     * This is called from Sandbox dashboard to show vendor's store members
     */
    public function getStoreMembersBySandboxId(Request $request, $sandboxId)
    {
        try {
            // Find the user by sandbox_id (stored as subscription_user_id in RizqMall)
            $user = User::where('subscription_user_id', $sandboxId)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'members' => [],
                    'stats' => null,
                ]);
            }

            // Get the user's store
            $store = $user->stores()->first();

            if (!$store) {
                return response()->json([
                    'success' => true,
                    'message' => 'No store found for this user',
                    'members' => [],
                    'stats' => null,
                    'store' => null,
                ]);
            }

            // Get members with pagination
            $perPage = $request->get('per_page', 10);
            $members = VendorMember::with('customer:id,name,email,avatar,phone')
                ->where('store_id', $store->id)
                ->active()
                ->orderBy('joined_at', 'desc')
                ->paginate($perPage);

            // Transform members data
            $membersData = $members->map(function ($member) {
                return [
                    'id' => $member->id,
                    'customer_name' => $member->customer->name ?? 'Unknown',
                    'customer_email' => $member->customer->email ?? null,
                    'customer_avatar' => $member->customer->full_avatar_url ?? null,
                    'customer_phone' => $member->customer->phone ?? null,
                    'join_method' => $member->join_method,
                    'joined_at' => $member->joined_at?->format('Y-m-d H:i:s'),
                    'joined_at_human' => $member->joined_at?->diffForHumans(),
                    'status' => $member->status,
                ];
            });

            // Stats
            $stats = [
                'total_members' => $store->members()->active()->count(),
                'new_this_month' => $store->members()
                    ->active()
                    ->whereMonth('joined_at', now()->month)
                    ->count(),
                'qr_scans' => $store->members()
                    ->active()
                    ->where('join_method', 'qr_scan')
                    ->count(),
                'referrals' => $store->members()
                    ->active()
                    ->whereIn('join_method', ['referral', 'ref_code'])
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'members' => $membersData,
                'pagination' => [
                    'total' => $members->total(),
                    'per_page' => $members->perPage(),
                    'current_page' => $members->currentPage(),
                    'last_page' => $members->lastPage(),
                    'has_more_pages' => $members->hasMorePages(),
                ],
                'stats' => $stats,
                'store' => [
                    'id' => $store->id,
                    'name' => $store->name,
                    'slug' => $store->slug,
                    'member_ref_code' => $store->member_ref_code,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching store members for sandbox', [
                'sandbox_id' => $sandboxId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching members',
                'members' => [],
                'stats' => null,
            ], 500);
        }
    }
}
