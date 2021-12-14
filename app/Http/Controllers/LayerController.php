<?php

namespace App\Http\Controllers;

use App\Models\Graphic;
use App\Models\Group;
use App\Models\Layer;
use App\Models\User;
use Illuminate\Http\Request;

class LayerController extends Controller
{
    public function getLayers(Request $request): \Illuminate\Http\JsonResponse
    {
        $layers = Layer::all();
        return response()->json(["state" => 0, "data" => $layers], 201);
    }

    public function getLayer(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'id' => 'required'
        ]);

        $id = $request->input('id');

        $layer = Layer::find( $id );

        if( $layer )
            return response()->json(["state" => 1, "data" => []], 404);

        return response()->json(["state" => 0, "data" => $layer ], 201);
    }

    public function getLayersByName(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => 'required'
        ]);

        $name = $request->input('name');

        $layers = Layer::where('name', 'LIKE', "%{$name}%")->get();

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
        ]);

        $layer = Layer::find( $request->input('layer_id') );

        if( !$layer )
            return response()->json(["state" => 1, "data" => "No layer"], 404);

        $graphic = new Graphic();
        $graphic->type = $request->input('type');
        $graphic->geometry = $request->input('geometry');
        $graphic->attributes = $request->input('attributes');
        $graphic->extent = $request->input('extent');
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

    public function addLayer(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required',
            'symbol' => 'required',
            'fields' => 'required',
        ]);

        $owner = User::find( 1 );

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
