<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display user profile
     */
    public function show()
    {
        $user = Auth::user();
        return view('customer.profile', compact('user'));
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'bio' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
        ]);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'bio' => $request->bio,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
        ]);

        return redirect()->back()
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Update user avatar
     */
    public function updateAvatar(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Delete old avatar
        if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return redirect()->back()
            ->with('success', 'Avatar updated successfully!');
    }

    /**
     * Display addresses
     */
    public function addresses()
    {
        $user = Auth::user();
        $addresses = $user->addresses()->get();

        return view('customer.addresses', compact('addresses'));
    }

    /**
     * Store new address
     */
    public function storeAddress(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'type' => 'required|in:billing,shipping,both',
            'label' => 'nullable|string|max:100',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_default' => 'nullable|boolean',
        ]);

        $address = $user->addresses()->create($request->all());

        if ($request->has('is_default') && $request->is_default) {
            $address->setAsDefault();
        }

        return redirect()->back()
            ->with('success', 'Address added successfully!');
    }

    /**
     * Update address
     */
    public function updateAddress(Request $request, Address $address)
    {
        $user = Auth::user();

        // Verify address belongs to this user
        if ($address->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'type' => 'required|in:billing,shipping,both',
            'label' => 'nullable|string|max:100',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $address->update($request->all());

        return redirect()->back()
            ->with('success', 'Address updated successfully!');
    }

    /**
     * Delete address
     */
    public function deleteAddress(Address $address)
    {
        $user = Auth::user();

        // Verify address belongs to this user
        if ($address->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        // If this is the default address, set another one as default
        if ($address->is_default) {
            $newDefault = $user->addresses()
                ->where('id', '!=', $address->id)
                ->first();
            
            if ($newDefault) {
                $newDefault->setAsDefault();
            }
        }

        $address->delete();

        return redirect()->back()
            ->with('success', 'Address deleted successfully!');
    }

    /**
     * Set address as default
     */
    public function setDefaultAddress(Address $address)
    {
        $user = Auth::user();

        // Verify address belongs to this user
        if ($address->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $address->setAsDefault();

        return redirect()->back()
            ->with('success', 'Default address updated!');
    }
}