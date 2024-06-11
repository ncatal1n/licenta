<?php

namespace App\Livewire;

use App\Services\UserService;
use Livewire\Component;

class NavigationButtons extends Component
{

    public $state = [
        "logged" => false
    ];
    public function loadUser(UserService $userService)
    {
        if($userService->validate(request())){
            $this->state["logged"] = true;
        }
    }

    public function render()
    {
        return view('livewire.navigation-buttons');
    }
}
