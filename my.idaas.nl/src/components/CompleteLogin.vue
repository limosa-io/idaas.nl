<template>
     <div class="error-container" v-if="error">
        <h1>We could not authenticate you</h1>
        <p>Something went wrong... Please <a href="/">try again</a></p>
    </div>
</template>

<style lang="scss" scoped>
.error-container{
    text-align: center;
    font-family: Arial;
    display: table-cell;
    vertical-align: middle;
    width: 100vw;
    height: 100vh;
    background-color: #2e72d9;
    color: white;

    a {
        color: white;
        text-decoration: underline;
    }
}
</style>

<script>

import Vue from 'vue';
import settings from '../settings';
import queryString from 'query-string';
import {
    noty
} from '../util.js';

export default {

    data(){
        return {
            error: false
        }
    },

    mounted() {

        const parsedSearch = queryString.parse(location.search);

        if (parsedSearch.code) {

            this.$http.post(settings.token_endpoint, {
                code: parsedSearch.code,
                client_id: settings.client_id,
                grant_type: 'authorization_code',
                redirect_uri: settings.redirect_uri
            }, {
                headers: {
                    //  'Authorization': 'Bearer ' + this.getAccessToken()
                }

            }).then(response => {
                window.localStorage.setItem('access_token', response.data.access_token);

                this.$store.commit('accessToken', window.localStorage.getItem('access_token'))

                this.$router.push('/');
            }).catch( response => {
                this.error= true;
            });

        } else {
            this.error = true;
        }

    }

}
</script>
