<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Notifications\ActionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::orderBy('updated_at', 'desc')->role('user');
        if ($request->search) {
            $users->where('name', 'LIKE', "%{$request->search}%")->orWhere('email', 'LIKE', "%{$request->search}%");
        }

        return inertia("Admin/Users/Page", ["users" => UserResource::collection($users->paginate(3)), "search" => $request->search ?? null]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia("Admin/Users/Create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {

        $data = $request->all();
        if ($request->photo) {
            $data["photo"] = $request->file("photo")->store("photos");
        }

        $user = User::create($data)->assignRole("user");
        return redirect()->route('admin.users.index')->with("message", "Berhasil tambah user " . $user->name);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return inertia("Admin/Users/Edit", ["user" => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->all();
        unset($data["password"], $data['password_confirmation'], $data['photo']);
        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }
        if ($request->photo) {
            @unlink("storage/" . $user->photo);
            $data['photo'] = $request->file('photo')->store('photos');
        }

        $user->update($data);
        $user->notify(new ActionNotification());
        return back()->with("message", "Berhasil edit user " . $user->name);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        @unlink("storage/" . $user->photo);
        return back()->with("message", "Berhasil hapus user " . $user->name);
    }
}
