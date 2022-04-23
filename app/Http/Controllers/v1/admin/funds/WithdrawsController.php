<?php

namespace App\Http\Controllers\v1\admin\funds;

use App\Http\Controllers\v1\admin\Controller;
use App\Models\Withdraw;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WithdrawsController extends Controller
{
    public function index(): JsonResponse
    {
        $withdraws = Withdraw::with('user')->get();
        return response()->json($withdraws);
    }

    public function show($id): JsonResponse
    {
        $withdraw = Withdraw::with('user')->where('id', $id)->firstOrFail();
        return response()->json($withdraw);
    }

    public function setCompleted($id): JsonResponse
    {
        $withdraw = Withdraw::where('id', $id)->firstOrFail();

        if ($withdraw->status == 'pending') {
            $withdraw->status = 'completed';
            $withdraw->save();

            return response()->json($withdraw);
        }

        return response()->json([
            'error' => 'Withdraw request is not in a pending status.'
        ], 409);
    }

    public function setRefused($id): JsonResponse
    {
        $withdraw = Withdraw::with('user')->where('id', $id)->firstOrFail();

        if ($withdraw->status == 'pending') {
            $withdraw->status = 'refused';
            $withdraw->user->funds += $withdraw->amount;
            $withdraw->save();
            $withdraw->user->save();

            return response()->json($withdraw);
        }

        return response()->json([
            'error' => 'Withdraw request is not in a pending status.'
        ], 409);
    }
}
