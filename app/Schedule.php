<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{

    protected $table = 'schedule';
    protected $fillable = ['user_id', 'friend_id', 'title', 'meeting_time', 'description', 'updated_by'];

    public function friendUser()
    {

        return $this->hasMany(Users::class, 'id', 'friend_id');
    }
    public function userData()
    {

        return $this->hasMany(Users::class, 'id', 'user_id');
    }
}
