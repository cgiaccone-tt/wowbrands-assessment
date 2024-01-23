<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     * @throws \Exception
     */
    public function register(array $data): Merchant
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'type' => User::TYPE_MERCHANT,
            'password' => $data['api_key']
        ]);

        $user->save();

        $merchant = Merchant::create([
            'user_id' => $user->id,
            'domain' => $data['domain'],
            'display_name' => $data['name'],
            'turn_customers_into_affiliates' => false,
            'default_commission_rate' => 0.0
        ]);

        $merchant->save();

        return $merchant;
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     * @throws \Exception
     */
    public function updateMerchant(User $user, array $data): void
    {
        try {
            $user->update([
                'email' => $data['email'],
                'password' => $data['api_key'],
                'type' => User::TYPE_MERCHANT
            ]);

            $merchant = $user->merchant;
            $merchant->update([
                'domain' => $data['domain'],
                'display_name' => $data['name'],
                'turn_customers_into_affiliates' => false,
                'default_commission_rate' => 0.0
            ]);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
        }
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                return null;
            }
            return $user->merchant;
        } catch (\Exception $e) {
            Log::info($e->getMessage());
        }
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     * @throws \Exception
     */
    public function payout(Affiliate $affiliate): void
    {
        try {
            $orders = Order::where('affiliate_id', $affiliate->id)->where('payout_status', Order::STATUS_UNPAID)->get();

            foreach ($orders as $order) {
                dispatch(new PayoutOrderJob($order));
            }
        } catch (\Exception $e) {
            throw new \Exception('Unable to payout');
        }
    }
}
