<?php

namespace App\Http\Controllers;

use App\PayPalToken;
use App\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // request will have a code parameter when doing the PayPal connect
        if(\request('code') !== null) {
            PayPalToken::genAndStoreTokens(\request('code'));
            Session::put('status', 'PayPal account connected.');
            return redirect('home');
        }
        return view('home')->with('products', Product::where('user_id', Auth::user()->id)->get());
    }
}
