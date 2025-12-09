<?php

namespace App\Http\Controllers;

use App\Models\PupProfile;
use App\Models\User;
use App\Models\UserDog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index()
    {
        // Aktif kullanıcı sayısı (status = 'active')
        $activeUsersCount = User::where('status', 'active')->count();

        // Aktif köpek sayısı (status = 'active')
        $activeDogsCount = PupProfile::where('name', '!=', null)->count();
        $dogOwnersCount = User::where('role_id', 3)->where('status', 'active')->count();

        // Dog Adoption Seekers (role_id = 4)
        $adoptionSeekersCount = User::where('role_id', 4)->where('status', 'active')->count();

        return view('dashboard', compact('activeUsersCount', 'activeDogsCount', 'dogOwnersCount', 'adoptionSeekersCount'));


    }
}
