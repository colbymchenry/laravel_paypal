<?php
/**
 * Created by PhpStorm.
 * User: colbymchenry
 * Date: 2019-02-25
 * Time: 13:16
 */

namespace App;


use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use PayPal\Api\Agreement;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Payer;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Common\PayPalModel;

class BillingAgreement {

    var $product;
    var $agreement_id;

    public function __construct($agreement_id, $product) {
        $this->agreement_id = $agreement_id;
        $this->product = $product;
    }

    public function create($months, $price) {
        $apiContext = PayPalUtil::ApiContext();
        $plan = new Plan();
        // The plan includes a description and name, this can be customized to fit specific needs
        $plan->setName($this->product->name . ' ' . $months . ' month\'s of access.')
            ->setDescription('No description provided.')
            ->setType('fixed');

        // The Payment Definitions include the intervals, the frequency, and the price at which the client will be charged
        $paymentDefinition = new PaymentDefinition();
        $paymentDefinition->setName('Regular Payments')
            ->setType('REGULAR')
            ->setFrequency('Month')
            ->setFrequencyInterval($months)
            ->setCycles("12")
            ->setAmount(new Currency(array('value' => $price, 'currency' => 'USD')));

        $merchantPreferences = new MerchantPreferences();

        // If you ever change the return url must update all payment plans
        // Merchant Preferences includes everything at the initial checkout for the end user
        $merchantPreferences->setReturnUrl(route(PayPalUtil::BillingAgreementExecuteURL(), 'true'))
            ->setCancelUrl(route(PayPalUtil::BillingAgreementExecuteURL(), 'false'))
            ->setAutoBillAmount("yes")
            ->setInitialFailAmountAction("CONTINUE")
            ->setMaxFailAttempts("0")
            ->setSetupFee(new Currency(array('value' => $price, 'currency' => 'USD')));

        // Update the plan with the new payment definitions and merchant preferences
        $plan->setPaymentDefinitions(array($paymentDefinition));
        $plan->setMerchantPreferences($merchantPreferences);
        $plan->create($apiContext);

        // Activate the plan
        $patch = new Patch();
        $value = new PayPalModel('{
	       "state":"ACTIVE"
	     }');
        $patch->setOp('replace')
            ->setPath('/')
            ->setValue($value);
        $patchRequest = new PatchRequest();
        $patchRequest->addPatch($patch);
        $plan->update($patchRequest, $apiContext);
        // Update the product to include the billing agreement ID
        $this->product->billing_agreement_id = $plan->getId();
        $this->product->save();
    }

    public static function createCheckout($plan_id) {
        $apiContext = PayPalUtil::ApiContext();

        // add 1 minute to the current time for the plan to start
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $date->add(new DateInterval('PT' . 1 . 'M'));
        // get the product tied to the plan_id
        $product = Product::where('billing_agreement_id', $plan_id)->get()[0];
        // store the product being purchased to the users session for the execution process
        Session::put('current-product', $product->toArray());
        // get the plan from PayPal tied to that ID
        $plan = Plan::get($product->billing_agreement_id, $apiContext);
        $price = $plan->toArray()['payment_definitions'][0]['amount']['value'];
        // setup the payer
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        // setup the agreement to show to the user at checkout
        $agreement = new Agreement();
        $agreement->setName($product->name . ' private access at ' . $plan->months . ' month intervals')
            ->setDescription('Subscribe to ' . $product->name . '\'s ' . 'private access. You will be billed $' . $price . ' every ' . $plan->months . ' months.')
            ->setStartDate($date->format(DATE_ISO8601));

        $newPlan = new Plan();
        $newPlan->setId($plan_id);

        $agreement->setPlan($newPlan);
        $agreement->setPayer($payer);

        $agreement->create($apiContext);

       return $agreement->getApprovalLink();
    }

    public static function executeCheckout($success) {
        $apiContext = PayPalUtil::ApiContext();
        $access_token = \request('token');

        if ($success == 'true') {
            $agreement = new Agreement();
            try {
                $agreement->execute($access_token, $apiContext);
                $transaction = new Transaction();
                $transaction->billing_agreement_id = $agreement->getId();
                $transaction->product_id = Session::get('current-product')['id'];
                $transaction->user_id = Auth::user()->id;
                $transaction->save();
            } catch (Exception $ex) {
                Session::put('status', 'Error occurred, agreement ID: ' . $agreement->getId());
                Log::error($ex);
                Session::remove('current-product');
                return redirect('/home');
            }

            Session::put('status', 'Success! You are now subscribed to ' . Session::get('current-product')['name']);
            Session::remove('current-product');
            return redirect('/home');
        } else {
            Session::put('status', 'User cancelled the approval.');
            Session::remove('current-product');
            return redirect('/home');
        }
    }

    public function delete() {
        $apiContext = PayPalUtil::ApiContext();
        $plan = Plan::get($this->product->billing_agreement_id, $apiContext);
        $plan->delete($apiContext);
        $this->product->billing_agreement_id = null;
        $this->product->save();
    }

}