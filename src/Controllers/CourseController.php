<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Illuminate\Http\Request;
use Uasoft\Badaso\Controllers\Controller;
class CourseController extends Controller
{
    public function add(Request $request)
    {
        return ApiResponse::success();
    }
}
