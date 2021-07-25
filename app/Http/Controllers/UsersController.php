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


    //To list Users and Friends
    public function users(Request $request)
    {


        $currentUser = Auth::user();

        $users = Users::where('id', '!=', $currentUser['id'])->whereNotIn('id', function ($query) use ($currentUser) {
            $query->select('friend_id')->from('usersconnect')->where('user_id', $currentUser['id']);
        })->get();

        // DB::enableQueryLog();
        $friends = Users::find($currentUser['id'])->getFriends()->join('users', 'users.id', 'usersconnect.friend_id')->get();
        // dd(DB::getQueryLog());

        return response()->json(['friends' => $friends, 'users' => $users, 'currentUser' => $currentUser], 201);
    }
    //API for user connect
    public function connectUser(Request $request)
    {
        $currentUser = Auth::user();

        $this->validate($request, ['friendId' => 'required']);
        if ($request->unFriend) {
            UsersConnect::where('user_id', $currentUser['id'])->where('friend_id', $request->friendId)->delete();
            UsersConnect::where('friend_id', $currentUser['id'])->where('user_id', $request->friendId)->delete();
        } else {




            UsersConnect::create([

                "user_id" => $currentUser->id,
                "friend_id" => $request->friendId,

            ]);

            UsersConnect::create([

                "user_id" => $request->friendId,
                "friend_id" => $currentUser->id,

            ]);
        }



        return response()->json(['message' => 'new friend has been connected', 200]);
    }

    //API to edit profile

    public function editProfile(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required'

        ]);
        $currentUser = Auth::user();
        $edit = Users::where('id', $currentUser['id'])->first();

        if ($edit) {
            $edit->update($request->only(['first_name', 'last_name']));
            return response()->json(['user' => $edit, 'message' => 'Profile has been updated', 200]);
        }
        return response()->json(['message' => 'User not found', 404]);
    }




    //API to add new schedule entry

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

    //To show list of Schedules

    public function getSchedule(Request $request)
    {

        $currentUser = Auth::user();
        $schedules = Schedule::where(
            "user_id",
            $currentUser['id']
        )->orWhere('friend_id', $currentUser['id'])->with(['friendUser', 'userData'])->get();

        return response()->json(['schedules' => $schedules, 'message' => 'all schedules'], 200);
    }

    //API to data for selected schedule
    public function openSchedule(Request $request)
    {
        $schedule = Schedule::where('id', $request->id)->first();

        if ($schedule) {
            return response()->json(['schedule' => $schedule, 'message' => 'found'], 200);
        } else {
            return response()->json(['message' => 'Not found'], 404);
        }
    }

    //API to Delete schedule
    public function deleteSchedule(Request $request)
    {
        Schedule::where('id', $request->id)->delete();
        return response()->json(['message' => 'Schedule Deleted'], 200);
    }
    //To edit schedule
    public function updateSchedule(Request $request)
    {
        $currentUser = Auth::user();

        $schedule = Schedule::where('id', $request->id)->first();

        if ($schedule) {
            $schedule->update($request->only(['title', 'meeting_time', 'description']));

            $schedule->save();

            return response()->json(['schedule' => $schedule, 'message' => 'schedule updated'], 200);
        } else {
            return response()->json(['message', 'not found'], 404);
        }
    }
}
