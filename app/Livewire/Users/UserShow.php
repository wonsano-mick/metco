<?php

namespace App\Livewire\Users;

use Livewire\Component;
use App\Models\Eloquent\User;
use Livewire\Attributes\Layout;
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

        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            abort(403, 'You do not have permission to view user details.');
        }

        $this->user = $user;
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.users.user-show', [
            'user' => $this->user->load(['branch']),
        ]);
    }
}
