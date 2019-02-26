<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model {

    protected $fillable = ['user_id', 'name', 'price', 'billing_agreement_id'];

    public function createBillingAgreement() {
        $billingAgreement = new BillingAgreement($this->billing_agreement_id, $this);
        $billingAgreement->create(1, $this->price);
    }

    public function deleteBillingAgreement() {
        $billingAgreement = new BillingAgreement($this->billing_agreement_id, $this);
        $billingAgreement->delete();
    }

    public function hasBillingAgreement() {
        return $this->billing_agreement_id != null;
    }


}
