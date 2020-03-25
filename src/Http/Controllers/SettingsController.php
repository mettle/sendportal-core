<?php

namespace Sendportal\Base\Http\Controllers;

class SettingsController extends Controller
{
    /**
     * Display a listing of settings.
     */
    public function index()
    {
        return view('settings.index');
    }
}
