<?php
/**
 * Created by PhpStorm.
 * User: colbymchenry
 * Date: 2019-02-24
 * Time: 21:08
 */

namespace App\Http\Controllers;


use App\BillingAgreement;
use App\Checkout;
use App\Product;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ProductController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function createProduct() {
        $name = \request('name');
        $price = \request('price');
        $product = new Product();

        $errors = array();

        // Check to see if a product by the given name already exists.
        if (Product::where('name', $name)->exists())
            $errors['name'] = 'A product with that name already exists.';

        // Check to see if the price is valid
        $pattern = '/^(0|[1-9]\d*)(\.\d{2})?$/';
        if (preg_match($pattern, $price) == '0')
            $errors['price'] = 'Invalid price.';

        // If there are errors return home with them and do not create the product
        if (!empty($errors))
            return redirect('/home')->withErrors($errors)->withInput();

        $product->name = $name;
        $product->price = $price;
        $product->user_id = Auth::user()->id;
        $product->save();

        Session::put('status', 'Product created.');
        return redirect('/home');
    }

    public function createBillingAgreement($product_id) {
        if(!Product::where('id', $product_id)->exists()) {
            Session::put('status', "Product does not exist.");
            return redirect('/home');
        }

        try {
            $product = Product::where('id', $product_id)->get()[0];
            $product->createBillingAgreement(1, $product->price);
            Session::put('status', 'Billing agreement created.');
        } catch(Exception $e) {
            Log::error($e);
            Session::put('status', 'An error occured: ' . $e->getMessage());
        }

        return redirect('/product/view/' . $product_id);
    }

    public function createBillingAgreementCheckout($product_id) {
        try {
            if(!Product::where('id', $product_id)->exists()) throw new Exception('Product doesn\'t exist.');
            $product = Product::where('id', $product_id)->get()[0];
            if($product->billing_agreement_id == null) throw new Exception('No billing agreement for this product.');
            return redirect(BillingAgreement::createCheckout($product->billing_agreement_id));
        } catch(Exception $e) {
            Session::put('status', $e->getMessage());
            Log::error($e);
            Log::error($e->getData());
            return redirect('/home');
        }
    }

    public function executeBillingAgreementCheckout($success) {
        return BillingAgreement::executeCheckout($success);
    }

    public function deleteBillingAgreement($product_id) {
        if(!Product::where('id', $product_id)->exists()) {
            Session::put('status', "Product does not exist.");
            return redirect('/home');
        }

        try {
            $product = Product::where('id', $product_id)->get()[0];
            $product->deleteBillingAgreement();
            Session::put('status', 'Billing agreement deleted.');
        } catch(Exception $e) {
            Log::error($e);
            Session::put('status', 'An error occured: ' . $e->getMessage());
        }

        return redirect('/product/view/' . $product_id);
    }

    public function createCheckout($product_id) {
        if(!Product::where('id', $product_id)->exists()) {
            Session::put('status', "Product does not exist.");
            return redirect('/home');
        }

        try {
            $product = Product::where('id', $product_id)->get()[0];
            $checkout = new Checkout($product);
            $checkout->createCheckout();
            return $checkout->payment;
        } catch(Exception $e) {
            Log::error($e);
            Session::put('status', 'An error occured: ' . $e->getMessage());
        }

        return null;
    }

    public function executeCheckout(Request $request) {
        return Checkout::executeCheckout($request);
    }

    public function index($product_id) {
        if(!Product::where('id', $product_id)->exists()) {
            Session::put('status', "Product does not exist.");
            return redirect('/home');
        }
        return view('product')->with('product', Product::where('id', $product_id)->get()[0]);
    }

}