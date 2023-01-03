<?php

namespace mykholy\PayMob;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use mysql_xdevapi\Exception;

class PayMob
{
    protected $auth_token;
    protected $payout;
    protected $payoutAuthToken;

    public function __construct()
    {
        $this->auth_token = $this->getAuthToken();
        $this->payout = config('paymob.payout');
    }

    /* -------------------------------------------------------------------------- */
    /*                                   HELPERS                                  */
    /* -------------------------------------------------------------------------- */

    protected function getConfigKey($key)
    {
        return Arr::get(
            config('paymob.accept'),
            str_replace('..', '.', $key)
        );
    }

    protected function getAmountInCents($amount)
    {
        return $amount * $this->getConfigKey('conversion_rate');
    }

    protected function getCurrency()
    {
        return $this->getConfigKey('currency');
    }

    /* -------------------------------------------------------------------------- */
    /*                                    AUTH                                    */
    /* -------------------------------------------------------------------------- */

    /**
     * 1. https://acceptdocs.paymobsolutions.com/docs/accept-standard-redirect#1-authentication-request.
     */
    protected function getAuthToken()
    {
        $response = Http::post(
            $this->getConfigKey('url.token'),
            ['api_key' => $this->getConfigKey('api_key')]
        )->throw();

        return $response['token'];
    }

    /* -------------------------------------------------------------------------- */
    /*                                    MISC                                    */
    /* -------------------------------------------------------------------------- */

    /**
     * validate hmac for data integrity check.
     *
     * https://acceptdocs.paymobsolutions.com/docs/hmac-calculation.
     *
     * @param string $hmac
     * @param int $trans_id
     *
     * @return bool
     */
    public function validateHmac($hmac, $trans_id)
    {
        $url = $this->getConfigKey('url.hmac');

        $response = Http::withToken($this->auth_token)
            ->get("$url/$trans_id/hmac_calc")
            ->throw();

        return $response['hmac'] == $hmac ?: abort(400, __('paymob::messages.incorrect_hmac'));
    }

    public function retrieveTransaction($trans_id)
    {
        $url = $this->getConfigKey('url.transaction');

        $response = Http::withToken($this->auth_token)
            ->get("$url/$trans_id")
            ->throw();

        return $response->json();
    }

    /**
     * check for minimum amount limits.
     *
     * @param [type] $total
     */
    public function checkForMinAmount($total)
    {
        $min = $this->getConfigKey('min_amount');
        $curr = $this->getCurrency();

        if ($total < $min) {
            abort(422, __('paymob::messages.min_amount', ['attr' => "$min $curr"]));
        }

        return true;
    }

    /**
     * https://acceptdocs.paymobsolutions.com/docs/refund-transaction.
     *
     * @param int $trans_id
     * @param float|int $amount
     */
    public function refund($trans_id, $amount)
    {
        $response = Http::withToken($this->auth_token)
            ->post($this->getConfigKey('url.refund'), [
                'transaction_id' => $trans_id,
                'amount_cents' => $this->getAmountInCents($amount),
            ])
            ->throw();

        return $response;
    }

    public function payoutAuth()
    {
        $body = [
            'client_id' => $this->payout['client_id'],
            'client_secret' => $this->payout['client_secret'],
            'username' => $this->payout['username'],
            'password' => $this->payout['password'],
            'grant_type' => 'password',
        ];

        $url = $this->payout['auth'];

        $response = Http::asForm()->post($url, $body)->throw();
        if ($response->successful()) {
            $response = $response->json();
            $payoutAuthToken = $response['access_token'];
            $this->payoutAuthToken = $payoutAuthToken;
            return $payoutAuthToken;
        }
    }

    public function budget()
    {
        try {
            $auth = $this->payoutAuth();
            $url = $this->payout['budget'];

            $response = Http::withToken($auth)
                ->get($url);
            $json['success'] = true;
            $json['message'] = '';
            $json['data'] = $response->json();
            return $json;
        } catch (\Exception $exception) {
//            dd($exception);
            $json['success'] = false;
            $json['message'] = trans('lang.paymob_error');
            return $json;

        }

    }

    public function payout($issuer, $amount, $attributes)
    {
        $auth = $this->payoutAuth();
        $url = $this->payout['payout'];
        $body = $attributes + ['amount' => $amount, 'issuer' => $issuer];

        $response = Http::asJson()->withToken($auth)
            ->post($url, $body);
       

        return $response->json();

    }

    public function getPayoutFee($amount, $type)
    {
        $commissionPercentage = config('paymob.payout_fee_percentage.' . $type);
        $fee = $amount * 100 / (100 - $commissionPercentage) - $amount;

        if ($type == 'bank_card') {
            if ($fee < config('paymob.payout_fee_boundaries_for_bank_card.min')) {
                $fee = config('paymob.payout_fee_boundaries_for_bank_card.min');
            } elseif ($fee > config('paymob.payout_fee_boundaries_for_bank_card.max')) {
                $fee = config('paymob.payout_fee_boundaries_for_bank_card.max');
            }
        }

        return $fee;
    }

    public function getPayinFee($transactionCount)
    {
        return $transactionCount * config('paymob.accept_payin_fixed_fee');
    }
}
