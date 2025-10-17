<?php

namespace App\Services;

use App\Models\Calendar;
use App\Models\Notification;
use App\Models\User;
use App\Models\Role;
use Berkayk\OneSignal\OneSignalClient;

class CalendarService
{


    public function index(array $data)
    {

        return Calendar::where('user_id',$data['user_id'])->get();
    }

    public function store(array $data)
    {
        return Calendar::create($data);
    }

    public function show(int $id,array $data)
    {
        return Calendar::where('user_id',$data['user_id'])->findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $calendar = Calendar::findOrFail($id);
        $calendar->update($data);
        return $calendar;
    }

    public function destroy(int $id)
    {
        $calendar = Calendar::findOrFail($id);
        $calendar->delete();
        return true;
    }
}
