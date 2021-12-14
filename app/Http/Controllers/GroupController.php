<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Layer;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function getGroups(Request $request): \Illuminate\Http\JsonResponse
    {
        $groups = Group::all();

        return response()->json(["state" => 0, "data" => $groups], 200);
    }

    public function getGroup(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'id' => 'required'
        ]);

        $id = $request->input('id');

        $group = Group::find( $id );

        if( !$group )
            return response()->json(["state" => 1, "data" => []], 404);

        return response()->json(["state" => 0, "data" => $group], 201);
    }

    public function getGroupsByName(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => 'required'
        ]);

        $name = $request->input('name');

        $groups = Group::where('name', 'LIKE', "%{$name}%")->get();

        if( !$groups )
            return response()->json(["state" => 1, "data" => []], 404);

        return response()->json(["state" => 0, "data" => $groups], 201);
    }

    public function getLayersByGroup(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'id' => 'required'
        ]);

        $id = $request->input('id');

        $group = Group::find( $id );

        if( !$group )
            return response()->json(["state" => 1, "data" => []], 404);

        return response()->json(["state" => 0, "data" => $group->layers], 201);
    }

    public function getMembersByGroup(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'id' => 'required'
        ]);

        $id = $request->input('id');

        $group = Group::find( $id );

        if( !$group )
            return response()->json(["state" => 1, "data" => []], 404);

        return response()->json(["state" => 0, "data" => $group->users], 201);
    }

    public function attachLayerToGroup(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'layer_id' => 'required',
            'group_id' => 'required',
        ]);

        $group = Group::find( $request->input('group_id') );

        if( !$group )
            return response()->json(["state" => 1, "data" => "No Group"], 404);

        $layer = Layer::find( $request->input('layer_id') );

        if( !$layer )
            return response()->json(["state" => 1, "data" => "No layer"], 404);

        if( $group->layers->contains( $layer->id ) )
            return response()->json(["state" => 1, "data" => "layer is attached already"], 404);


        $group->layers()->attach( $layer->id );

        return response()->json(["state" => 0, "data" => $layer], 200);
    }

    public function detachLayerToGroup(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'layer_id' => 'required',
            'group_id' => 'required',
        ]);

        $group = Group::find( $request->input('group_id') );

        if( !$group )
            return response()->json(["state" => 1, "data" => "No Group"], 404);

        $layer = Layer::find( $request->input('layer_id') );

        if( !$layer )
            return response()->json(["state" => 1, "data" => "No layer"], 404);

        $group->layers()->detach( $layer->id );

        return response()->json(["state" => 0, "data" => "Success"], 200);
    }

    public function attachMemberToGroup(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'user_id' => 'required',
            'group_id' => 'required',
            'role' => 'required',
        ]);

        $group = Group::find( $request->input('group_id') );

        if( !$group )
            return response()->json(["state" => 1, "data" => "No Group"], 404);

        $user = User::find( $request->input('user_id') );

        if( !$user )
            return response()->json(["state" => 1, "data" => "No user"], 404);

        if( $group->users->contains( $user->id ) )
            return response()->json(["state" => 1, "data" => "Is member already"], 404);

        $group->users()->attach( $user->id, [ "user_role" => $request->input('role') ]);

        return response()->json(["state" => 0, "data" => $user], 200);
    }

    public function detachMemberToGroup(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'user_id' => 'required',
            'group_id' => 'required',
        ]);

        $group = Group::find( $request->input('group_id') );

        if( !$group )
            return response()->json(["state" => 1, "data" => "No Group"], 404);

        $user = User::find( $request->input('user_id') );

        if( !$user )
            return response()->json(["state" => 1, "data" => "No user"], 404);

        $group->users()->detach( $user->id );

        return response()->json(["state" => 0, "data" => "Success"], 200);
    }

    public function addGroup(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => 'required',
            'owner_id' => 'required'
        ]);

        $owner = User::find( $request->input('owner_id') );

        if( !$owner )
            return response()->json(["state" => 1, "data" => null], 404);

        $newGroup = new Group();
        $newGroup->name = $request->input('name');
        $newGroup->owner_id = $owner->id;
        $newGroup->save();

        return response()->json(["state" => 0, "data" => $newGroup], 200);
    }

    public function deleteGroup(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'id' => 'required',
        ]);

        $group = Group::find( $request->input('id') );

        if( !$group )
            return response()->json(["state" => 1, "data" => null], 404);

        $group->layers()->detach();

        $group->users()->detach();

        $group->delete();

        return response()->json(["state" => 0, "data" => "Deleted"], 200);
    }
}
