<?php

namespace App;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\InputFields;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\WebProfile;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class Checkout {

    var $apiContext;
    var $product;
    var $payment;

    public function __construct($product) {
        $this->apiContext = PayPalUtil::ApiContext();
        // initialize product variable
        $this->product = $product;
        // initialize the payer
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        // create the item
        $item = new Item();
        $item->setName($this->product->name)
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setSku($this->product->sku)
            ->setPrice($this->product->price);
        // add the item to an ItemList
        $itemList = new ItemList();
        $itemList->setItems(array($item));
        // setup the details and tax
        $details = new Details();
        $tax = $this->product->price * 0.15;
        $details->setTax($tax)->setSubtotal($this->product->price);
        // create the overall amount to be paid
        $amount = new Amount();
        $amount->setCurrency("USD")->setTotal($this->product->price + $tax)->setDetails($details);
        // create the transaction with its appropriate details
        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription("Payment description")
            ->setInvoiceNumber(uniqid());
        // setup the return urls for after they've paid
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(route(PayPalUtil::CheckoutExecuteURL()) . '?success=true')->setCancelUrl(route(PayPalUtil::CheckoutExecuteURL()) . '?success=false');
        // create the payment
        $this->payment = new Payment();
        // remove shipping options from the checkout
        $inputFields = new InputFields();
        $inputFields->setNoShipping(1);
        $webProfile = new WebProfile();
        $webProfile->setName('test' . uniqid())->setInputFields($inputFields);
        $webProfileId = $webProfile->create($this->apiContext)->getId();
        $this->payment->setExperienceProfileId($webProfileId);
        // setup the last details for the payment
        $this->payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));
    }

    public function createCheckout() {
        $this->payment->create($this->apiContext);
    }

    public static function executeCheckout(Request $request) {
        $apiContext = PayPalUtil::ApiContext();
        $paymentId = $request->payment_id;
        $payment = Payment::get($paymentId, $apiContext);

        $execution = new PaymentExecution();
        $execution->setPayerId($request->payer_id);

        try {
            $result = $payment->execute($execution, $apiContext);
            $payment = Payment::get($paymentId, $apiContext);
            $transaction = new \App\Transaction();
            $transaction->invoice_id = $result->getTransactions()[0]->invoice_number;
            $transaction->user_id = Auth::user()->id;
            $transaction->product_id = $request->product_id;
            $transaction->save();
        } catch (Exception $ex) {
            Log::error($ex);
        }

        return $payment;
    }
}