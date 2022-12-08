<template>
    
<div>

    <p v-if="userinfo" class="text-center">You are logged in as {{ userinfo.email || userinfo.name || userinfo.sub }}</p>
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link" :class="{'active': page == 'tenants'}" href="#" @click.prevent="page = 'tenants'">Tenants</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" :class="{'active': page == 'profile'}" href="#" @click.prevent="page = 'profile'">Profile</a>
        </li>
    </ul>

    <div>
        <Tenants v-if="page == 'tenants'" />
        <Profile v-else-if="page == 'profile'" :code="code" />
    </div>


</div>
</template>

<script>
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

import Tenants from './Tenants';
import Profile from './Profile';
import queryString from 'query-string';
import settings from '../settings';


export default {

    components: {
        Tenants,
        Profile
    },

    data(){
        return {
            page: null,

            //authorization code used for account linking
            code: null,

            userinfo: null
        }
    },

    mounted(){
        
        const parsedSearch = queryString.parse(location.search);
        const parsedHash = queryString.parse(location.hash);

        this.code = parsedSearch.code;
        
        if(this.code){
            this.page = 'profile';
        }else{
            this.page = Object.keys(parsedHash)[0] || 'tenants';
        }
        
        this.$http.get(settings.userinfo_endpoint, {
            headers: {
                'Authorization': `Bearer ${this.$store.state.accessToken}`
            }
        }).then(response => {
            this.userinfo = response.data;
        }).catch(error => {
            if(error.status == 403){
                noty('You don\'t have sufficient permissions','error');
            }
        });
        
    },

    watch: {
        page: function(val){

            history.pushState(null, null, `/#${val}`);

        }
    }
    
}
</script>

<style lang="scss">
@import "~noty/src/noty.scss";
@import "~noty/src/themes/mint.scss";

body .nav-tabs .nav-link.active, body .nav-tabs .nav-item.show .nav-link{
    background-color: lighten(#293243,5%);
    color: #fff;


    &:hover, &:focus{
        border-color: #dee2e6;
    }
}

body .nav-tabs .nav-link:hover, body .nav-tabs .nav-link:focus{
    border-color: transparent;
    border-bottom: 2px solid #46bd87;
}

body th, body td{
    border: none;
}
table {
    display: table;
}

body .row {
    margin-left: 0px!important;
    margin-right: 0px!important;
}

body .row.stats{
    padding-left: 20px;
    margin-top: 50px;
    margin-bottom: 30px;
    border-style: solid;
    border-color: rgb(205, 205, 205);
    border-width: 1px;
    padding-top: 20px;
    box-shadow: 0 2px 5px rgba(26, 109, 70, 0.7);


    > div{
        display: flex;

        > div {
            margin-left: 10px;
        }

        h2 {
            font-size: 1em;
            margin: 0px;
            border-style: none;
        }
    }
    
    svg {
        float: left;
    }
}

.tenants-page {

    a{ 
        color: #46bd87
    }

    button.btn-primary {
        background-color: #46bd87;
        border-color: darken(#46bd87,20%);

        &:hover, &:focus, &:active {
            background-color: darken(#46bd87,20%);
        }
    }

    .form-control {
        border-color: #5f6d86;
        background-color: #293243;
        color: #d2d5db;
    }
}


.tenants > div{

    

    
    &:nth-child(2n+1){
        padding-right: 0.5rem;
    }

    > div {
        background-color: #424f66;
    }

    margin-bottom: 0.5rem;

    padding: 0px;
}

body .tenants-page {

    transition: background-color 0.2s ease;
    min-height: 100vh;

    .content{
        margin-top: 0px!important;
        padding-top: 0px!important;
        padding: 3.6rem 2rem 0;
        max-width: 960px;

        
    }
}

</style>

<style lang="stylus">

$vue-color = #4fc08d
$bg-color = darken(desaturate(hue($vue-color, 220deg), 50%), 60%)
$MQNarrow = 959px
$MQMobile = 719px
$MQMobileNarrow = 419px

body .tenants-page
  background-color $bg-color
  color desaturate(lighten($bg-color, 80%), 50%)

body .tenants-page .search-box
  input
    border-color desaturate(lighten($bg-color, 30%), 30%)
    background-color $bg-color
    color desaturate(lighten($bg-color, 80%), 50%)
  .suggestions
    background-color desaturate(lighten($bg-color, 15%), 10%)
    border 0
  .suggestion
    a
      color desaturate(lighten($bg-color, 80%), 50%)

    &.focused
      background-color desaturate(lighten($bg-color, 30%), 30%)
      a
        color #fff

body .tenants-page .algolia-search-wrapper
  .algolia-autocomplete
    .ds-dropdown-menu
      border: 0
      background-color desaturate(lighten($bg-color, 15%), 10%)

      &:before
        background-color desaturate(lighten($bg-color, 15%), 10%)
        border: 0;

@media (min-width: $MQMobile)
  .tenants-page .dropdown-wrapper
    .nav-dropdown
      background-color desaturate(lighten($bg-color, 15%), 10%)
      border 0

      .dropdown-item
        h4
          border-top-color desaturate(lighten($bg-color, 30%), 30%)

.tenants-page .sw-update-popup
  background-color desaturate(lighten($bg-color, 15%), 10%)
  border 0

.tenants-page .navbar
  border-bottom-color desaturate(lighten($bg-color, 10%), 10%)
  background-color $bg-color
  transition: background-color 0.2s ease;

  .site-name
    color #fff

  .links
    background-color $bg-color
    transition: background-color 0.2s ease;

.tenants-page .page-edit
  .edit-link
    a
      color desaturate(lighten($bg-color, 50%), 50%)
      transition: color 0.2s ease;

      &:hover,
      &:focus
        color #fff
        transition: color 0.2s ease;
  .last-updated
    .prefix
      color desaturate(lighten($bg-color, 50%), 50%)
      transition: color 0.2s ease;
    
    .time
      color desaturate(lighten($bg-color, 40%), 50%)

.tenants-page h2
  border-bottom-color desaturate(lighten($bg-color, 10%), 10%)

.tenants-page hr
  border-top-color desaturate(lighten($bg-color, 10%), 10%)

.tenants-page .page-nav
  .inner
    color desaturate(lighten($bg-color, 50%), 50%)
    border-top-color desaturate(lighten($bg-color, 10%), 10%)

.tenants-page .nav-links
  a
    color desaturate(lighten($bg-color, 80%), 50%)
    transition: color 0.2s ease;

    &:hover,
    &:focus,
    &.router-link-active
        color #fff

</style>


