<?php

namespace App\Http\Controllers;

use App\Events\MessageCreated;
use App\Models\Conversation;
use App\Models\Recipient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class MessagesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $user = Auth::user();
        $conversation = $user->conversations()->with(['participants' => function($builder) use($user){
            $builder->where('id' , '<>' , $user->id);
        }])->findOrFail($id);
        $messages = $conversation->messages()
            ->with('user')
            ->where(function($query) use($user){
                $query->where('user_id' , $user->id)
                    ->orWhereRaw('id IN (select message_id from recipients
             where recipients.message_id = messages.id AND
           recipients.user_id = ?
            AND recipients.deleted_at IS NULL)' , [$user->id]);

            })->paginate(300);


        return [
            'conversation' => $conversation ,
           'messages' => $messages ,
            ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'message' => [Rule::requiredIf(function () use ($request) {
                return !$request->hasFile('attachment');}),
                ],
            'attachment' => 'nullable|file|max:10240',
            'user_id' => [Rule::requiredIf(function () use ($request) {
                return !$request->input('conversation_id');
            }),
                'integer', 'exists:users,id'],


            'conversation_id' => [Rule::requiredIf(function () use ($request) {
                return !$request->input('user_id');
            }),
                'integer', 'exists:conversations,id'],

        ]);
        $conversation_id = $request->input('conversation_id');
        $user_id = $request->input('user_id');
        DB::beginTransaction();
        try {
            if ($conversation_id) {
                $conversation = $user->conversations()->findOrFail($conversation_id);
            } else {
                $conversation = Conversation::where('type', '=', 'peer')
                    ->whereHas('participants', function ($builder) use ($user_id) {

                        $builder->where('user_id', $user_id);
                    })
                    ->whereHas('participants', function ($builder) use ($user) {

                        $builder->where('user_id', $user->id);
                    })
                    ->first();
                if (!$conversation) {
                    $conversation = Conversation::create([
                        'user_id' => $user->id,
                        'type' => 'peer',
                    ]);
                    $conversation->participants()->attach([
                        $user->id => ['joined_at' => now()], $user_id => ['joined_at' => now()]
                    ]);
                }
            }
            $type = 'text';
            $message = $request->post('message');
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $message = [
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mimetype' => $file->getMimeType(),
                    'file_path' => $file->store('attachments', [
                        'disk' => 'public'
                    ]),
                ];
                $type = 'attachment';
            }
            $message = $conversation->messages()->create([
                'user_id' => $user->id,
                'type' => $type ,
                'body' => $message
            ]);
            DB::statement('
               INSERT iNTO `recipients` (user_id , message_id )
             select user_id , ? from participants
            WHERE conversation_id = ? AND user_id <> ?
            ', [$message->id, $conversation->id , $user->id] );
            DB::commit();
            $message->load('user');
            broadcast(new MessageCreated($message));
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        $conversation->update([
            'last_message_id' => $message->id
        ]);

        return $message;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();

        // Retrieve the message
        $message = $user->sentMessages()->where('id', $id)->first();

        // Soft delete the message by updating the 'deleted_at' column
        $user->sentMessages()
            ->where('id', '=', $id)
            ->update([
                'deleted_at' => Carbon::now(),
            ]);


        if ($message && $message->body) {



            // If the body contains an attachment, delete it from storage
            if (isset($message['file_path'])) {
                $attachmentPath = storage_path('storage/' . $message['file_path']);

                if (file_exists($attachmentPath)) {
                    storage::delete($attachmentPath) ; // Deletes the file from storage
                }
            }
        }

        if ($request->target == 'me') {

            Recipient::where([
                'user_id' => $user->id,
                'message_id' => $id,
            ])->delete();

        } else {
            Recipient::where([
                'message_id' => $id,
            ])->delete();
        }

        return [
            'message' => 'deleted',
        ];
    }
}

