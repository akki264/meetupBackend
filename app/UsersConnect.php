<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsersConnect extends Model
{
    //
    protected $table = 'usersconnect';
    protected $fillable = ['friend_id', 'user_id'];
}
