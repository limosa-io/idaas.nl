
@extends('login')

{{-- @extends('login_debug') --}}

@section('head')
<script type="text/javascript" nonce="{{ $nonce }}">
window.oauthLogout = @json($data);
</script>
@show