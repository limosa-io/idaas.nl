<template>
    
</template>

<script>

import {toLogin, toLogout} from '../util'

export default {
    beforeCreate(){
        toLogout();
    }
}
</script>

<style>

</style>
