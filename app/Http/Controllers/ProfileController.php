<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form
     */
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update user profile information
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->fill($validated);
        $user->save();

        return back()->with('success', 'Профиль успешно обновлён');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['nullable'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = $request->user();

        // If user has a password, verify current one
        if ($user->password && $request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Неверный текущий пароль']);
            }
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Пароль успешно изменён');
    }

    /**
     * Disconnect Telegram
     */
    public function disconnectTelegram(Request $request)
    {
        $user = $request->user();
        $user->telegram_id = null;
        $user->save();

        return back()->with('success', 'Telegram отвязан от аккаунта');
    }
}
