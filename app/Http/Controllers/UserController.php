<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //


    public function toggleStatus(User $user)
    {
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return response()->json([
            'success' => true,
            'status' => $user->status
        ]);
    }
    public function index(Request $request)
    {
        $query = User::withCount([
            'pupProfiles as survey_count' => function ($q) {
                $q->join('pup_profile_answers', 'pup_profiles.id', '=', 'pup_profile_answers.pup_profile_id');
            }
        ]);

        if ($request->search) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->role_id) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', $request->role_id);
            });
        }

        $users = $query->paginate(20);

        $roles = Role::all();

        return view('users', compact('users', 'roles'));
    }



    // public function show(User $user)
    // {
    //     $user->load([
    //         'roles'
    //     ]);

    //     return view('users.show', compact('user'));
    // }



    public function pups(User $user)
    {
        $user->load([
            'pupProfiles.images',
            'pupProfiles.breed',
            'pupProfiles.ageRange'
        ]);

        return view('users.pups', compact('user'));
    }



}
