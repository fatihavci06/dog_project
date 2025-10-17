<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = User::where(function ($q) {
            $q->where('role_id', '!=', 1)
                ->orWhereNull('role_id');
        });

        // Search özelliği
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Role filtreleme
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Rolleri al (select box için)
        $roles = \App\Models\Role::all();

        $users = $query->paginate(20)->withQueryString();

        return view('users', compact('users', 'roles'));
    }

    public function toggleStatus(User $user)
    {
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return response()->json([
            'success' => true,
            'status' => $user->status
        ]);
    }
    public function show(User $user)
    {

        $user->load([
            'userDogs',
            'testUserRoles.role', // role ilişkisini yükle
            'testUserRoles.dog'   // dog ilişkisini yükle
        ]);


        return view('users.show', compact('user'));
    }
    public function questionnaireShow(string $id)
    {
        $testId = $id;

        // Soruları ve ilgili test cevabı olan kullanıcı cevaplarını rank sırasına göre al
        $questions = Question::with(['userAnswers' => function ($query) use ($testId) {
            $query->where('test_id', $testId)
                ->orderBy('rank', 'asc'); // rank'a göre sırala
        }, 'options'])
            ->where('is_active', 1) // aktif sorular
            ->get();

        return view('questionnaire.show', compact('questions', 'testId'));
    }
}
