<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class ApiLocationController extends ApiController
{

    public function index() {
        return Location::select(['id','title','latitude','longitude','address'])->get();
    }
}
