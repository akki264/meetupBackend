<?php

use App\Users;

$this->app['auth']->viaRequest('api', function ($request) {
    if ($request->header('Authorization')) {
        $key = explode(' ', $request->header('Authorization'));
        $user = Users::where('api_key', $key[1])->first();
        if (!empty($user)) {
            $request->request->add(['userid' => $user->id]);
        }
        return $user;
    }
});
