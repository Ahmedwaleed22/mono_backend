<?php

namespace App\Http\Controllers\v1\requests;

use App\Http\Controllers\v1\Controller;
use App\Models\ChatRoom;
use App\Models\Milestone;
use App\Models\ServiceRequest;
use App\Models\SubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use function response;

class RequestsController extends Controller
{
    public function requestService(Request $request, $serviceSlug): JsonResponse
    {
        $request->validate([
            'funds' => 'required|numeric',
            'milestone_name' => 'string|max:255'
        ]);

        $user = $request->user();
        $service = SubService::where('slug', $serviceSlug)->firstOrFail();

        if (!($request->funds >= $service->min_price)) {
            return response()->json([
                'error' => trans('requests.not_enough_funds')
            ], 402);
        }

        if (!($user->funds >= $service->min_price)) {
            return response()->json([
                'error' => trans('requests.add_funds')
            ], 402);
        }

        $serviceRequest = new ServiceRequest;
        $serviceRequest->client_id = $user->id;
        $serviceRequest->worker_id = $service->user_id;
        $serviceRequest->sub_service_id = $service->id;
        $serviceRequest->save();

        $milestone = new Milestone;
        $milestone->name = $request->milestone_name ?? "Initial Milestone";
        $milestone->funds = $request->funds;
        $milestone->service_request_id = $serviceRequest->id;
        $milestone->save();

        $user->funds -= $request->funds;
        $user->save();

        $chatRoom = new ChatRoom;
        $chatRoom->name = Str::random(32);
        $chatRoom->service_request_id = $serviceRequest->id;
        $chatRoom->save();

        $responseData = [
          'service' => [
              'id' => $serviceRequest->id,
          ],
          'chat' => [
              'name' => $chatRoom->name,
              'service_request_id' => $chatRoom->service_request_id
          ],
          'payment' => [
              'service_min_price' => $service->min_price,
              'service_max_price' => $service->max_price,
              'amount' => $request->funds,
              'currency' => 'EGP'
          ]
        ];

        return response()->json($responseData);
    }

    public function serviceMilestones(Request $request, $serviceRequestID): JsonResponse
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::where('id', $serviceRequestID)->where('client_id', $user->id)->orWhere('worker_id', $user->id)->firstOrFail();

        return response()->json($serviceRequest->milestones);
    }

    public function createMilestone(Request $request, $serviceRequestID): JsonResponse
    {
        $request->validate([
            'funds' => 'required|numeric',
            'milestone_name' => 'required|string|max:255'
        ]);

        $user = $request->user();
        $serviceRequest = ServiceRequest::where('id', $serviceRequestID)->where('client_id', $user->id)->firstOrFail();

        $milestone = new Milestone;
        $milestone->name = $request->milestone_name;
        $milestone->funds = $request->funds;
        $milestone->service_request_id = $serviceRequest->id;
        $milestone->save();

        $user->funds -= $request->funds;
        $user->save();

        return response()->json($milestone);
    }

    public function releaseMilestone(Request $request, $milestoneID): JsonResponse
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::with(['milestones' => function ($query) use ($milestoneID) {
            $query->where('id', $milestoneID)->firstOrFail();
        }])->where('client_id', $user->id)->firstOrFail();

        $milestone = $serviceRequest->milestones[0];

        if ($milestone->status != 'released' && $milestone->status != 'refunded') {
            $milestone->status = 'released';
            $milestone->save();

            $serviceRequest->worker->funds += $serviceRequest->milestones[0]->funds - ($serviceRequest->milestones[0]->funds * 0.10);
            $serviceRequest->worker->save();

            return response()->json($milestone);
        } else {
            return response()->json([
                'error' => 'Milestone status is ' . $milestone->status,
            ], 409);
        }
    }
}
