<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />

    <script type="text/javascript">
window.manageClient = {
  clientId: '{{ resolve('App\Tenant')->client_id }}', //login
  authorize: '{{ route('oauth.authorize') }}',
  redirectUri: '{{ route('ice.manage.completelogin') }}',
  post_logout_redirect_uri: '{{ route('ice.manage.completelogout') }}',
  end_session_endpoint: '{{ route('oidc.logout') }}',
};
window.manageUrls = {
  manage: '{{ route('ice.manage.home') }}',
  oidc: '{{ route('ice.login.ui') }}'
};
    </script>
</head>
<body>
    
    <div id="app"></div>
    <!-- built files will be auto injected -->

    <script src="https://static.idaas.tst:8080/admin.js?123"></script>

</body>
</html>
