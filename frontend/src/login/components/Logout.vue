<!--
Initiates a OpenID Connect logout
-->
<template>
    <div class="logout-container">
        <template v-if="logged_out">
        <h1>
            You are now logged out.
        </h1>
        </template>
    </div>
</template>

<style lang="scss" scoped>
.logout-container{
    text-align: center;
    font-family: Arial;
    display: table-cell;
    vertical-align: middle;
    width: 100vw;
    height: 100vh;
    background-color: #58884e;
    color: white;
}
</style>

<script>

export default {

    data(){
        return {
            logged_out: false
        }
    },
    
    mounted(){
        if(window.oauthLogout.valid && window.oauthLogout.post_logout_redirect_uri){

            if(window.oauthLogout.response_mode == 'query'){
                document.location = `${window.oauthLogout.post_logout_redirect_uri}${window.oauthLogout.state ? ('?state=' + decodeURIComponent(window.oauthLogout.state)) : ''}`;
            }else if(window.oauthLogout.response_mode == 'web_message'){
                // to parent ...
                parent.postMessage({
                    type: 'logout_response',
                    response: {
                        state: window.oauthLogout.state
                    }
                }, window.oauthLogout.post_logout_redirect_uri);
            }else{
                this.$store.commit('error', 'invalid_response_mode');
                this.$router.replace({name: 'error.default'});
            }
        }else if(window.oauthLogout.valid){
            this.logged_out = true;
        }else{
            this.$store.commit('error', 'invalid_logout_url');
            this.$router.replace({name: 'error.default'});
        }
    }
}
</script>

