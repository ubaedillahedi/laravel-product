<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    public function index()
    {
        try {
            $images = Image::select(['id', 'name', 'file'])->where('enable', 1)->orderBy('name', 'asc')->get();
            if(!$images->isEmpty()) {
                foreach ($images as &$image) {
                    $image->file = asset('storage/images/' . $image->file);
                }
            }
            $response = [
                'statusCode' => 200,
                'message' => 'Success!',
                'data' => $images
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'file' => 'file|required|mimes:jpg,jpeg,png|max:1000',
            'enable' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }
        $response = [];
        try {
            $fileName = null;
            if($request->hasFile('file')) {
                $path = Storage::path('public/images');
                File::isDirectory($path) or File::makeDirectory($path, 0755);

                $fileExt = $request->file('file')->getClientOriginalExtension();
                $fileName = time() . ".$fileExt";
                $request->file('file')->storeAs('public/images', $fileName);
            }
            Image::create(['name' => $request->name, 'file' => $fileName, 'enable' => $request->enable]);
            $response = [
                'statusCode' => 200,
                'message' => 'Success!'
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

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'enable' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }
        $response = [];
        try {
            $image = Image::find($id);
            if($image == null) return response()->json(['statusCode' => 404, 'message' => 'Not found!']);
            if($request->hasFile('file')) {
                $path = Storage::path('public/images');
                File::isDirectory($path) or File::makeDirectory($path, 0755);

                $fileExt = $request->file('file')->getClientOriginalExtension();
                $fileName = time() . ".$fileExt";
                unlink($path.'/'.$image->file);
                $request->file('file')->storeAs('public/images', $fileName);
                $image->file = $fileName;
            }
            $image->name = $request->name;
            $image->enable = $request->enable;
            $image->save();
            $response = [
                'statusCode' => 200,
                'message' => 'Success!'
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

    public function delete(Request $request, $id)
    {
        $response = [];
        try {
            $image = Image::find($id);
            if($image == null) return response()->json(['statusCode' => 404, 'message' => 'Not found!']);
            $path = Storage::path('public/images');
            unlink($path.'/'.$image->file);
            $image->delete();
            $response = [
                'statusCode' => 200,
                'message' => 'Success!'
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
