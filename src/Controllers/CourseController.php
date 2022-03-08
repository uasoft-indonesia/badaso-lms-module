<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Illuminate\Http\Request;
use Uasoft\Badaso\Controllers\Controller;
class CourseController extends Controller
{
    public function add(Request $request)
    {
        return ApiResponse::success([
            'id' => 1,
            'name' => $request->input('name'),
            'subject' => $request->input('subject'),
            'room' => $request->input('room'),
        ]);
    }
}
