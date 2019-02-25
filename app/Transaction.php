<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    protected $fillable = ['invoice_id', 'billing_agreement_id', 'user_id', 'product_id'];

}
