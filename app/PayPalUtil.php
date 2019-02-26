<?php
/**
 * Created by PhpStorm.
 * User: colbymchenry
 * Date: 2019-02-24
 * Time: 21:56
 */

namespace App;


use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class PayPalUtil {

    public static function ApiContext() {
        $apiContext = new ApiContext(new OAuthTokenCredential('AQm2RTbgardc_osr1S9rydXNwmg7TPcNRbP9s1_jVeS62BVLYFdKNCAu37dbQfG6N17GVA-SWAuhBwg7', 'ECye0788S1sl7ryCjMZYy-YrkVhC9H9i07i2ZBqWc7RELLmRbxWVzjhkNhagkHAh9gx0pRZ-J_GI7O3Q'));
        return $apiContext;
    }

    public static function BillingAgreementExecuteURL() {
        return 'execute-agreement';
    }

    public static function CheckoutExecuteURL() {
        return 'execute-checkout';
    }

}