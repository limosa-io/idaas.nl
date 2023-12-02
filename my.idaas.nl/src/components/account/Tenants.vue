
<template>

<div>
    <b-modal v-model="showModal" id="newTenant" class="modal-dark" title="New tenant" @ok="onSubmit">
        <form @submit.prevent="onSubmit">
            <div class="form-group">
                <label for="subdomain">Create your tenant on your subdomain</label>
                <input :class="{'is-invalid': errors['subdomain']}" v-model="subdomain" type="text" class="form-control"
                    id="subdomain" aria-describedby="subdomainHelp" placeholder="subdomain">
                <small id="subdomainHelp" class="form-text text-muted">Choose a simple name.</small>

                <div v-for="(e,index) in errors.subdomain" class="invalid-feedback" :key="index">
                    {{ e }}
                </div>

            </div>
        </form>
    </b-modal>

    <template v-if="tenants">

        <div class="tenants w-100">

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Tenant</th>

                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>

                    <tr v-for="(tenant,index) in tenants" :key="index">
                        <td>{{ tenant.subdomain }}<em>.limosa.io</em><span v-if="tenant.isNew" class="badge badge-pill badge-warning ml-3">new</span>
</td>
                        <td style="width:100px;">
                            <a :href="tenant.main_url" target="_blank" class="card-link">Manage</a>
                            <a href="#" @click.prevent="deleteTenant(tenant)" class="card-link">Delete</a>
                        </td>
                    </tr>

                    <tr v-if="tenants.length == 0">
                        <td colspan="3">You haven't created a tenant</td>
                    </tr>

                </tbody>
            </table>
        </div>

        <button class="btn btn-primary mt-2 ml-2 ml-0 mb-2 float-right" @click="generateName"><font-awesome-icon icon="magic" /></button>

        <button class="btn btn-primary mt-2 ml-0 mb-2 float-right" @click="showModal = true">New Tenant</button>

    </template>
    <template v-else-if="approvalNeeded">
        
        <div class="alert alert-primary" role="alert">
            <h2>Thanks for joining</h2>
             <p>Before you can create your own limosa.io environment, your account needs approval. </p>
             <p>We'll inform you when this is done.</p>
        </div>
    </template>
    <template v-else>
        <p>Loading ...</p>
    </template>

</div>

</template>

<script>
import Vue from 'vue';
import { VBModal } from 'bootstrap-vue'
import { BModal } from 'bootstrap-vue'

import settings from '../../settings';

import {
    toLogin,
    noty
} from '../../util';

import {
    library
} from '@fortawesome/fontawesome-svg-core'
import {
    faTh, faUsers, faLayerGroup, faBars, faMagic
} from '@fortawesome/free-solid-svg-icons'
import {
    FontAwesomeIcon
} from '@fortawesome/vue-fontawesome'

import {first, second} from './names'

library.add(faUsers)
library.add(faTh)
library.add(faLayerGroup)
library.add(faBars)
library.add(faMagic)

Vue.component('font-awesome-icon', FontAwesomeIcon)

export default {

    components: {
        'b-modal': BModal
    },

    data() {
        return {
            approvalNeeded: false,
            tenants: null,

            subdomain: null,

            showModal: false,
            errors: {},

            oauth: {
                clientId: settings.client_id,
                redirectUri: settings.redirect_uri
            }

        }
    },
    mounted() {

        console.log('Load tenants ...');

        this.loadTenants().catch(error => {
            
            if (error.status == 403) {
                this.approvalNeeded = true;
                // your 
                // noty('You don\'t have sufficient permissions.', 'error');
            }

        });

    },

    methods: {

        generateName(){

            this.$http.post(settings.tenants_endpoint, {
                subdomain: first[Math.floor(Math.random() * first.length)] + '-' + second[Math.floor(Math.random() * second.length)]
            }, {
                headers: {
                    'Authorization': 'Bearer ' + this.$store.state.accessToken
                }
            }).then(response => {
                // success
            }).catch(response => {
                // retry ...
                this.showMessage('Failed to generate a name. Please choose one yourself.');
            }).finally(() => {
                this.loadTenants();
            });

        },

        showMessage(message) {

            noty(message);

        },

        deleteTenant(tenant) {

            this.$http.delete(`${settings.tenants_endpoint}/${tenant.id}`, {
                headers: {
                    'Authorization': 'Bearer ' + this.$store.state.accessToken
                }
            }).then(response => {

                this.tenants.splice(this.tenants.indexOf(tenant), 1);

                this.showMessage('Tenant is deleted!');

            }, response => {

                this.showMessage('Tenant is deleted!');

            })


        },

        loadTenants() {

            return new Promise((resolve, reject) => {

                this.$http.get(settings.tenants_endpoint, {
                    headers: {
                        'Authorization': 'Bearer ' + this.$store.state.accessToken
                    },
                    no403ErrorInterception: true
                }).then(response => {
                    this.tenants = response.data;

                    resolve(this.tenants);
                }, response => {
                    reject(response);
                })

            });
        },

        initOauth() {

            toLogin();

        },

        onSubmit(event) {

            this.$http.post(settings.tenants_endpoint, {
                subdomain: this.subdomain
            }, {
                headers: {
                    'Authorization': 'Bearer ' + this.$store.state.accessToken
                }
            }).then(response => {
                this.showModal = false;
                return this.loadTenants();
            }, response => {
                this.errors = response.data.errors;
                event.preventDefault();

                throw new Error("Whoops!");
            }).then(tenants => {
                
                tenants.forEach(element => {
                    if(element.subdomain == this.subdomain){
                        element.isNew = true;
                    }
                });
                
                this.subdomain = null;
                this.showMessage('Your tenant is now available');

                this.showModal = false;
                event.preventDefault();

            }).catch(e => {

            });


        }

    }

}
</script>


<style lang="scss">


.section-profile {
    padding-top: 60px;
    padding-bottom: 60px;

    .profile{
        background-color: #fafaff;
        padding: 20px;
    }
}

</style>
