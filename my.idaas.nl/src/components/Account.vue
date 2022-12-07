<template>

<div>

    <b-modal :no-close-on-esc="true" :no-close-on-backdrop="true" :hide-header-close="true" @ok="login" :ok-only="true"
        header-bg-variant="danger" header-text-variant="light" ref="notAuthenticatedModal" id="modal1" title="Authentication required">
        <p class="my-4">You need to authenticate yourself!</p>
    </b-modal>

    <div class="account-container" v-if="loaded">

        <div class="logo">
            <a href="/"><img src="../assets/bulb.svg" /></a>
        </div>

        <div class="modal special-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        
                        <button type="button" class="btn btn-light btn-sm mr-2 mb-0 mt-0" @click.stop="showNavigation = true">
                            <font-awesome-icon icon="bars" />
                        </button>

                        <h5 class="modal-title">Account Settings</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div class="row profile">
                            <div class="col-3" v-click-outside="hideNavigation" :class="{'show-navigation': showNavigation}">
                                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                    <router-link to="/" :exact="true" active-class="active" class="nav-link" id="v-pills-home-tab"
                                        data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home"
                                        aria-selected="true">Tenants</router-link>
                                    <router-link to="/profile" :exact="true" active-class="active" class="nav-link" id="v-pills-profile-tab"
                                        data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile"
                                        aria-selected="false">Profile</router-link>
                                    <router-link to="/password" :exact="true" active-class="active" class="nav-link" id="v-pills-profile-tab"
                                        data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile"
                                        aria-selected="false">Password</router-link>

                                    <router-link to="/security" :exact="true" active-class="active" class="nav-link" id="v-pills-profile-tab"
                                        data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile"
                                        aria-selected="false">Security</router-link>
                                </div>
                            </div>
                            <div class="col overflow-auto">
                                <div class="tab-content" id="v-pills-tabContent">
                                    <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel"
                                        aria-labelledby="v-pills-home-tab">

                                        <router-view v-if="loaded"></router-view>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="home">Home</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal" @click="logout">Log out</button>

                    </div>
                </div>
            </div>

        </div>

        <h1>idaas.nl</h1>

    </div>
</div>

</template>

<script>

import settings from "../settings";
import {
    toLogin,
    toLogout
} from '../util'
import Vue from 'vue'

export default {

    methods: {

        hideNavigation(){
            this.showNavigation = false;
        },

        close() {
            // this.$router.push('/');
            document.location = '/';
        },

        login() {
            toLogin();
        },

        home() {
            document.location = 'https://www.idaas.nl';
        },

        logout() {
            toLogout();
        },

        loginNeeded() {
            this.$refs.notAuthenticatedModal.show();

        }
    },

    data() {
        return {
            loaded: false,

            showNavigation: false
        }
    },

    mounted(){

    
    },

    watch:{
        $route (to, from){
            this.showNavigation = false;
        }
    },

    created() {

        Vue.http.interceptors.push(request => {

            return (response) => {
                
                    if ((!response.ok && response.status == 0) || response.status == 401 || response.status == 500) {
                        
                        // TODO: fails if is not mounted yet, wait for mount
                        // window.localStorage.clear();
                        this.loginNeeded();
                    }

                    if(!request.no403ErrorInterception && response.status == 403){
                        this.loginNeeded();
                    }
                

            };

        });
        console.log(import.meta.env);
        this.$http
            .get(settings.scim_endpoint, {
                headers: {
                    Authorization: `Bearer ${this.$store.state.accessToken}`
                }
            })
            .then(response => {

                this.loaded = true;
                console.log('Loaded user ...');

                let user = response.data;

                if (!user["arietimmerman:ice"]) {
                    user["arietimmerman:ice"] = {};
                }

                if (!user["arietimmerman:ice"].links) {
                    user["arietimmerman:ice"].links = [];
                }

                this.$store.commit('user', user);

                window.drift.on('ready', function() {
                    drift.identify(user.id, {
                        email: user['urn:ietf:params:scim:schemas:core:2.0:User'].emails.find(e => e.primary).value
                    });
                });

            })
            .catch(error => {
                if (error.status == 403) {
                    noty("You don't have sufficient permissions", "error");
                }
            });

    }

}

</script>

<style lang="scss">

// $theme-colors: (primary: $blue, secondary: $gray-600, success: $green, info: $cyan, warning: $yellow, danger: $red, light: $gray-100, dark: $gray-800);

$theme-colors: (
  "primary": #3eaf7c
);

@import "bootstrap/scss/bootstrap";
@import "noty/src/noty.scss";
@import "noty/src/themes/bootstrap-v4.scss";

$background-color: lighten(#3eaf7c, 10%);

.account-container {

    .modal-header button {
        display: none;
    }

    @include media-breakpoint-down(md) {

        .modal-header button {
            display: block;
        }
        
        .profile > .col-3 {
            position: fixed;
            left: 0px;
            height: 100%;
            width: 240px;
            top: 0px;
            z-index: 10;
            background-color: white;
            padding-top: 2rem;
            display: none;
            max-width: 100%;

            // on click outside > hide

            &.show-navigation {
                display: block;
            }
        }
    }

    display: flex;
    justify-content: center;
    align-items: center;
    width: 100vw;
    flex-direction: column;

    .overflow-auto{
        overflow-y: auto;
        max-height: 100%;
    }

    .logo {

        img{
            height: 130px;
            margin: 30px;
        }

    }

    h1 {
        color: rgb(31, 31, 31);
        font-size: 1rem;
        position: absolute;
        text-align: center;
        width: 100%;
        padding: 20px;
        position: relative;
    }

    margin: 0px;
    padding: 0px;

    width: 100vw;
    min-height: 100vh;

    background-color: white;

    .modal.special-modal {

        position: relative;

        z-index: 1;

        display: block;
        width: 60vw;
        max-width: 1000px;
        min-height: 600px;
        height: 600px;
        box-shadow: 0 3px 7px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(0, 0, 0, 0.3);

        > .modal-dialog {
            max-width: inherit;
            margin: 0px;

            min-height: 100%;
            height: 100%;            

            > .modal-content {
                border-radius: 0px !important;
                min-height: 100%;
                height: 100%;
                border-style: none;

                > .modal-header {
                    border-radius: 0px !important;
                    background-color: map-get($theme-colors,'primary');
                    color: white;

                    .close {
                        color: white;
                    }
                }

                > .modal-body {
                    height: 0;

                    .profile {
                        height: 100%;

                        .col-3 {
                            border-right-width: 1px;
                            border-right-style: dashed;
                            border-right-color: rgb(160, 160, 160);
                        }

                        .table thead th {
                            border-top-style: none;
                        }
                    }


                }
            }
        }

        @include media-breakpoint-down(sm) {
            max-width: 100%;
            width: 100%;
        }

        .section-profile {
            .container {
                padding: 0px;
                margin-left: 15px;
                margin-right: 15px;
                max-width: calc(100% - 30px);

            }
        }
    }
}

</style>

