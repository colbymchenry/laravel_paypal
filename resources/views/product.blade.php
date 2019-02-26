@extends('layouts.app')

@section('head')
    <meta name="product_id" content="{{ $product->id }}">
@endsection

@section('content')
    <div class="container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">Dashboard</div>

                        <div class="card-body">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <form action="/paypal/create-agreement/1" method="POST">
                                @csrf
                                <button action="submit" class="btn btn-warning">SUBSCRIBE</button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div id="checkout-button"></div>
                        </div>
                        <div class="card-body">
                            <form action="/product/plan/create/{{ $product->id }}" method="POST">
                                @csrf
                                <button type="submit" onclick="createPlan()" class="btn btn-block btn-success">
                                    <i class="fa fa-fw fa-sign-in-alt mr-1"></i> Create Billing Agreement
                                </button>
                            </form>
                        </div>
                        <div class="card-body">
                            <form action="/product/plan/delete/{{ $product->id }}" method="POST">
                                @csrf
                                <button type="submit" onclick="deletePlan()" class="btn btn-block btn-danger">
                                    <i class="fa fa-fw fa-sign-in-alt mr-1"></i> Delete Billing Agreement
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function loadScript( url, callback ) {
            var script = document.createElement( "script" )
            script.type = "text/javascript";
            if(script.readyState) {  // only required for IE <9
                script.onreadystatechange = function() {
                    if ( script.readyState === "loaded" || script.readyState === "complete" ) {
                        script.onreadystatechange = null;
                        callback();
                    }
                };
            } else {  //Others
                script.onload = function() {
                    callback();
                };
            }

            script.src = url;
            document.getElementsByTagName( "head" )[0].appendChild( script );
        }


        // call the function...
        loadScript('https://www.paypalobjects.com/api/checkout.js', function() {
            alert('script ready!');
            paypal.Button.render({
                env: 'sandbox', // Or 'production
                style: {
                    size: 'large',
                    color: 'gold',
                    shape: 'pill',
                    label: 'checkout',
                    tagline: 'true'
                },
                // Set up the payment:
                // 1. Add a payment callback
                payment: function (data, actions) {
                    // 2. Make a request to your server
                    return actions.request.post('/paypal/create-checkout/' + $('meta[name="product_id"]').attr('content'), {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    })
                        .then(function (res) {
                            // 3. Return res.id from the response
                            return res.id;
                        });
                },
                // Execute the payment:
                // 1. Add an onAuthorize callback
                onAuthorize: function (data, actions) {
                    // 2. Make a request to your server
                    return actions.request.post('/paypal/execute-checkout', {
                        payment_id: data.paymentID,
                        payer_id: data.payerID,
                        product_id: $('meta[name="product_id"]').attr('content'),
                        _token: $('meta[name="csrf-token"]').attr('content')
                    })
                        .then(function (res) {
                            // 3. Show the buyer a confirmation message.
                            console.log('WE MADE IT HERE');
                        });
                },
                onError: function (err) {
                    // window.location.replace('/home');
                    console.log(err);
                }
            }, '#checkout-button');
        });
    </script>

    <script>
        function createPlan() {

        }

        function deletePlan() {

        }
    </script>
@endsection
