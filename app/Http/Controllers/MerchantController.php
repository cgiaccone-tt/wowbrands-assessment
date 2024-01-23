<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        $jsonResponse = new JsonResponse();

        try {
            $from = Carbon::parse($request->input('from'));
            $to = Carbon::parse($request->input('to'));

            $merchant = Merchant::where('user_id', $request->user()->id)->first();

            $orders = $merchant->orders()
                ->whereBetween('created_at', [$from, $to])
                ->get();

            $count = $orders->count();
            $revenue = round($orders->sum('subtotal'), 2);
            $commissionsOwed = round($orders->sum('commission_owed'), 2);

            $jsonResponse->setData([
                'count' => $count,
                'commissions_owed' => $commissionsOwed,
                'revenue' => $revenue
            ]);


            return $jsonResponse;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
