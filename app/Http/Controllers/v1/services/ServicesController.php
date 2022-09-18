<?php

namespace App\Http\Controllers\v1\services;

use App\Http\Controllers\v1\Controller;
use App\Models\Service;
use Illuminate\Support\Facades\Cache;
use function response;

class ServicesController extends Controller
{
    public function getAllServices(): \Illuminate\Http\JsonResponse
    {
        $services = Service::orderBy('is_primary', 'DESC')->paginate();
        return response()->json($services);
    }

    public function getPrimaryServices(): \Illuminate\Http\JsonResponse
    {
        $services = Cache::remember('services', 86400, function() {
            return Service::where('is_primary', true)->get();
        });

        return response()->json($services);
    }

//    public function createService(Request $request): \Illuminate\Http\JsonResponse
//    {
//        $request->validate([
//            'name' => 'required|string',
//            'icon' => 'required|mimes:png,jpg,jpeg',
//            'slug' => 'required|string'
//        ]);
//
//        $user = $request->user();
//
//        if (!($user->account_type == 'worker')) {
//            return response()->json([
//                'error' => 'Your account is not a worker account.'
//            ], 409);
//        }
//
//        // Storing File in Amazon s3
//        $file = $request->file('icon');
//        $fileExtension = $file->getClientOriginalExtension();
//        $fileName = Str::random(32) . '.' . $fileExtension;
//        $file->storeAs('service_icons/', $fileName, 's3');
//
//        $fileLocation = 'https://' . env('AWS_BUCKET') . '.' . 's3' . '.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/service_icons/' . $fileName;
//
//        // Storing Service Data To Database
//        $service = new Service();
//        $service->name = $request->name;
//        $service->icon = $fileLocation;
//        $service->slug = $request->slug;
//        $service->save();
//
//        return response()->json($service);
//    }
}
