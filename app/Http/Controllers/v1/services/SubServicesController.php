<?php

namespace App\Http\Controllers\v1\services;

use App\Http\Controllers\v1\Controller;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\SubService;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use function env;
use function response;

class SubServicesController extends Controller
{
    public function all(): Collection
    {
        return SubService::with('service')->get();
    }

    public function createSubService(Request $request): JsonResponse
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

        if (!($user->account_type == 'worker')) {
            return response()->json([
                'error' => 'Your account is not a worker account.'
            ], 409);
        }

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

    public function getByService($service): Builder|Model
    {
        return Service::with('subServices')->where('slug', $service)->firstOrFail();
    }

    public function getSubService($subService): Model|Builder
    {
        return SubService::with('service')->where('slug', $subService)->firstOrFail();
    }

    public function userInterests(Request $request): JsonResponse
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::with(['subService.service.subServices' => function($query) {
            $query->limit(10)->get();
        }])->where('client_id', $user->id)->orderBy('created_at', 'DESC')->firstOrFail();
        $subServices = $serviceRequest
                        ->getRelation('subService')
                        ->getRelation('service')
                        ->getRelation('subServices');
        return response()->json($subServices);
    }

    public function myServices(Request $request) {
        $user = $request->user();
        $subService = SubService::where('user_id', $user->id)->get();
        return response()->json($subService);
    }
}
