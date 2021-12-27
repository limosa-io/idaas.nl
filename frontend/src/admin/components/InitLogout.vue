<template>
    
</template>

<script>
export default {

    beforeRouteEnter(to, from, next) {
        
        document.location = `${window.manageClient.end_session_endpoint}?post_logout_redirect_uri=${encodeURIComponent(window.manageClient.post_logout_redirect_uri)}`;
        
    }

}
</script>

<style>

</style>
