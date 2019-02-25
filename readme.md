**Install via composer**
```
composer require paypal/rest-api-sdk-php:*\
```
**Run**
```
php artisan migrate:refresh
```

Creating your "Connect with PayPal" button
-
Step 1. Create an application [here](https://developer.paypal.com/developer/applications/create):\
Step 2. Make note of your Client ID, you'll need this.\
Step 3. Return URL: I just use the /home for this tutorial\
Step 4. Scroll down to Sandbox App Settings and enable "Connect with PayPal"\
Step 5. Click the Advanced Options that should show up after you enable CWP then click the scope you'll want to use.\
_Note*** you will need a valid TOS page and Privacy Page (You can just copy these from some website)_\
Step 6. Make your button [here](https://developer.paypal.com/docs/integration/direct/identity/button-js-builder) using those settings (Client ID, Scope, etc.):\
Step 7. Past the script code wherever you want the user to connect their account. 
