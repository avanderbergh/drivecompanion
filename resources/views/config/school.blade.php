@extends("app")

@section("content")
    <!-- Vue Application -->
    <div class="container-fluid" id="config_school">
        <nav class="navbar navbar-default navbar-main navbar-static-top">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand">&nbsp;<i class="icon icon-logo fa-lg"></i>&nbsp;Drive Companion</a>
                </div>
                <div class="collapse navbar-collapse">
                    @include('config.partials.buy_credits_button')
                </div>
            </div>
        </nav>
        <div class="row">
            @include('config.partials.school_code')
            @include('config.forms.buy_credits')
            @include('config.forms.enter_billing_details')
            @include('config.partials.billing_details')
            @include('config.partials.google_instructions')
        </div>
        <div class="row">
            @include('config.partials.sections')
        </div>
        @if(env('APP_DEBUG')=='true')
            <pre>@{{ $data | json }}</pre>
            <pre>{{ var_dump($user) }}</pre>
            <pre>{{ var_dump($school) }}</pre>
        @endif
    </div>
    @include(('partials.footer'))

    <!-- Application Variables-->
    @include('partials.phpvars')
    <!-- Application JavaScript -->
    <script src="{{ elixir('js/config.js') }}"></script>
@stop
