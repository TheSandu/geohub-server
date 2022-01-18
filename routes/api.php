<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LayerController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware([
    'api.auth',
//    'auth:api',
    'cors'
])->group(function () {
    Route::post('/group/getGroups', [GroupController::class, 'getGroups']);
    Route::post('/group/getGroup', [GroupController::class, 'getGroup']);
    Route::post('/group/getGroupsByName', [GroupController::class, 'getGroupsByName']);

    Route::post('/group/getLayersByGroup', [GroupController::class, 'getLayersByGroup']);
    Route::post('/group/getMembersByGroup', [GroupController::class, 'getMembersByGroup']);

    Route::post('/group/attachLayerToGroup', [GroupController::class, 'attachLayerToGroup']);
    Route::post('/group/detachLayerToGroup', [GroupController::class, 'detachLayerToGroup']);
    Route::post('/group/attachMemberToGroup', [GroupController::class, 'attachMemberToGroup']);
    Route::post('/group/detachMemberToGroup', [GroupController::class, 'detachMemberToGroup']);

    Route::post('/group/addGroup', [GroupController::class, 'addGroup']);
    Route::post('/group/deleteGroup', [GroupController::class, 'deleteGroup']);

    Route::post('/layer/getLayer', [LayerController::class, 'getLayer']);
    Route::post('/layer/getLayers', [LayerController::class, 'getLayers']);
    Route::post('/layer/getLayersByName', [LayerController::class, 'getLayersByName']);

    Route::post('/layer/getLayerGraphics', [LayerController::class, 'getLayerGraphics']);
    Route::post('/layer/getLayerBordering', [LayerController::class, 'getLayerBordering']);
    Route::post('/layer/getUserLayerBordering', [LayerController::class, 'getUserLayerBordering']);

    Route::post('/layer/addLayer', [LayerController::class, 'addLayer']);
    Route::post('/layer/addBorder', [LayerController::class, 'addBorder']);
    Route::post('/layer/deleteLayer', [LayerController::class, 'deleteLayer']);
    Route::post('/layer/addGraphicToLayer', [LayerController::class, 'addGraphicToLayer']);
    Route::post('/layer/updateLayerGraphic', [LayerController::class, 'updateLayerGraphic']);
    Route::post('/layer/deleteLayerGraphic', [LayerController::class, 'deleteLayerGraphic']);

    Route::post('/user/getUser', [UserController::class, 'getUser']);
    Route::post('/user/getCurentUser', [UserController::class, 'getCurentUser']);

    Route::post('/user/getUsers', [UserController::class, 'getUsers']);
    Route::post('/user/getUsersByName', [UserController::class, 'getUsersByName']);
    Route::post('/user/getUserLayers', [UserController::class, 'getUserLayers']);
    Route::post('/user/addUser', [UserController::class, 'addUser']);

    Route::post('/logout', [UserController::class, 'logout']);
});

Route::middleware(['cors'])->group(function () {
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register', [UserController::class, 'register']);
});
