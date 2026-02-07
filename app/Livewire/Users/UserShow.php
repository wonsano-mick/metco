<?php

namespace App\Livewire\Users;

use Livewire\Component;
use App\Models\Eloquent\User;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UserShow extends Component
{
    public User $user;

    public function mount(User $user)
    {
        $currentUser = Auth::user();
        if (! $currentUser instanceof \App\Models\Eloquent\User) {
            return;
        }

        if (!$currentUser) {
            abort(403, 'Unauthorized.');
        }

        if (!$currentUser->isAdmin()) {
            abort(403, 'You do not have permission to view user details.');
        }

        $this->user = $user;

        // Debug log
        Log::info('UserShow Component Mounted', [
            'user_id' => $user->id,
            'user_exists' => $user->exists,
            'user_class' => get_class($user),
        ]);
    }

    #[Layout('layouts.main')]
    public function render()
    {
        // Debug before rendering
        Log::info('UserShow Component Rendering', [
            'user_id' => $this->user->id,
            'user_data' => [
                'id' => $this->user->id,
                'email' => $this->user->email,
                'full_name' => $this->user->full_name,
            ]
        ]);

        return view('livewire.users.user-show', [
            'user' => $this->user->load(['branch']),
        ]);
    }
}
