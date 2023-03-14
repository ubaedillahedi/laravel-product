<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use stdClass;

class ProductController extends Controller
{

    public function index()
    {
        try {
            $products = Product::where('enable', 1)->get();
            if($products->isEmpty()) return response()->json(['statusCode' => 200, 'message' => 'Not found!', 'data' => []], 200);
            foreach ($products as &$product) {
                if(!$product->categories()->get()->isEmpty()) {
                    $categories = $product->categories()->get()->toArray();
                    $product->categories = array_map(function($category){
                        $tmp = new stdClass;
                        $tmp->id = $category['id'];
                        $tmp->name = $category['name'];
                        return $tmp;
                    }, $categories);
                }

                if(!$product->images()->get()->isEmpty()) {
                    $images = $product->images()->get()->toArray();
                    $product->images = array_map(function($image){
                        $tmp = new stdClass;
                        $tmp->id = $image['id'];
                        $tmp->name = $image['name'];
                        $tmp->file = asset('storage/images/' . $image['file']);
                        return $tmp;
                    }, $images);
                }
            }
            $response = ['statusCode' => 200, 'message' => 'Success!', 'data' => $products];
        } catch (\Throwable $th) {
            Log::debug('Error: ' . json_encode($th->getMessage()));
            $response = [
                'statusCode' => 400,
                'message' => 'Failed!'
            ];
        }
        
        return response()->json($response, $response['statusCode']);
    }

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
            DB::beginTransaction();
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
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
            DB::commit();

        } catch (\Throwable $th) {
            Log::debug('Error: ' . json_encode($th->getMessage()));
            $response = [
                'statusCode' => 400,
                'message' => 'Failed!'
            ];
            DB::rollBack();
        }
        return response()->json($response, $response['statusCode']);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'enable' => 'required',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return response([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $response = ['statusCode' => 200, 'message' => 'Success!', 'data' => []];
        try {
            DB::beginTransaction();
            $product = Product::find($id);
            $product->name = $request->name;
            $product->description = $request->description;
            $product->enable = $request->enable;
            $product->save();

            if(isset($request->categories)) {
                $expCat = explode(',', $request->categories);
                $product->categories()->sync($expCat);
            }

            if(isset($request->images)) {
                $expImg = explode(',', $request->images);
                $product->image()->sync($expImg);
            }
            $response = ['statusCode' => 200, 'message' => 'Success!', 'data' => []];
            DB::commit();
        } catch (\Throwable $th) {
            Log::debug('Error: ' . json_encode($th->getMessage()));
            $response = [
                'statusCode' => 400,
                'message' => 'Failed!'
            ];
            DB::rollBack();
        }

        return response()->json($response, $response['statusCode']);

    }

}
