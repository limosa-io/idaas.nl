<template>
</template>

<script>

import Vue from 'vue';
import settings from '../settings';
import queryString from 'query-string';

import {
    noty
} from '../util.js';

export default {

    mounted() {

        // TODO: exchange token

        const parsedQuery = queryString.parse(location.search);

        if(parsedQuery.token){

            this.$http.post(settings.token_endpoint, {
            grant_type: 'urn:ietf:params:oauth:grant-type:token-exchange',
            subject_token_type: 'io.orange:one-time-token',
            subject_token: parsedQuery.token
        }, {
            headers: {
                 'Authorization': `Bearer ${this.$store.state.accessToken}`
            }

        }).then(response => {

            window.localStorage.setItem('access_token', response.data.access_token);

            this.$http
            .get(settings.scim_endpoint, {
                headers: {
                    Authorization: `Bearer ${this.$store.state.accessToken}`
                }
            })
            .then(response => {
                var user = response.data
                
                //TODO: do a SCIM put
                this.$store.commit('accessToken', window.localStorage.getItem('access_token'));

                user['urn:ietf:params:scim:schemas:core:2.0:User'].emails.find(email => email.primary == true).value = window.localStorage.getItem('email_new');
                
                return this.$http.put(settings.scim_endpoint, JSON.stringify(user), {
                    headers: {
                        "content-type": "application/scim+json",
                        Authorization: `Bearer ${this.$store.state.accessToken}`
                    }
                });

            }).then(response => {

                noty('Your email has now been changed');

                this.$router.replace('/profile');

            });
            
        })

        }
        

    }

}
</script>
