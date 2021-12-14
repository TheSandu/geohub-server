<?php

namespace App\Http\Controllers;

use App\Models\Layer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['errors'=>$validator->errors()->all()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->getRememberToken();
                $response = ['token' => $token, 'user' => $user];
                return response($response, 200);
            } else {
                $response = ["message" => "Password mismatch"];
                return response($response, 422);
            }
        } else {
            $response = ["message" =>'User does not exist'];
            return response($response, 422);
        }
    }

    public function register (Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $request['password'] = Hash::make($request['password']);
        $request['remember_token'] = Str::random(10);
        $user = User::create($request->toArray());
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => $token];
        return response($response, 200);
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
        $request->validate([
            'id' => 'required'
        ]);

        $id = $request->input('id');

        $users = User::find( $id );

        if( !$users )
            return response()->json(["state" => 1, "data" => null], 200);

        $groups = $users->groups;

        $layers = [];

        foreach( $groups as $group) {
            $groupLayers = $group->layers;
            foreach ( $groupLayers as $layer ) {
                $layers[] = $layer;
            }
        }

        return response()->json(["state" => 0, "data" => $layers], 200);
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

        $users = User::where('name', 'LIKE', "%{$name}%")->get();

        if( !$users )
            return response()->json(["state" => 1, "data" => []], 404);

        return response()->json(["state" => 0, "data" => $users], 200);
    }

    public function addUser(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(["state" => 0, "data" => "Success"], 200);
    }
}
