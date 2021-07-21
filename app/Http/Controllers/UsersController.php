<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Users;
use App\UsersConnect;
use App\Schedule;
use Illuminate\Console\Scheduling\Schedule as SchedulingSchedule;

class UsersController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }



    public function users(Request $request)
    {

        $currentUser = Auth::user();

        $users = Users::where('email', '!=', $currentUser['email'])->whereNotIn('id', function ($query) use ($currentUser) {
            $query->select('friend_id')->from('usersconnect')->where('user_id', $currentUser['id']);
        })->get();

        $friends = Users::find($currentUser['id'])->getFriends()->get();

        return response()->json(['friends' => $friends, 'users' => $users, 'currentUser' => $currentUser], 201);
    }

    public function connectUser(Request $request)
    {
        $currentUser = Auth::user();

        $this->validate($request, ['friendId' => 'required']);

        $findFriend = Users::find($currentUser['id'])->getFriends()->where('friend_id', $request->friendId)->first();
        if ($findFriend) {

            $friends = Users::find($currentUser['id'])->getFriends()->get();
            return response()->json(['friends' => $friends, 'message' => 'already friend'], 200);
        }

        UsersConnect::create([

            "user_id" => $currentUser->id,
            "friend_id" => $request->friendId,

        ]);

        UsersConnect::create([

            "user_id" => $request->friendId,
            "friend_id" => $currentUser->id,

        ]);

        $friends = Users::find($currentUser['id'])->getFriends()->get();

        return response()->json(['friends' => $friends, 'message' => 'new friend has been connected', 200]);
    }

    public function addSchedule(Request $request)
    {
        $this->validate($request, [
            'friend_id' => 'required',
            'title' => 'required',
            'meeting_time' => 'required',
            'description' => 'required',

        ]);
        $currentUser = Auth::user();
        $schedule = Schedule::create([
            "user_id" => $currentUser['id'],
            "friend_id" => $request->friend_id,
            "title" => $request->title,
            "meeting_time" => $request->meeting_time,
            "description" => $request->description


        ]);

        return response()->json(['schedule' => $schedule, 'message' => 'New Schedule Created'], 200);
    }

    public function getSchedule(Request $request)
    {

        $currentUser = Auth::user();
        $schedules = Schedule::where(
            "user_id",
            $currentUser['id']
        )->orWhere('friend_id', $currentUser['id'])->get();

        return response()->json(['schedules' => $schedules, 'message' => 'all schedules'], 200);
    }
    public function openSchedule(Request $request)
    {
        $schedule = Schedule::where('id', $request->id)->first();

        if ($schedule) {
            return response()->json(['schedule' => $schedule, 'message' => 'found'], 200);
        } else {
            return response()->json(['message' => 'Not found'], 404);
        }
    }

    public function deleteSchedule(Request $request)
    {
        Schedule::where('id', $request->id)->delete()->first();
        return response()->json(['message' => 'Schedule Deleted'], 200);
    }
    public function updateSchedule(Request $request)
    {
        $currentUser = Auth::user();

        $schedule = Schedule::where('id', $request->id)->first();

        if ($schedule) {
            $schedule->update($request->only(['title', 'meeting_time', 'description']));
            $schedule->updated_by = $currentUser->id;
            $schedule->save();

            return response()->json(['schedule' => $schedule, 'message' => 'schedule updated'], 200);
        } else {
            return response()->json(['message', 'not found'], 404);
        }
    }
}