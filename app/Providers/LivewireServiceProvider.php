<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LivewireServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Livewire::component('users.user-index', \App\Livewire\Users\UserIndex::class);
        // Livewire::component('dashboard', \App\Livewire\Dashboard::class);
        // Add other components as needed
    }
}
