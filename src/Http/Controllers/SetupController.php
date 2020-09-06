<?php

namespace Sendportal\Base\Http\Controllers;

use Exception;
use Illuminate\View\View;
use Sendportal\Base\Models\User;

class SetupController extends Controller
{
    /**
     * @return View
     */
    public function index()
    {
        try {
            //if (User::exists()) {
            //    return redirect()->route('login');
            //}
        } catch (Exception $e) {
            //
        }

        return view('sendportal::setup.index');
    }
}
