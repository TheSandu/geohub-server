<?php

namespace Database\Seeders;

use App\Models\Graphic;
use App\Models\Group;
use App\Models\Layer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */


    private $groups = [
        [
            "name"=> "Administrators",
            "owner_id"=> 1,
        ],
        [
            "name"=> "Moderators",
            "owner_id"=> 1,
        ],
        [
            "name"=> "Editors",
            "owner_id"=> 1,
        ],
    ];

    private $layers = [
        [
            'name' => 'Buildings Layers',
            'type' => 'polygon',
            'symbol' => "{\"type\":\"simple-fill\",\"color\":\"#ff0000\",\"outline\":{\"color\":\"#424242\",\"width\":2}}",
            'fields' => "[{\"name\":\"address\",\"type\":\"string\"},{\"name\":\"sector\",\"type\":\"string\"},{\"name\":\"number\",\"type\":\"number\"}]",
            'owner_id' => 1,
        ],
        [
            'name' => 'Streets Layer',
            'type' => 'line',
            'symbol' => "{\"type\":\"simple-line\",\"color\":\"#424242\",\"width\":4}",
            'fields' => "[{\"name\":\"address\",\"type\":\"string\"},{\"name\":\"sector\",\"type\":\"string\"},{\"name\":\"number\",\"type\":\"number\"}]",
            'owner_id' => 1,
        ],
        [
            'name' => 'Stations Layer',
            'type' => 'point',
            'symbol' => "{\"type\":\"simple-marker\",\"color\":\"#ff0000\",\"outline\":{\"color\":\"#424242\",\"width\":2}}",
            'fields' => "[{\"name\":\"address\",\"type\":\"string\"},{\"name\":\"sector\",\"type\":\"string\"},{\"name\":\"number\",\"type\":\"number\"}]",
            'owner_id' => 1,
        ],
    ];

    private $graphics = [
        [
            "type" => 'polygon',
            'geometry' =>"{\"spatialReference\":{\"latestWkid\":3857,\"wkid\":102100},\"rings\":[[[3174264.7486462547,5936159.23215577],[3214164.8774110978,5936617.854325482],[3187717.6656244393,5920566.078385602],[3174264.7486462547,5936159.23215577]]]}",
            'attributes' => "{\"address\":\"Str Stefan cel mare\",\"sector\":\"Centru\",\"number\":168}",
            'extent' => "{\"spatialReference\":{\"latestWkid\":3857,\"wkid\":102100},\"xmin\":3174264.7486462547,\"ymin\":5920566.078385602,\"xmax\":3214164.8774110978,\"ymax\":5936617.854325482}",
        ],
    ];

    public function run()
    {

        \App\Models\User::factory(3)->create();

        $user = User::find(1);
        $user->email = "fcole@example.org";
        $user->password =  Hash::make("admin321");
        $user->save();

        if(Group::all()->count() == 0) {
            foreach ($this->groups as $id => $group) {
                Group::create([
                    'name' => $group['name'],
                    'owner_id' => $group['owner_id'],
                ]);
            }
        }

        if(Layer::all()->count() == 0) {
            foreach ($this->layers as $id => $layer) {
                Layer::create([
                    'name' => $layer['name'],
                    'type' => $layer['type'],
                    'symbol' => $layer['symbol'],
                    'fields' => $layer['fields'],
                    'owner_id' => $layer['owner_id'],
                ]);
            }
        }

        foreach (Group::all() as $id => $group) {
            $group->layers()->attach( $id + 1 );
        }

        foreach (Group::all() as $id => $group) {
            $group->users()->attach( $id + 1, ['user_role' => 'editor']);
        }

        if(Graphic::all()->count() == 0) {
            foreach ($this->graphics as $id => $graphic) {
                Graphic::create([
                    'type' => $graphic['type'],
                    'geometry' => $graphic['geometry'],
                    'attributes' => $graphic['attributes'],
                    'extent' => $graphic['extent'],
                ]);
            }
        }

        foreach (Layer::all() as $id => $layer) {
            if( $layer->type === 'polygon' )
                $layer->graphics()->attach( 1 );
        }

    }
}
