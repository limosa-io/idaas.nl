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

        <div class="row mt-3">

        <div class="col-md-3">
            Security keys
        </div>

        <div class="col-md-9">

            
            <table class="table table-striped" v-if="fido_keys">
                <thead>
                    <tr>
                        <th scope="col">Date</th>

                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>

                    <tr v-for="(key,index) in fido_keys" :key="index">
                        <td>{{ new Date(key.created_at*1000).toLocaleDateString("en-US") }}</td>
                        <td style="width:100px;">
                            <a href="#" @click.prevent="deleteFidoKey(key)" class="card-link">Delete</a>
                        </td>
                    </tr>

                    <tr v-if="fido_keys.length == 0">
                        <td colspan="3">You have not added a FIDO key yet</td>
                    </tr>

                </tbody>
            </table>

            <button class="btn btn-secondary" type="button" @click="enableFido">Add Security key</button>



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

function arrayBufferToBase64(buffer) {
    let binary = '';
    let bytes = new Uint8Array(buffer);
    let len = bytes.byteLength;
    for (let i = 0; i < len; i++) {
        binary += String.fromCharCode( bytes[ i ] );
    }
    return window.btoa(binary);
}

export default {

    data(){
        return {
            otpSecret: null,
            qrcode: null,
            fido_keys: null
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

    mounted(){
        this.loadFidoKeys();
    },

    methods: {

        recursiveBase64StrToArrayBuffer: function (obj) {
            let prefix = '=?BINARY?B?';
            let suffix = '?=';
            if (typeof obj === 'object') {
                for (let key in obj) {
                    if (typeof obj[key] === 'string') {
                        let str = obj[key];
                        if (str.substring(0, prefix.length) === prefix && str.substring(str.length - suffix.length) === suffix) {
                            str = str.substring(prefix.length, str.length - suffix.length);

                            let binary_string = window.atob(str);
                            let len = binary_string.length;
                            let bytes = new Uint8Array(len);
                            for (let i = 0; i < len; i++)        {
                                bytes[i] = binary_string.charCodeAt(i);
                            }
                            obj[key] = bytes.buffer;
                        }
                    } else {
                        this.recursiveBase64StrToArrayBuffer(obj[key]);
                    }
                }
            }
        },

        createRegistration: async function(createArgs, challenge) {
            try {

                // check browser support
                if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
                    throw new Error('Browser not supported.');
                }

                // error handling
                if (createArgs.success === false) {
                    throw new Error(createArgs.msg || 'unknown error occured');
                }

                // replace binary base64 data with ArrayBuffer. a other way to do this
                // is the reviver function of JSON.parse()
                this.recursiveBase64StrToArrayBuffer(createArgs);

                // create credentials
                const cred = await navigator.credentials.create(createArgs);

                // create object
                const authenticatorAttestationResponse = {
                    transports: cred.response.getTransports  ? cred.response.getTransports() : null,
                    clientDataJSON: cred.response.clientDataJSON  ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
                    attestationObject: cred.response.attestationObject ? arrayBufferToBase64(cred.response.attestationObject) : null
                };

                const authenticatorAttestationServerResponse = await this.$http.post(
                    settings.fido_register_endpoint,
                    authenticatorAttestationResponse,
                    {
                        headers: {
                            'Authorization': `Bearer ${this.$store.state.accessToken}`,
                            'x-challenge': challenge
                        }
                    }
                );

                this.loadFidoKeys();

                // prompt server response
                if (authenticatorAttestationServerResponse.success) {
                    // do nothing
                } else {
                    throw new Error(authenticatorAttestationServerResponse.msg);
                }

            } catch (err) {
                throw err;
            }
        },

        enableFido(){
            this.$http.get(settings.fido_get_create_arguments_endpoint, {
                headers: {
                    'Authorization': `Bearer ${this.$store.state.accessToken}`
                }
            }).then(response => {
                this.createRegistration(response.data, response.headers.get('x-challenge')); 
            });
        },

        loadFidoKeys(){
            this.$http.get(settings.fido_list_keys_endpoint, {
                headers: {
                    'Authorization': `Bearer ${this.$store.state.accessToken}`
                }
            }).then(data => {
                this.fido_keys = data.body;
            });
        },

        deleteFidoKey(key){
            this.$http.delete(settings.fido_endpoint + '/' + key.id, {
                headers: {
                    'Authorization': `Bearer ${this.$store.state.accessToken}`
                }
            }).then(data => {
                this.fido_keys.splice(this.fido_keys.indexOf(key), 1);
            });
        },

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
