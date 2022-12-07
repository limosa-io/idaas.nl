<template>
    <p>Loading ...</p>
</template>

<script>

import queryString from 'query-string';
import settings from '@/settings';
import { noty } from '../../util';

export default {

    computed: {

        user: {
            get () {
                return this.$store.state.user
            },
            set (value) {
                this.$store.commit('user', value)
            }
        }

    },

    mounted(){

        const parsedSearch = queryString.parse(location.search);

        let code = parsedSearch.code;
        
        this.$http.post(settings.token_endpoint, {
            code: code,
            client_id: settings.client_id,
            grant_type: 'authorization_code',
            redirect_uri: settings.redirect_uri_account_linking
        }, {

        }).then(response => {

            return this.$http.get(settings.userinfo_endpoint, {
                headers: {
                    'Authorization': 'Bearer ' + response.data.access_token
                }
            });

        }).then(response => {
            
            var userinfo = response.data;

            if(!this.user['arietimmerman:ice']){
                this.user['arietimmerman:ice'] = {};
            }

            if(!this.user['arietimmerman:ice'].links){
                this.user['arietimmerman:ice'].links = [];
            }

            this.user['arietimmerman:ice'].links.push({
                subject_type: null,
                subject_id: userinfo.sub
            });

            return this.$http.put(settings.scim_endpoint, JSON.stringify(this.user), {
                headers: {
                    'content-type': 'application/scim+json',
                    'Authorization': `Bearer ${this.$store.state.accessToken}`
                }
            });

        }).then(response => {

            this.$store.commit('user', response.data);

            noty('Successfully linked your account');
            this.$router.replace('/');

        });


        



    }

}
</script>

<style>

</style>
