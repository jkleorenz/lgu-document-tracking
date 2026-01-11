<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the user's profile
     */
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    /**
     * Show the edit profile form
     */
    public function edit()
    {
        // Get fresh user data from database to ensure profile picture is up to date
        $user = Auth::user()->fresh();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store new profile picture with random name for security
            $filename = uniqid() . '_' . time() . '.' . $request->file('profile_picture')->getClientOriginalExtension();
            $path = $request->file('profile_picture')->storeAs('profile-pictures', $filename, 'public');
            $validated['profile_picture'] = $path;
        } else {
            // Keep existing profile picture if no new one uploaded
            unset($validated['profile_picture']);
        }

        $oldValues = $user->only(['name', 'email', 'phone']);
        $user->update($validated);
        $newValues = $user->fresh()->only(['name', 'email', 'phone']);

        // Audit log
        AuditLog::log('profile.updated', $user->id, null, null, 'User updated profile', $oldValues, $newValues, $request->ip(), $request->userAgent());

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Update the user's profile picture
     */
    public function updateProfilePicture(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'profile_picture' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        // Delete old profile picture if exists
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        // Store new profile picture with random name for security
        $filename = uniqid() . '_' . time() . '.' . $request->file('profile_picture')->getClientOriginalExtension();
        $path = $request->file('profile_picture')->storeAs('profile-pictures', $filename, 'public');
        
        // Verify the file was stored successfully
        if (!Storage::disk('public')->exists($path)) {
            return back()->withErrors(['profile_picture' => 'Failed to upload profile picture. Please try again.']);
        }
        
        $user->update(['profile_picture' => $path]);

        // Refresh user model to get updated profile picture
        $user->refresh();
        
        // Verify the update was successful
        if ($user->profile_picture !== $path) {
            \Log::error('Profile picture path mismatch', [
                'expected' => $path,
                'actual' => $user->profile_picture,
            ]);
        }

        // Audit log
        AuditLog::log('profile.picture.updated', $user->id, null, null, 'User updated profile picture', null, null, $request->ip(), $request->userAgent());

        // Refresh the authenticated user instance to ensure fresh data
        $user->refresh();
        Auth::setUser($user);
        
        return redirect()->route('profile.edit')
            ->with('success', 'Profile picture updated successfully!')
            ->with('picture_updated', true);
    }

    /**
     * Remove the user's profile picture
     */
    public function removeProfilePicture()
    {
        $user = Auth::user();

        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->update(['profile_picture' => null]);
        $user->refresh();

        return redirect()->route('profile.edit')->with('success', 'Profile picture removed successfully!');
    }

    /**
     * Show the settings page
     */
    public function settings()
    {
        $user = Auth::user();
        return view('profile.settings', compact('user'));
    }

    /**
     * Update the user's password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Audit log
        AuditLog::log('password.changed', $user->id, null, null, 'User changed password', null, null, $request->ip(), $request->userAgent());

        return back()->with('success', 'Password updated successfully!');
    }
}

