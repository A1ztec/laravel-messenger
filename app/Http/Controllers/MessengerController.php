<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessengerController extends Controller
{
    public function index( string $id = null){

        $user = Auth::user();
        $friends = User::where('id' , '<>' , $user->id)
            ->orderby('name')
            ->paginate();




        return view('messenger' , compact('friends' ));
    }
    public function friends( string $id = null){

        $user = Auth::user();
        $friends = User::where('id' , '<>' , $user->id)
            ->orderby('name')
            ->paginate();

        return $friends;
    }


    public function friendsSearch(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');

        $friends = collect();

        if (strlen($search) >= 3) {
            $friends = User::where('id', '<>', $user->id)
                ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                ->orderBy('name')
                ->paginate(10);
        }


        return response()->json(['data' => $friends->items()]);
    }

    public function friendConversation($id)
    {
        $user = Auth::user();
        $friend = User::findOrFail($id);

        $conversation = $user->conversations()
            ->whereHas('participants', function ($query) use ($friend) {
                $query->where('user_id', $friend->id);
            })->with(['participants' => function ($query) use ($user) {
                $query->where('user_id', '<>', $user->id);
            }])->with('messages')
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_id' => $user->id,
                'type' => 'peer',
            ]);
            $conversation->participants()->attach([
                $user->id => ['joined_at' => now()], $friend->id => ['joined_at' => now()]
            ]);


            $conversation->load('participants');
            $conversation->messages = [];
        }

        return response()->json(['body' => $conversation]);
    }





}
