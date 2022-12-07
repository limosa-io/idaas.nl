<template>

<div>
 

    <div class="row" v-if="user">

        <div class="col-md-12">

            <form class="pt-3" @submit.prevent="changeEmail">

                <b-form-group v-for="(email, index) in user['urn:ietf:params:scim:schemas:core:2.0:User'].emails"
                    v-bind:key="index" horizontal :label-cols="3" description="You can use this address to log in" label="E-mail" label-for="user.email">

                    <b-form-input :class="{'is-invalid': errors['urn:ietf:params:scim:schemas:core:2.0:User:emails.' + index + '.value'] ? true : false }"
                        id="user.email" v-model="email.value"></b-form-input>

                    <div v-if="errors['urn:ietf:params:scim:schemas:core:2.0:User:emails.' + index + '.value']" class="invalid-feedback">
                        This is a required field and must be a valid mail address.
                    </div>

                </b-form-group>

                

                <button type="submit" class="btn btn-primary float-right mb-3">Change E-mail</button>

                <div class="alert alert-warning" style="clear: both;" role="alert" v-if="pendingEmail != null && pendingEmail != user['urn:ietf:params:scim:schemas:core:2.0:User'].emails.find(e => e.primary).value">
                    Check the mailbox of <strong>{{pendingEmail}}</strong> to confirm your mail change.
                </div>

                


            </form>

        </div>

    </div>

</div>

</template>

<script>
import queryString from "query-string";
import settings from "../../settings";
import {
    startAccountLinking,
    noty
} from "../../util";

import {BFormInput} from "bootstrap-vue";

export default {
    components: {
        // bFormGroup,
        BFormInput
    },

    props: ["code"],

    data() {
        return {
            //user: null,
            errors: {},

            pendingEmail: null,
            password: null,
        };
    },

    computed: {

        currentMail() {
            return 'test123';
        },

        primaryMail(){
            return this.user['urn:ietf:params:scim:schemas:core:2.0:User'].emails.find(e => e.primary).value;
        }, 

        user: {
            get () {
                return this.$store.state.user
            },
            set (value) {
                console.log('test');
                this.$store.commit('user', value)
            }
        }

    },

    mounted(){

        if(this.primaryMail == window.localStorage.getItem('email_new')){
            window.localStorage.removeItem('email_new');
        }

        this.pendingEmail = this.primaryMail != window.localStorage.getItem('email_new') ? window.localStorage.getItem('email_new') : null;
    },

    methods: {

        changeEmail() {

            const primary = this.user['urn:ietf:params:scim:schemas:core:2.0:User'].emails.find(v => v.primary == true).value;
            let url = document.location.origin + this.$router.resolve({
                name: 'confirm_mail'
            }).href + '?token={TOKEN}';

            window.localStorage.setItem('email_new', primary);

            this.$http
                .post(settings.update_email_endpoint, {
                    url: url,
                    email: primary,
                }, {
                    headers: {
                        Authorization: `Bearer ${this.$store.state.accessToken}`
                    }
                }).then(response => {

                    noty("We've send you an email to confirm this change.");
                    this.pendingEmail = primary;

                });

        }

       
    }
};
</script>

<style scoped lang="scss">

.banner_area{
    height: 100px;
}
</style>
