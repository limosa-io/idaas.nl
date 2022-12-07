<template>

<div>
    <form class="pt-3">
        <div class="row" v-if="user">
            <div class="col-md-6">
                <h2>Information</h2>

                <b-form-group v-for="(email, index) in user['urn:ietf:params:scim:schemas:core:2.0:User'].emails"
                    v-bind:key="index" horizontal :label-cols="3" description="E-mail." label="E-mail" label-for="user.email">
                    <b-form-input :class="{'is-invalid': errors['urn:ietf:params:scim:schemas:core:2.0:User:emails.' + index + '.value'] ? true : false }"
                        id="user.email" v-model="email.value"></b-form-input>

                    <div v-if="errors['urn:ietf:params:scim:schemas:core:2.0:User:emails.' + index + '.value']" class="invalid-feedback">
                        This is a required field and must be a valid mail address.
                    </div>

                </b-form-group>

                <b-form-group horizontal :label-cols="3" description="Ensure you're using a secure password." label="Password"
                    label-for="password">
                    <b-form-input type="password" id="password" v-model.trim="password"></b-form-input>
                </b-form-group>


            </div>

            <div class="col-md-6">

                <h2>Security</h2>

                <p>Configure one-time password</p>

                <h3>Linked accounts</h3>

                <template v-if="user['arietimmerman:ice'].links && user['arietimmerman:ice'].links.length">
                    <button v-for="(link,index) in user['arietimmerman:ice'].links" :key="index" type="button" class="btn btn-primary btn-block"
                        @click.prevent="removeLink(link)">
                        {{ link.subject_type }}
                    </button>
                </template>

                <h3>Link new account</h3>

                <button class="btn btn-primary btn-block" type="button" @click="startAccountLinking('facebook')">
                        Facebook
                </button>

            </div>

            <div class="col-md-12 mt-3">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>

        </div>

    </form>

</div>

</template>

<script>
import queryString from 'query-string';
import settings from '../settings';
import {
    startAccountLinking,
    noty
} from '../util';

import {BFormGroup, BFormInput} from 'bootstrap-vue'

export default {

    components: {
        BFormGroup,
        BFormInput
    },

    props: ['code'],

    data() {
        return {
            user: null,
            errors: {},
            password: null
        }
    },

    mounted() {

        this.$http.get(settings.scim_endpoint, {
            headers: {
                'Authorization': `Bearer ${this.$store.state.accessToken}`
            }
        }).then(response => {
            console.log(response.data);
            this.user = response.data;

        }).catch(error => {
            if (error.status == 403) {
                noty('You don\'t have sufficient permissions', 'error');
            }
        });

        if (this.code) {

            this.$http.post(settings.token_endpoint, {
                code: this.code,
                client_id: settings.client_id,
                grant_type: 'authorization_code',
                redirect_uri: settings.redirect_uri_account_linking
            }, {

            }).then(response => {

                console.log('call userinfo ...');
                return this.$http.get(settings.userinfo_endpoint, {
                    headers: {
                        'Authorization': 'Bearer ' + response.data.access_token
                    }
                });

            }).then(response => {

                var userinfo = response.data;

                if (!this.user['arietimmerman:ice']) {
                    this.user['arietimmerman:ice'] = {};
                }

                if (!this.user['arietimmerman:ice'].links) {
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

                this.user = response.data;

            });

            this.$router.replace('/');

        }

    },

    methods: {

        startAccountLinking(acrValue) {
            startAccountLinking(acrValue);
        },

        removeLink(link) {

            this.user['arietimmerman:ice'].links.splice(this.user['arietimmerman:ice'].links.indexOf(link), 1);

            this.$http.put(settings.scim_endpoint, JSON.stringify(this.user), {
                headers: {
                    'content-type': 'application/scim+json',
                    'Authorization': `Bearer ${this.$store.state.accessToken}`
                }
            }).then(response => {
                noty('Unlinked the accuont');
            });


        }

    }

}
</script>

