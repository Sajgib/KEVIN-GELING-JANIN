<?php

namespace Database\Seeders;

use App\Models\item;
use App\Models\Player;
use App\Models\playersitem;
use Illuminate\Database\Seeder;

class PlayerItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $PlayerData = Player::query()->
        select(['id'])->
        get();

        $ItemData = Item::query()->
        select(['id'])->
        get();

        $count = 1;
        foreach($PlayerData as $playerid)
        {
            foreach($ItemData as $itemid)
            {
                $player_item = new playersitem;
                $player_item->player_id = $playerid['id'];
                $player_item->item_id = $itemid['id'];
                $player_item->count = "1";
                $player_item->save();
            }
        }
    }
}
