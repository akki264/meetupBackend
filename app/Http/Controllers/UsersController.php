<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Users;
use App\UsersConnect;
use App\Schedule;
use Carbon\Carbon;
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

    //API to update timezone

    public function updateTimezone(Request $request)
    {
        $this->validate($request, [
            'usertimezone' => 'required'
        ]);
        $currentUser = Auth::user();
        $addtimezone = Users::where('id', $currentUser['id'])->first();
        if ($addtimezone) {
            $addtimezone->update($request->only(['usertimezone']));
            return response()->json(['timezone' => $addtimezone, 'message' => 'Timezone has been updated', 200]);
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

        $usertimezone = $currentUser->usertimezone;
        // echo ("simple method" . $userTz);

        // $usertimezone = Users::where(
        //     "id",
        //     $currentUser['id']
        // )->pluck('usertimezone');
        // $timezone = $usertimezone[0];
        // echo ($timezone);

        $datetime = Carbon::createFromFormat('Y-m-d H:i:s', $request->meeting_time, $usertimezone);
        // echo ($datetime);
        $datetime->setTimezone('EST');
        //echo ($datetime);


        $schedule = Schedule::create([
            "user_id" => $currentUser['id'],
            "friend_id" => $request->friend_id,
            "title" => $request->title,
            "meeting_time" => $datetime->toDateTimeString(),
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

        // $response = [];

        for ($i = 0; $i < count($schedules); $i++) {

            $getMeetTime = Carbon::createFromFormat('Y-m-d H:i:s', $schedules[$i]['meeting_time'], 'EST');
            $getMeetTime->setTimezone($currentUser->usertimezone);
            $now = Carbon::now('EST');
            $compare = Carbon::now('EST')->lt($getMeetTime);
            // if ($compare) {
            //     $schedules[$i]['meeting_time_as_timezone'] = $getMeetTime->toDateTimeString();
            // }
            $schedules[$i]['meeting_time_as_timezone'] = $getMeetTime->toDateTimeString();
            $concat = $compare ? 'Upcoming in ' : 'Missed call ';
            $schedules[$i]['message'] = $concat . $getMeetTime->diffForHumans($now);
            $schedules[$i]['status'] = $compare;

            // array_push($response, [
            //     ...$schedules[$i],
            //     'userMeetTime' => $getMeetTime->toDateTimeString()
            // ]);
        }

        // $scheduletime = Schedule::where(
        //     "user_id",
        //     $currentUser['id']
        // )->orWhere('friend_id', $currentUser['id'])->pluck('meeting_time');
        // echo ($scheduletime);

        // $usertimezone = Users::where(
        //     "id",
        //     $currentUser['id']
        // )->pluck('usertimezone');
        // $timezone = $usertimezone[0];
        // $updatedtime = Carbon::createFromFormat('Y-m-d H:i:s', $scheduletime[0]);
        // $updatedtime->setTimezone($timezone);
        // // $scheduletime->setTimezone($timezone);
        // echo ($timezone);
        // echo ($updatedtime);

        // echo ('break');

        return response()->json(['schedules' => $schedules, 'message' => 'all schedules'], 200);
    }

    //API to data for selected schedule
    public function openSchedule(Request $request)
    {
        $currentUser = Auth::user();
        $schedule = Schedule::where('id', $request->id)->first();

        if ($schedule) {


            $getMeetTime = Carbon::createFromFormat('Y-m-d H:i:s', $schedule['meeting_time'], 'EST');
            $getMeetTime->setTimezone($currentUser->usertimezone);

            $schedule['meeting_time_as_timezone'] = $getMeetTime->toDateTimeString();


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
