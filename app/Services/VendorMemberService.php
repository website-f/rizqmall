<?php

namespace App\Services;

use App\Models\Store;
use App\Models\User;
use App\Models\VendorMember;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VendorMemberService
{
    protected string $sandboxUrl;
    protected string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->sandboxUrl = config('services.sandbox.url');
        $this->apiKey = config('services.sandbox.api_key');
        $this->timeout = config('services.sandbox.timeout', 30);
    }

    /**
     * Register a customer as a member of a store/vendor
     */
    public function registerMember(
        Store $store,
        User $customer,
        string $joinMethod = 'direct',
        ?string $referralCode = null
    ): array {
        // Check if already a member
        $existingMembership = VendorMember::where('store_id', $store->id)
            ->where('customer_id', $customer->id)
            ->first();

        if ($existingMembership) {
            if ($existingMembership->status === 'active') {
                return [
                    'success' => false,
                    'message' => 'You are already a member of this store.',
                    'membership' => $existingMembership,
                ];
            }

            // Reactivate inactive membership
            $existingMembership->status = 'active';
            $existingMembership->join_method = $joinMethod;
            $existingMembership->referral_code = $referralCode ?? $store->member_ref_code;
            $existingMembership->joined_at = now();
            $existingMembership->save();

            // Update Sandbox referral
            $this->linkMemberInSandbox($store, $customer);

            return [
                'success' => true,
                'message' => 'Your membership has been reactivated.',
                'membership' => $existingMembership,
            ];
        }

        try {
            // Link the member in Sandbox referral system
            $sandboxReferralId = $this->linkMemberInSandbox($store, $customer);

            // Create vendor member record
            $membership = VendorMember::create([
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'sandbox_referral_id' => $sandboxReferralId,
                'join_method' => $joinMethod,
                'referral_code' => $referralCode ?? $store->member_ref_code,
                'status' => 'active',
                'joined_at' => now(),
            ]);

            Log::info('Customer joined as vendor member', [
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'join_method' => $joinMethod,
                'sandbox_referral_id' => $sandboxReferralId,
            ]);

            return [
                'success' => true,
                'message' => 'Successfully joined as a member.',
                'membership' => $membership,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to register vendor member', [
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to complete membership registration. Please try again.',
            ];
        }
    }

    /**
     * Link the member in Sandbox referral system under the vendor
     */
    protected function linkMemberInSandbox(Store $store, User $customer): ?int
    {
        try {
            // Get the vendor's Sandbox user ID
            $vendor = $store->user;
            if (!$vendor || !$vendor->subscription_user_id) {
                Log::warning('Vendor has no Sandbox user ID', [
                    'store_id' => $store->id,
                    'vendor_id' => $store->user_id,
                ]);
                return null;
            }

            // Get the customer's Sandbox user ID
            if (!$customer->subscription_user_id) {
                Log::warning('Customer has no Sandbox user ID', [
                    'customer_id' => $customer->id,
                ]);
                return null;
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->post($this->sandboxUrl . '/api/rizqmall/link-member', [
                    'vendor_user_id' => $vendor->subscription_user_id,
                    'member_user_id' => $customer->subscription_user_id,
                    'store_id' => $store->id,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success'] ?? false) {
                    Log::info('Member linked in Sandbox', [
                        'vendor_sandbox_id' => $vendor->subscription_user_id,
                        'member_sandbox_id' => $customer->subscription_user_id,
                        'referral_id' => $data['referral_id'] ?? null,
                    ]);
                    return $data['referral_id'] ?? null;
                }
            }

            Log::warning('Failed to link member in Sandbox', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error linking member in Sandbox', [
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Generate a unique store referral code
     */
    public function generateStoreRefCode(): string
    {
        $code = strtoupper(Str::random(8));

        // Ensure uniqueness
        while (Store::where('member_ref_code', $code)->exists()) {
            $code = strtoupper(Str::random(8));
        }

        return $code;
    }

    /**
     * Get member count for a store
     */
    public function getMemberCount(Store $store): int
    {
        return VendorMember::where('store_id', $store->id)
            ->active()
            ->count();
    }

    /**
     * Check if a user is a member of a store
     */
    public function isMember(Store $store, User $user): bool
    {
        return VendorMember::where('store_id', $store->id)
            ->where('customer_id', $user->id)
            ->active()
            ->exists();
    }

    /**
     * Get membership details
     */
    public function getMembership(Store $store, User $user): ?VendorMember
    {
        return VendorMember::where('store_id', $store->id)
            ->where('customer_id', $user->id)
            ->active()
            ->first();
    }

    /**
     * Deactivate a membership
     */
    public function deactivateMembership(VendorMember $membership): bool
    {
        $membership->status = 'inactive';
        return $membership->save();
    }
}
