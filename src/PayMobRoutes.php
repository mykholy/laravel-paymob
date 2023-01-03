<?php

namespace mykholy\PayMob;

use Illuminate\Support\Facades\Route;

class PayMobRoutes
{
    public static function routes()
    {
        $controller = config('paymob.controller', '\mykholy\PayMob\Controllers\DummyController');

        Route::get('checkout', [
            'as'   => 'checkout',
            'uses' => "$controller@checkOut",
        ]);

        Route::post('/', [
            'as'   => 'process',
            'uses' => "$controller@process",
        ]);

        Route::get('complete', [
            'as'   => 'complete',
            'uses' => "$controller@complete",
        ]);
    }
}
