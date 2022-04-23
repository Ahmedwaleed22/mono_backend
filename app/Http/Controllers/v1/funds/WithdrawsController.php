<?php

namespace App\Http\Controllers\v1\funds;

use App\Http\Controllers\v1\Controller;
use App\Models\Withdraw;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function response;

class WithdrawsController extends Controller
{
    public function withdraws(Request $request): JsonResponse
    {
        $user = $request->user();
        $withdraws = Withdraw::where('user_id', $user->id)->get();
        return response()->json($withdraws);
    }

    public function withdraw(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:200',
            'account_number' => 'required|string',
            'full_name' => 'required|string'
        ]);

        $user = $request->user();
        $funds = $user->funds;

        if ($funds < $request->amount) {
            return response()->json([
                'error' => 'Your account funds are less than the amount requested for withdrawal.'
            ], 402);
        }

        $withdraw = new Withdraw;
        $withdraw->amount = $request->amount;
        $withdraw->account_number = $request->account_number;
        $withdraw->full_name = $request->full_name;
        $withdraw->status = 'pending';
        $withdraw->user_id = $user->id;
        $withdraw->save();

        $user->funds -= $request->amount;
        $user->save();

        return response()->json($withdraw);
    }
}
