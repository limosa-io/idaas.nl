<template>

<div>

    <b-modal @ok="onSubmitNewHOTP" ref="modalNewHOTP" id="modalNewHOTP" title="Connect new authenticator" ok-title="I've connected the phone">

        <p>Scan the QR code using an Android or iOS device with a mobile app installed that supports HOTP. For example
            <a href="https://support.google.com/accounts/answer/1066447?co=GENIE.Platform%3DAndroid">Google
                Authenticator</a>
            or
            <a href="https://freeotp.github.io/">FreeOTP</a>.</p>
        <div class="text-center">
            <img :src="qrcode" />
        </div>

    </b-modal>



    <div class="row mt-3">

        <div class="col-md-3">
            Enable One-Time Password
        </div>

        <div class="col-md-9">

            <button v-if="user['arietimmerman:ice'].otpSecretPresent" type="button" class="btn btn-secondary" @click="disableTOTP">Disable</button>
            <button v-else class="btn btn-secondary" type="button" @click="showModal">Enable</button>

        </div>

    </div>

    <div class="row mt-3">

        <div class="col-md-3">
            Connected accounts
        </div>

        <div class="col-md-9">

            <template v-if="user['arietimmerman:ice'].links && user['arietimmerman:ice'].links.length">
                <h3>Linked accounts</h3>

                <button v-for="(link,index) in user['arietimmerman:ice'].links" :key="index" type="button" class="btn btn-primary"
                    @click.prevent="removeLink(link)">
                    unlink {{ link.subject_type }}
                </button>
            </template>

            <button class="btn btn-primary" type="button" @click="startAccountLinking('facebook')" v-if="!hasLink('facebook')">
                Connect Facebook
            </button>

        </div>

    </div>

</div>

</template>

<script>

import Vue from 'vue';
import settings from '../../settings';
import {
    startAccountLinking,
    noty
} from "../../util";


export default {

    data(){
        return {
            otpSecret: null,
            qrcode: null,
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

        disableTOTP(){

            this.user['arietimmerman:ice'].otpSecret = null;

            this.$http.put(settings.scim_endpoint, JSON.stringify(this.user), {
                headers: {
                    'Authorization': `Bearer ${this.$store.state.accessToken}`,
                    'content-type': 'application/scim+json'
                }
            }).then(response => {
                noty('We have succesfully saved this user');

                this.otpSecret = null;
                // this.user['arietimmerman:ice'].otpSecretPresent = false;

                Vue.set(this.user['arietimmerman:ice'], 'otpSecretPresent', false);


            }, error => {
                noty('There were some errors during saving');
            });

        },

        showModal() {

            this.$http.get(settings.hotp_generator_endpoint, {
                headers: {
                    'Authorization': `Bearer ${this.$store.state.accessToken}`
                }
            }).then(response => {

                var identifier = this.user['urn:ietf:params:scim:schemas:core:2.0:User'].emails.find(email => email.primary == true).value;
                
                if (identifier == null && this.user['urn:ietf:params:scim:schemas:core:2.0:User'].emails) {
                    identifier = this.user['urn:ietf:params:scim:schemas:core:2.0:User'].emails[0].value;
                }

                if (identifier == null) {
                    identifier = this.user['urn:ietf:params:scim:schemas:core:2.0:User'].userName || this.user['urn:ietf:params:scim:schemas:core:2.0:User'].id;
                }

                this.otpSecret = response.data.secret;

                import( /* webpackChunkName: "qrious" */ 'qrious').then(module => {

                    const QRious = module.default;

                    var qr = new QRious({
                        value: 'otpauth://totp/' + identifier + '?secret=' + this.otpSecret,
                        size: 300
                    });

                    this.qrcode = qr.toDataURL();

                    this.$refs.modalNewHOTP.show();

                });


            })

        },

        onSubmitNewHOTP(event) {

            this.$refs.modalNewHOTP.hide();

            this.user['arietimmerman:ice'].otpSecret = this.otpSecret;

            
            this.$http.put(settings.scim_endpoint, JSON.stringify(this.user), {
                headers: {
                    'Authorization': `Bearer ${this.$store.state.accessToken}`,
                    'content-type': 'application/scim+json'
                }
            }).then(response => {
                noty('Now you can make use of one-time passwords.');

                this.otpSecret = null;

                Vue.set(this.user['arietimmerman:ice'], 'otpSecretPresent', true);


                this.$store.commit('user', this.user);
                
            }, error => {
                noty('There were some errors during saving');
            });

        },

         startAccountLinking(acrValue) {
            startAccountLinking(acrValue);
        },

        hasLink(link){
            return this.user["arietimmerman:ice"].links != null && this.user["arietimmerman:ice"].links.find(e => e.subject_type == link) != null;
        },

        removeLink(link) {

            this.user["arietimmerman:ice"].links.splice(
                this.user["arietimmerman:ice"].links.indexOf(link),
                1
            );

            this.$http
                .put(settings.scim_endpoint, JSON.stringify(this.user), {
                    headers: {
                        "content-type": "application/scim+json",
                        Authorization: `Bearer ${this.$store.state.accessToken}`
                    }
                })
                .then(response => {
                    noty("Your account has now been unlinked");
                });

        }

    }

    
}

</script>

<style>

</style>
