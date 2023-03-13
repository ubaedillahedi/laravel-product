<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::select(['id', 'name'])->where('enable', 1)->orderBy('name', 'asc')->get();
            $response = [
                'statusCode' => 200,
                'message' => 'Success!',
                'data' => $categories
            ];
        } catch (\Throwable $th) {
            Log::debug('Error: ' . json_encode($th->getMessage()));
            $response = [
                'statusCode' => 400,
                'message' => 'Failed!'
            ];
        }
        return response()->json($response);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'enable' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        try {
            Category::create([
                'name' => $request->name
            ]);
            $response = [
                'statusCode' => 200,
                'message' => 'Success!',
                'data' => []
            ];
        } catch (\Throwable $th) {
            Log::debug('Error: ' . json_encode($th->getMessage()));
            $response = [
                'statusCode' => 400,
                'message' => 'Failed!'
            ];
        }

        return response()->json($response);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'enable' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        try {
            $category = Category::find($id);
            if($category == null) return response()->json(['statusCode' => 404, 'message' => 'Not found!']);
            $category->name = $request->name;
            $category->enable = $request->enable;
            $category->save();
            $response = [
                'statusCode' => 200,
                'message' => 'Success!',
                'data' => []
            ];
        } catch (\Throwable $th) {
            Log::debug('Error: ' . json_encode($th->getMessage()));
            $response = [
                'statusCode' => 400,
                'message' => 'Failed!'
            ];
        }
        return response()->json($response);
    }

    public function delete(Request $request, $id)
    {
        try {
            $category = Category::find($id);
            if($category == null) return response()->json(['statusCode' => 404, 'message' => 'Not found!']);
            $category->delete();
            $response = [
                'statusCode' => 200,
                'message' => 'Success!',
                'data' => []
            ];
        } catch (\Throwable $th) {
            Log::debug('Error: ' . json_encode($th->getMessage()));
            $response = [
                'statusCode' => 400,
                'message' => 'Failed!'
            ];
        }
        return response()->json($response);
    }
}
