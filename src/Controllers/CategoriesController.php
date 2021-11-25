<?php

namespace Uasoft\Badaso\Module\Lms\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\Lms\Models\Category;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'sometimes|required|integer',
                'limit' => 'sometimes|required|integer',
                'relation' => 'nullable'
            ]);

            if ($request->has('page') || $request->has('limit')) {
                $category = Category::when($request->relation, function ($query) use ($request) {
                    return $query->with(explode(',', $request->relation));
                })->paginate($request->limit);
            } else {
                $category = Category::when($request->relation, function ($query) use ($request) {
                    return $query->with(explode(',', $request->relation));
                })->get();
            }

            $data['category'] = $category->toArray();
            return ApiResponse::success($data);
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function add(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:Uasoft\Badaso\Module\Lms\Models\Category',
                'icon' => 'nullable|string',
                'status' => 'required|integer'
            ]);

            Category::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'icon' => $request->icon,
                'status' => $request->status,
            ]);
            DB::commit();
            $category = Category::latest()->first();
            return ApiResponse::success($category);
        } catch (Exception $e) {
            DB::rollback();
            return ApiResponse::failed($e);
        }
    }
}
