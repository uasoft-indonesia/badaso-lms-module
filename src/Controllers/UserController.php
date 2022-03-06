<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;

class UserController extends Controller 
{
    public function home()
    {
        $data['body'] = "hello you are in api";

        return ApiResponse::success($data);
    }
}
