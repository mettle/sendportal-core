<?php

namespace Sendportal\Base\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('sendportal::dashboard');
    }
}
