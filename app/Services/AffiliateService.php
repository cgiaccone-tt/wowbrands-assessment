<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param Merchant $merchant
     * @param string $email
     * @param string $name
     * @param float $commissionRate
     * @return Affiliate
     * @throws Throwable
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        $discountCode = $this->apiService->createDiscountCode();

        $user = new User();
        $user = $user->where('email', $email)->first();
        //$user->type = User::TYPE_AFFILIATE;
        $user->save();

        $affiliate = new Affiliate();

        $affiliate->create([
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'commission_rate' => $commissionRate,
            'discount_code' => $discountCode['code']]);



        $affiliate->user()->associate($user);
        $affiliate->merchant()->associate($merchant);

        $affiliate->save();

        Mail::to($email)->send(new AffiliateCreated($affiliate));

        return $affiliate;
    }
}
