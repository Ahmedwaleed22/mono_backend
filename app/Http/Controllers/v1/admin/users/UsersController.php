<?php

namespace App\Http\Controllers\v1\admin\users;

use App\Http\Controllers\v1\admin\Controller;
use App\Models\User;
use App\Rules\AccountType;
use App\Rules\Gender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = User::query()->paginate();
        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,name',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'gender' => ['required', 'string', 'max:255', new Gender],
            'profession' => 'required|string|max:255',
            'account_type' => ['required', 'string', 'max:255', new AccountType],
            'password' => 'required|string|min:8',
        ]);

        $user = new User();
        $user->name = $request->username;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->birth_date = $request->birth_date;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;
        $user->country = $request->country;
        $user->city = $request->city;
        $user->gender = $request->gender;
        $user->profession = $request->profession;
        $user->account_type = $request->account_type;
        $user->password = Hash::make($request->password);
        $user->save();

        if ($request->account_type === "client") {
            $user->assignRole('client');
        } else if ($request->account_type === "worker") {
            $user->assignRole('worker');
        }

        return response()->json($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::where('id', $id)->firstOrFail();
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'username' => 'string|max:255|unique:users,name',
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'birth_date' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users',
            'phone_number' => 'string|max:255',
            'country' => 'string|max:255',
            'city' => 'string|max:255',
            'gender' => ['string', 'max:255', new Gender],
            'profession' => 'string|max:255',
            'account_type' => ['string', 'max:255', new AccountType],
        ]);

        $user = User::where('id', $id)->first();
        $user->update($request->except('password'));

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::where('id', $id)->firstOrFail();
        $user->delete();

        return response()->json([
            'message' => 'Successfully deleted user.'
        ], 202);
    }
}
