<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Models\LessonMaterial;
use Uasoft\Badaso\Module\LMSModule\Models\MaterialComment;

class MaterialCommentController extends Controller
{
    public function add(Request $request)
    {
        try {
            $user = auth()->user();
            $request->validate([
                'material_id' => 'required|integer',
                'content' => 'required|string|max:65535',
            ]);

            $announcement = LessonMaterial::where('id', $request->input('material_id'))
                ->first();

            if (! $announcement) {
                throw ValidationException::withMessages([
                    'material_id' => 'Lesson Material not found',
                ]);
            }

            $materialComment = MaterialComment::create([
                'material_id' => $request->input('material_id'),
                'content' => $request->input('content'),
                'created_by' => $user->id,
            ]);

            return ApiResponse::success($materialComment->toArray());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }
}
