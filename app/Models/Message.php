<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory , SoftDeletes ;

    protected $fillable = ['user_id'
        , 'conversation_id'
        , 'body'
        , 'type'];

    protected $casts = [
        'body' => 'json'
    ];

    public function conversation() : BelongsTo {

        return $this->belongsTo(Conversation::class  , 'conversation_id' );
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => __('user')
        ]);
    }

        public function recipients() : BelongsToMany {

        // this with pivot to return extra columns with the relation from the pivot table
            return $this->belongsToMany(User::class , 'recipients')->withPivot([
                'read_at' , 'deleted_at' ,
            ]);
        }



    }
