<?php
/**
 * Created by PhpStorm.
 * User: colbymchenry
 * Date: 2019-02-24
 * Time: 21:08
 */

namespace App\Http\Controllers;


use App\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ProductController extends Controller {

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function createProduct() {
        $name = \request('name');
        $price = \request('price');
        $product = new Product();

        $errors = array();

        // Check to see if a product by the given name already exists.
        if(Product::where('name', $name)->exists())
            $errors['name'] = 'A product with that name already exists.';

        // Check to see if the price is valid
        $pattern = '/^(0|[1-9]\d*)(\.\d{2})?$/';
        if(preg_match($pattern, $price) == '0')
            $errors['price'] = 'Invalid price.';

        // If there are errors return home with them and do not create the product
        if(!empty($errors))
            return redirect('/home')->withErrors($errors)->withInput();

        $product->name = $name;
        $product->price = $price;
        $product->user_id = Auth::user()->id;
        $product->save();

        Session::put('status', 'Product created.');
        return redirect('/home');
    }

    public function index($product_id) {
        return view('product')->with('product_id', $product_id);
    }

}