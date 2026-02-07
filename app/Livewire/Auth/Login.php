<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }

    // public function login()
    // {
    //     $this->validate([
    //         'email' => ['required', 'email'],
    //         'password' => ['required', 'string'],
    //     ]);

    //     if (! Auth::attempt([
    //         'email' => $this->email,
    //         'password' => $this->password,
    //         'status' => 'active',
    //     ], $this->remember)) {
    //         throw ValidationException::withMessages([
    //             'email' => 'Invalid credentials.',
    //         ]);
    //     }

    //     session()->regenerate();

    //     activity()
    //         ->causedBy(Auth::user()->id)
    //         ->withProperties([
    //             'ip' => request()->ip(),
    //             'user_agent' => request()->userAgent(),
    //         ])
    //         ->log('login');

    //     return redirect()->intended(route('dashboard'));
    // }

    public function login()
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
            'status' => 'active',
        ];

        if (!Auth::attempt($credentials, $this->remember)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials.',
            ]);
        }

        // Update last_login_at after successful login
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return;
        }
        
        $user->last_login_at = now();
        $user->save();

        session()->regenerate();

        activity()
            ->causedBy($user->id)
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('login');

        return redirect()->intended(route('dashboard'));
    }


    #[Layout('layouts.auth')]
    public function render()
    {
        return view('livewire.auth.login');
    }
}
