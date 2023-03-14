<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'enable' => 'required',
            'description' => 'required',
            'categories' => 'required',
            'images' => 'required',
        ]);
        $response = [];
        if ($validator->fails()) {
            return response([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        try {
            $product = Product::create([
                'name' => $request->name,
                'descrioption' => $request->description,
                'enable' => $request->enable,
            ]);

            $expCat = explode(',', $request->categories);
            $expImg = explode(',', $request->images);

            $product->categories()->sync($expCat);
            $product->images()->sync($expImg);

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
        return response()->json($response, $response['statusCode']);
    }
}
