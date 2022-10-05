<?php

namespace App\Http\Controllers;

use App\Models\playersitem;
use App\Models\Item;
use App\Http\Resources\PlayerResource;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PlayersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new Response(
            Player::query()->
            select(['id', 'name', 'hp', 'mp','money',])->
            get());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      return new Response(
        Player::query()->find($id));
      
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      return new Response(
        Player::query()->
        insertGetId([
          'name' => $request['name'],
          'hp' => $request['hp'],
          'mp' => $request['mp'],
          'money' => $request['money'],

              ]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      return new Response(
        Player::query()->
        where('id', $id)->
        update([
          'name' => $request['name'],
          'hp' => $request['hp'],
          'mp' => $request['mp'],
          'money' => $request['money'],

        ]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      return new Response(
        Player::query()->
        where('id', $id)->delete());

    }

    public function edit($id)
    {
       $edit=Player::find($id);
       return new Response($edit);
    }

    public function addItem(Request $request, $id)
    {
    $json_count = playersitem::query()->
    select('count')->
    where('player_id', $id)->
    where('item_id', 
    $request['item_id'])->
    get();

    $count = json_decode($json_count,true);
    if(!$count){
        playersitem::query()->
        insert(['player_id' => $id, 
        'item_id' => 
        $request['item_id'], 'count' =>
        $request['count']]
      );
    }
    else{
        playersitem::query()->
        where('player_id', $id)->
        where('item_id', $request['item_id'])->
        update(['count' =>
        $request['count'] + $count[0]["count"]]
      );
    }

    return new Response(
        playersitem::query()->
        select('item_id', 'count')->
        where('player_id', $id)->
        where('item_id',
        $request['item_id'])->
        get()
      );
    }

    public function useItem(Request $request, $id)
    {


      $playeritem=playersitem::query()->where([['player_id','=',$id],
      ['item_id','=',$request->item_id]])->
      select(['count'])->
      get();
      if($request->count <= 0)


      {
        return new Response("\"count\" Must Be > 0 !");
      }
        if($request->count > $playeritem[0]->count)
      {
        if($playeritem[0]->count <= 0)
            {
            $itemname=Item::query()->
            select(['name'])->
            where('id',$request->item_id)->
            get();
            $name = $itemname[0]->name;
            return new Response("Number of items $request->item_id : $name is 0");
            }
            $HaveCount = $playeritem[0]->count;
            return new Response("You Only Have $HaveCount of item , But your input count is = $request->count!!");
        }
        else
        {
            $count=$playeritem[0]->count;           
            $player=Player::find($id);              
            $item=Item::find($request->item_id);    
            $string='Succesful! 成功！';                   
            while($request->count > 0)
        {
        
        
          //item : hp counter id here
                switch($item->id)
                {
                    case 1:
                    {
                        //playerHp >= 200
                        if($player->hp>=200)
                        {
                            $string = 'Hp is maxed out';
                            goto endloop;
                        }
                        //playerHp < 200
                        else
                        {
                            $count--;
                            $hp = ($player->hp+$item->value) >200 ? 200 : ($player->hp+$item->value);
                            $player->hp=$hp;
                            $player->save();
                            playersitem::query()->
                            where([['player_id','=',$id] ,
                                    ['item_id','=',$request->item_id]])->
                            update(['count'=>$count]);
                        }
                        break;
                    }
                    case 2:
                    {
                            //player Mp more or equal to >= 200
                            if($player->mp>=200)
                            {
                                $string = 'MP is maxed out!';
                                goto endloop;
                            }
                            //player Mp less than < 200
                            else
                            {
                                $count--;
                                $mp = ($player->mp+$item->value) >200 ? 200 : ($player->mp+$item->value);
                                $player->mp=$mp;
                                $player->save();
                                playersitem::query()->
                                where([['player_id','=',$id],
                                        ['item_id','=',$request->item_id]])->
                                update(['count'=>$count]);
                            }//end else player Mp is less than < 200
                            break;
                    }
                }
                $request->count--;
            }
            endloop:
            return response()->json(
                    [
                        'Result'=>$string,
                        'itemId'=>$request->item_id,
                        'count'=>$count,
                        'player'=> 
                        [
                           'id'=> $id,
                           'hp'=>$player->hp,
                           'mp'=>$player->mp
                        ]
                    ]);
        }
    }
    
}

