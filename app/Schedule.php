<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{

    protected $table = 'schedule';
    protected $fillable = ['user_id', 'friend_id', 'title', 'meeting_time', 'description', 'updated_by'];
}
