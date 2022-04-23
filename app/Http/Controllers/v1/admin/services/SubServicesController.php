<?php

namespace App\Http\Controllers\v1\admin\services;

use App\Http\Controllers\v1\admin\Controller;
use App\Models\Service;
use App\Models\SubService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $subServices = SubService::query()->paginate();
        return response()->json($subServices);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|mimes:png,jpg,jpeg',
            'description' => 'required|string',
            'search_tags' => 'required|string',
            'min_price' => 'required|numeric',
            'max_price' => 'required|numeric',
            'slug' => 'required|string|unique:sub_services',
            'service_id' => 'required|string'
        ]);

        $user = $request->user();

        // Storing File in Amazon s3
        $file = $request->file('image');
        $fileExtension = $file->getClientOriginalExtension();
        $fileName = Str::random(32) . '.' . $fileExtension;
        $file->storeAs('sub_service_images/', $fileName, 's3');

        $fileLocation = 'https://'. env('AWS_BUCKET') . '.' . 's3' . '.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/sub_service_images/' . $fileName;

        $service = Service::where('id', $request->service_id)->firstOrFail();

        $subService = new SubService;
        $subService->name = $request->name;
        $subService->image = $fileLocation;
        $subService->description = $request->description;
        $subService->search_tags = $request->search_tags;
        $subService->min_price = $request->min_price;
        $subService->max_price = $request->max_price;
        $subService->currency = 'EGP';
        $subService->slug = $request->slug;
        $subService->user_id = $user->id;
        $subService->service_id = $service->id;
        $subService->save();

        return response()->json($subService);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $subServices = SubService::where('id', $id)->firstOrFail();
        return response()->json($subServices);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'string|max:255',
            'image' => 'mimes:png,jpg,jpeg',
            'description' => 'string',
            'search_tags' => 'string',
            'min_price' => 'numeric',
            'max_price' => 'numeric',
            'slug' => 'string|unique:sub_services',
        ]);

        $requestData = $request->all();

        if ($request->hasFile('image')) {
            // Storing File in Amazon s3
            $file = $request->file('image');
            $fileExtension = $file->getClientOriginalExtension();
            $fileName = Str::random(32) . '.' . $fileExtension;
            $file->storeAs('sub_service_images/', $fileName, 's3');

            $fileLocation = 'https://'. env('AWS_BUCKET') . '.' . 's3' . '.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/sub_service_images/' . $fileName;

            $requestData['image'] = $fileLocation;
        }

        $subServices = SubService::where('id', $id)->firstOrFail();
        $subServices->update($requestData);

        return response()->json($subServices);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $subServices = SubService::where('id', $id)->firstOrFail();
        $subServices->delete();

        return response()->json([
            'message' => 'Successfully deleted sub service.'
        ], 202);
    }
}
