<?php

namespace App\Http\Controllers\v1\admin\requests;

use App\Http\Controllers\v1\admin\Controller;
use App\Models\Milestone;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestsController extends Controller
{
    public function index(): JsonResponse
    {
        $requests = ServiceRequest::with(['client', 'worker', 'subService'])->get();
        return response()->json($requests);
    }

    public function show($id): JsonResponse
    {
        $requests = ServiceRequest::with(['client', 'worker', 'subService', 'milestones'])->where('id', $id)->firstOrFail();
        return response()->json($requests);
    }

    public function releaseMilestone($id): JsonResponse
    {
        $milestone = Milestone::with(['request', 'request.worker'])->where('id', $id)->firstOrFail();

        if ($milestone->status != 'released' && $milestone->status != 'refunded') {
            $milestone->request->worker->funds += $milestone->funds;
            $milestone->status = 'released';
            $milestone->request->worker->save();
            $milestone->save();

            return response()->json($milestone);
        }

        return response()->json([
            'error' => 'Milestone status is ' . $milestone->status,
        ], 409);
    }

    public function refundMilestone($id): JsonResponse
    {
        $milestone = Milestone::with(['request', 'request.client'])->where('id', $id)->firstOrFail();

        if ($milestone->status != 'released' && $milestone->status != 'refunded') {
            $milestone->request->client->funds += $milestone->funds;
            $milestone->status = 'refunded';
            $milestone->request->client->save();
            $milestone->save();

            return response()->json($milestone);
        }

        return response()->json([
            'error' => 'Milestone status is ' . $milestone->status,
        ], 409);
    }
}
