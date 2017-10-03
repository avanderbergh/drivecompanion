<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta id="token" name="token" value="{{ csrf_token() }}">
    <title>Drive Companion</title>
    <link rel="stylesheet" href="{{ elixir('css/app.css') }}">
    {{-- Google Analytics Code --}}
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', '{{ env('GOOGLE_ANALYTICS_TRACKING_ID') }}', 'auto');
        ga('set', 'userId', '{{ Auth::user()->id }}');
        ga('send', 'pageview');

    </script>
</head>
<body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    @yield('content')
</body>
</html>