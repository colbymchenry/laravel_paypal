**Install via composer**\
composer require paypal/rest-api-sdk-php:*\
**Make an authentication framework**\
php artisan make:auth\
**Create transaction migration**\
php artisan make:model Transaction -m\
**Set up the migration to look like this**\
    `Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('invoice_id');
            $table->string('billing_agreement_id')->nullable();
            $table->integer('user_id');
            $table->integer('product_id')->nullable();
            $table->timestamps();
        });`


