<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Controllers\Controller;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $validation = $request->validate([
            'file'  =>  'required|file|image|mimes:jpeg,png,gif,jpg|max:2048'
        ]);

        $file = $validation['file'];

        $fileName = 'lesson-'.time().'.'.$file->getClientOriginalExtension();

        $path = $file->storeAs('files', $fileName);

        Storage::url($path);

        return ApiResponse::success($path);
    }

    public function delete($fileName)
    {
        $status = Storage::disk("public")->delete("files/".$fileName);

        if ($status) {
            return ApiResponse::success("succesfully delete file");
        }

        return ApiResponse::failed("there is no file with given name");
    }
}
