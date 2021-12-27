<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Login') }}</title>

    @section('head')
    @show

    <script type="text/javascript" nonce="{{ $nonce }}">this.name = "idaas";window.information = @json(@$information);</script>

</head>
<body>
    
    <div id="app"></div>
    <!-- built files will be auto injected -->

</body>
</html>
