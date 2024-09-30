<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationsController extends Controller
{
    public function index()
    {
     $user = Auth::user();
     return $user->conversations()->with([
         'lastMessage',
         'participants' => function($builder) use($user){
             $builder->where('id' , '<>' , $user->id);
         }
     ])->withCount([
         'recipients as new_messages' => function($builder) use($user) {
         $builder->where('recipients.user_id' , $user->id)
             ->whereNull('read_at');
         }
     ])->paginate();
    }

    public function show($id){
        $user = Auth::user();
        return $user->conversations()->with([
            'lastMessage',
            'participants' => function($builder) use($user){
                $builder->where('id' , '<>' , $user->id);
            }
        ])->withCount([
            'recipients as new_messages' => function($builder) use($user) {
                $builder->where('recipients.user_id' ,'=' , $user->id)
                    ->whereNull('read_at');
            }
        ])->findOrFail($id);
    }

    public function markAsRead($id){
        Recipient::where('user_id' , Auth::id())
            ->whereNull('read_at')
            ->whereRaw('message_id IN (SELECT id FROM messages WHERE conversation_id = ?)' , [$id])
            ->update(['read_at' => now()]);
        return ['message' => 'Messages Marked As Read'];
    }

    public function destroy(Conversation $conversation){
        Recipient::where('user_id' , Auth::id())
            ->whereRaw('message_id IN (SELECT id FROM messages WHERE conversation_id = ?)' , [$conversation->id])
            ->delete();
        return ['message' => 'Conversation Deleted'];
    }

    public function addParticipant(Conversation $conversation , Request $request){

        $request->validate([
           'user_id' => ['required' , 'integer', 'exists:users,id']
        ]);
        $conversation->participants()
            ->attach($request->input('user_id') , ['joined_at'=>now()]);

    }

    public function removeParticipant(Conversation $conversation , Request $request){

        $request->validate([
            'user_id' => ['required' , 'integer', 'exists:users,id']
        ]);
        $conversation->participants()
            ->detach($request->input('user_id'));

    }


}
