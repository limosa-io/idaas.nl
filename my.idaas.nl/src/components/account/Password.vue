<template>
    <div>
        <div class="row">

        <div class="col-md-12">

            <form class="pt-3" @submit.prevent="storeUser">

                <b-form-group horizontal :label-cols="3" description="Ensure you're using a secure password." label="Password"
                    label-for="password">
                    <b-form-input type="password" id="password" v-model="password"></b-form-input>
                </b-form-group>


                <button type="submit" class="btn btn-primary float-right">Change Password</button>
            </form>

        </div>
    </div>
    </div>
</template>

<script>

import settings from '../../settings';
import {
    noty
} from "../../util";

export default {
    
    data(){
        return {
            password: null
        }
    },

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

    methods: {
        storeUser() {

            var user = JSON.parse(JSON.stringify(this.user));

            user["urn:ietf:params:scim:schemas:core:2.0:User"].password = this.password;
            
            this.$http
                .put(settings.scim_endpoint, JSON.stringify(user), {
                    headers: {
                        "content-type": "application/scim+json",
                        Authorization: `Bearer ${this.$store.state.accessToken}`
                    }
                })
                .then(response => {
                    noty("You've changed your password");
                });

        },
    }

}
</script>

<style>

</style>
