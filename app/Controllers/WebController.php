<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class WebController extends BaseController
{
    public function index()
    {
        return view('game');
    }
    
    public function login()
    {
        return view('login');
    }
}
