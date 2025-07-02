<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

    protected $fillable = ['title','content','user_id'];
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function user(){
        return $this->belongsTo(User::class);

    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id==$user->id;
    }
}
