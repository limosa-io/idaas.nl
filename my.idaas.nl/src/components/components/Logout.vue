<template>
    
</template>

<script>

export default {
    mounted(){
        window.localStorage.removeItem('access_token');
        this.$store.commit('accessToken', null);

        this.$router.replace('/');
    }    
}
</script>
