<?php
namespace App\Services;
 
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserService
{

    public function validate(Request $request) : bool
    {
        return ($this->get($request) != null);
    }

    public function get(Request $request)
    {
        $user = Cache::remember("userForSession-".$request->session()->getId(),60*60, function () use ($request)    {
            return $request->user();
        });
        
        return $user;
    }

    public function isPremiumUser($user)
    {
        return false;
    }


}