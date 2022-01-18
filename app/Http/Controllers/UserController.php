<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $credentials = $request->only('email','password');
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if (auth('api')->attempt($credentials,true)) {
            $user = auth('api')->user();

            $success['token'] = auth('api')->user()->getRememberToken();
            return response()->json([
                'success' => true,
                'token' => $success,
                'user' => $user
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
            ], 401);
        }
    }

    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'email|required|unique:users',
            'name' => 'required',
            'password' => 'required|min:1|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/',
            'confirm_password' => 'required|same:password'
        ]);

        if(!User::where('email', strtolower($request->input('email')))->first()) {
            $bcryptPass = bcrypt($request->input('password'));
            $user = User::create([
                'name' => $request->input('name'),
                'email' => strtolower($request->input('email')),
                'password' => $bcryptPass,
            ]);

            if(auth('api')->attempt($request->only('email','password'),true)) {
                $token = auth('api')->user()->getRememberToken();

                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'user' => $user
                ], 200);
            }

            return response()->json(['error'=>'61: Unauthorised'], 401);
        }

        return response()->json(['error'=>'64: Unauthorised'], 401);

    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        auth('api')->user()->tokens()->delete();

        return response()->json(["state" => 0,
            "data" => 'Tokens Revoked',
        ], 200);
    }

    public function getUsers(Request $request): \Illuminate\Http\JsonResponse
    {
        $users = User::all();

        return response()->json(["state" => 0, "data" => $users], 200);
    }

    public function getUserLayers(Request $request): \Illuminate\Http\JsonResponse
    {

        $user = User::where('remember_token', $request->header('token'))->first();

        if( !$user )
            return response()->json(["state" => 1, "data" => null], 200);

        $groups = $user->groups;

        $layers = [];

        foreach( $groups as $group) {
            $groupLayers = $group->layers;
            foreach ( $groupLayers as $layer ) {
                $layer["group_id"] = $group->id;
                $layers[] = $layer;
            }
        }

        return response()->json(["state" => 0, "data" => $layers], 200);
    }

    public function getCurentUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = User::where('remember_token', $request->header('token'))->first();

        return response()->json(["state" => 0, "data" => $user], 200);
    }

    public function getUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'id' => 'required'
        ]);

        $id = $request->input('id');

        $users = User::find( $id );

        if( !$users )
            return response()->json(["state" => 1, "data" => null], 200);

        return response()->json(["state" => 0, "data" => $users], 200);
    }

    public function getUsersByName(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => 'required'
        ]);

        $name = $request->input('name');

        $users = null;

        if( $request->has('group_id') ) {
            $group = Group::find( $request->input('group_id') );
            $users = $group->users()->where('name', 'LIKE', "%{$name}%")->get();
        } else {
            $users = User::where('name', 'LIKE', "%{$name}%")->get();
        }

        if( !$users )
            return response()->json(["state" => 1, "data" => []], 404);

        return response()->json(["state" => 0, "data" => $users], 200);
    }

    public function addUser(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(["state" => 0, "data" => "Success"], 200);
    }
}
