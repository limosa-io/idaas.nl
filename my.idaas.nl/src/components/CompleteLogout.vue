<template>
</template>

<script>

import {toLogin, toLogout} from '../util'

export default {

    mounted(){
        
        this.$store.commit('accessToken', null);
        
        window.sessionStorage.clear();
        window.localStorage.clear();

        window.location = 'https://www.idaas.nl';

    }

}
</script>

<style>

</style>
