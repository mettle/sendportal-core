<?php

namespace Sendportal\Base\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Sendportal\Base\Http\Controllers\Controller;

class FileController extends Controller
{
    public function store(Request $request)
    {
        request()->validate([
            'file'  => 'required|mimes:jpg,jpeg,png|max:2048',
        ]);

        $file = $request->file->storePublicly('images');

        return response()->json([
            "file" => Storage::url($file)
        ]);
    }
}