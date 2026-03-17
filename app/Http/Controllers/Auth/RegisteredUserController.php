<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Show registration page
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */

    private function generateUniqueUserId(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (User::where('unique_id', $code)->exists());

        return $code;
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['nullable', 'string', 'max:100'],

            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'mobile'   => ['nullable', 'digits_between:8,15', 'unique:users,mobile'],

            'unique_id' => ['nullable', 'string', 'size:6', 'unique:users,unique_id'],

            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            // 🔐 Short Unique ID (6 chars)
            'unique_id' => $request->filled('unique_id')
                ? strtoupper($request->unique_id)
                : $this->generateUniqueUserId(),

            // 👤 Default role & status
            'role_id' => 1,
            'general_status_id' => 1,

            // 🧍 Personal info
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'username'   => $request->username,
            'email'      => $request->email,
            'mobile'     => $request->mobile,

            // 💰 Wallet
            'balance' => 0,

            // 🔑 Security
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
