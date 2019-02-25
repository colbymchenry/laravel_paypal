<?php

namespace App;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PayPal\Api\OpenIdTokeninfo;

class PayPalToken extends Model
{

    protected $fillable = ['user_id', 'refresh_token', 'access_token'];

    public static function genAndStoreTokens($code) {
        try {
            $apiContext = PayPalUtil::ApiContext();
            $refreshToken = OpenIdTokeninfo::createFromAuthorizationCode(array('code' => $code), null, null, $apiContext);
            $tokenInfo = new OpenIdTokeninfo();
            $tokenInfo = $tokenInfo->createFromRefreshToken(array('refresh_token' => $refreshToken->toArray()['refresh_token']), $apiContext);

            $paypalToken = new PayPalToken();

            if(PayPalToken::where('user_id', Auth::user()->id)->exists()) {
                $paypalToken = PayPalToken::where('user_id', Auth::user()->id)->get()[0];
            }

            $paypalToken->user_id = Auth::user()->id;
            $paypalToken->refresh_token = $refreshToken->toArray()['refresh_token'];
            $paypalToken->access_token = $tokenInfo->getAccessToken();
            $paypalToken->save();
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

    public static function hasTokens($user_id) {
        return PayPalToken::where('user_id', $user_id)->exists();
    }
}
