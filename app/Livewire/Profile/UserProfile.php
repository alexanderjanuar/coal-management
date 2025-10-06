<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class UserProfile extends Component
{
    public $user;
    public $userClients;
    public $userRole;

    public function mount()
    {
        $this->user = Auth::user();
        $this->userClients = $this->user->userClients()->with('client')->get();
        $this->userRole = $this->user->roles->first()?->name ?? 'No Role';
    }

    public function render()
    {
        return view('livewire.profile.user-profile');
    }
}