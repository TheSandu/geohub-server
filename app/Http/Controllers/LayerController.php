<?php

namespace App\Http\Controllers;

use App\Models\Bordering;
use App\Models\Graphic;
use App\Models\Group;
use App\Models\Layer;
use App\Models\User;
use Illuminate\Http\Request;

class LayerController extends Controller
{
    public function getLayers(Request $request): \Illuminate\Http\JsonResponse
    {
        $owner = User::where('remember_token', $request->header('token'))->first();

        $groups = $owner->groups;

        $uniqueLayersId = [];

        $layers = [];
        foreach( $groups as $group ) {
            foreach ( $group->layers as $layer ) {
                if( !in_array( $layer->id, $uniqueLayersId ) ) {
                    $layers[] = $layer;
                    $uniqueLayersId[] = $layer->id;
                }
            }
        }

        foreach( $ownedLayers = Layer::where("owner_id", $owner->id)->get() as $layer) {
            if( !in_array( $layer->id, $uniqueLayersId ) ) {
                $layers[] = $layer;
                $uniqueLayersId[] = $layer->id;
            }
        }

        return response()->json(["state" => 0, "data" => $layers], 201);
    }

    public function getLayer(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'id' => 'required'
        ]);

        $id = $request->input('id');

        $layer = Layer::find( $id );

        if( !$layer )
            return response()->json(["state" => 1, "data" => []], 404);

        return response()->json(["state" => 0, "data" => $layer ], 201);
    }

    public function getLayersByName(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => 'required'
        ]);

        $user = User::where('remember_token', $request->header('token'))->first();

        $name = $request->input('name');

        $layers = Layer::where('name', 'LIKE', "%{$name}%")
            ->where('owner_id', $user->id)
            ->get();

        if( !$layers )
            return response()->json(["state" => 1, "data" => []], 404);

        return response()->json(["state" => 0, "data" => $layers], 201);
    }

    public function getLayerGraphics(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'id' => 'required'
        ]);

        $layer = Layer::find( $request->input('id') );

        if( !$layer )
            return response()->json(["state" => 1, "data" => []], 404);

        $graphics = $layer->graphics;

        return response()->json(["state" => 0, "data" => $graphics ], 201);
    }

    public function addGraphicToLayer(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'layer_id' => 'required',
            'type' => 'required',
            'geometry' => 'required',
            'attributes' => 'required',
            'extent' => 'required',
            'user_id' => 'required',
        ]);

        $layer = Layer::find( $request->input('layer_id') );

        if( !$layer )
            return response()->json(["state" => 1, "data" => "No layer"], 404);

        $graphic = new Graphic();
        $graphic->type = $request->input('type');
        $graphic->geometry = $request->input('geometry');
        $graphic->attributes = $request->input('attributes');
        $graphic->extent = $request->input('extent');
        $graphic->user_id = $request->input('user_id');
        $graphic->save();

        $layer->graphics()->attach( $graphic->id );

        return response()->json(["state" => 0, "data" => $graphic], 201);
    }

    public function updateLayerGraphic(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'id' => 'required',
        ]);

        $graphic = Graphic::find( $request->input('id') );

        if(!$graphic)
            return response()->json(['error' => 'Graphic not found'], 401);

        $graphic->geometry = !empty($request->input('geometry')) ? $request->input('geometry') : $graphic->geometry;
        $graphic->attributes = !empty($request->input('attributes')) ? $request->input('attributes') : $graphic->attributes;
        $graphic->extent = !empty($request->input('extent')) ? $request->input('extent') : $graphic->extent;
        $graphic->save();

        return response()->json(["state" => 0, "data" => $graphic], 201);
    }

    public function deleteLayerGraphic(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'layer_id' => 'required',
            'graphic_id' => 'required',
        ]);

        $layer = Layer::find( $request->input('layer_id') );

        if( !$layer )
            return response()->json(["state" => 1, "data" => "No layer"], 404);

        $graphic = Graphic::find( $request->input('graphic_id') );

        if(!$graphic)
            return response()->json(["state" => 1, "data" => "Graphic not found"], 404);

        $layer->graphics()->detach( $graphic->id );
        $graphic->delete();

        return response()->json(["state" => 0, "data" => "Deleted successfully"], 201);
    }

    public function getLayerBordering(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'layer_id' => 'required',
            'group_id' => 'required'
        ]);

        $bordering = Bordering::where([
            "layer_id" => $request->input("layer_id"),
            "group_id" => $request->input("group_id"),
        ])->first();

        if( !$bordering )
            return response()->json(["state" => 1, "data" => null], 201);

        $graphics = $bordering->graphics;

        return response()->json(["state" => 0, "data" => $graphics], 201);
    }

    public function getUserLayerBordering(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'layer_id' => 'required',
            'group_id' => 'required'
        ]);

        $user = User::where('remember_token', $request->header('token'))->first();

        if( !$user )
            return response()->json(["state" => 1, "data" => "No User found"], 404);

        $bordering = Bordering::where([
            "layer_id" => $request->input("layer_id"),
            "group_id" => $request->input("group_id"),
        ])->first();

        if( !$bordering )
            return response()->json(["state" => 1, "data" => null], 201);

        $graphics = $bordering->graphics()->where("attributes", "LIKE", "%\"id\":$user->id%")->get();

        return response()->json(["state" => 0, "data" => $graphics, "user" => $user, "query" => "%id:$user->id%"], 201);
    }

    public function addBorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'layer_id' => 'required',
            'group_id' => 'required',
            'geometry' => 'required',
            'attributes' => 'required',
            'extent' => 'required',
        ]);

        $owner = User::where('remember_token', $request->header('token'))->first();

        if( !$owner )
            return response()->json(['error' => 'User not found'], 401);

        $bordering = Bordering::where([
            "layer_id" => $request->input("layer_id"),
            "group_id" => $request->input("group_id"),
        ])->first();

        if( !$bordering ) {
            $bordering = new Bordering();
            $bordering->layer_id = $request->input("layer_id");
            $bordering->group_id = $request->input("group_id");
            $bordering->save();
        }

        $graphic = new Graphic();
        $graphic->type = "polygon";
        $graphic->geometry = $request->input('geometry');
        $graphic->attributes = $request->input('attributes');
        $graphic->extent = $request->input('extent');
        $graphic->user_id = $owner->id;
        $graphic->save();

        $bordering->graphics()->attach( $graphic->id );

        return response()->json(["state" => 0, "data" => $graphic], 201);
    }

    public function addLayer(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required',
            'symbol' => 'required',
            'fields' => 'required',
        ]);

        $owner = User::where('remember_token', $request->header('token'))->first();

        if( !$owner )
            return response()->json(["state" => 1, "data" => "No user"], 404);

        $newLayer = new Layer();
        $newLayer->name = $request->input('name');
        $newLayer->type = $request->input('type');
        $newLayer->fields = $request->input('fields');
        $newLayer->symbol = $request->input('symbol');
        $newLayer->owner_id = $owner->id;
        $newLayer->save();

        return response()->json(["state" => 0, "data" => $newLayer], 201);
    }

    public function deleteLayer(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'id' => 'required'
        ]);

        $layer = Layer::find($request->input('id'));

        if( !$layer )
            return response()->json(["state" => 1, "data" => "Layer not found"], 404);

        $layer->groups()->detach();

        $layer->delete();

        return response()->json(["state" => 0, "data" => "Layer deleted"], 201);
    }
}
