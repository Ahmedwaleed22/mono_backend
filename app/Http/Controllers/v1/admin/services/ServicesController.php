<?php

namespace App\Http\Controllers\v1\admin\services;

use App\Http\Controllers\v1\admin\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $services = Service::query()->paginate();
        return response()->json($services);
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
            'name' => 'required|string',
            'icon' => 'required|mimes:png,jpg,jpeg',
            'slug' => 'required|string',
            'is_primary' => 'boolean'
        ]);

        // Storing File in Amazon s3
        $file = $request->file('icon');
        $fileExtension = $file->getClientOriginalExtension();
        $fileName = Str::random(32) . '.' . $fileExtension;
        $file->storeAs('service_icons/', $fileName, 's3');

        $fileLocation = 'https://' . env('AWS_BUCKET') . '.' . 's3' . '.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/service_icons/' . $fileName;

        // Storing Service Data To Database
        $service = new Service();
        $service->name = $request->name;
        $service->icon = $fileLocation;
        $service->slug = $request->slug;
        $service->is_primary = $request->is_primary ?? false;
        $service->save();

        return response()->json($service);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $services = Service::where('id', $id)->firstOrFail();
        return response()->json($services);
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
            'name' => 'string',
            'icon' => 'mimes:png,jpg,jpeg',
            'slug' => 'string',
            'is_primary' => 'boolean'
        ]);

        $service = Service::where('id', $id)->first();
        $service->update($request->all());

        return response()->json($service);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $service = Service::where('id', $id)->firstOrFail();
        $service->delete();

        return response()->json([
            'message' => 'Successfully deleted service.'
        ], 202);
    }
}
