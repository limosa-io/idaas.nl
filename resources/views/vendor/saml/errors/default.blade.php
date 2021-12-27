@extends('login')

@section('head')
<script type="text/javascript">
window.error = @json($exception->getMessage());
</script>
@show