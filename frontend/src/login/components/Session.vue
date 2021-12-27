<template>
    <p>show sessions</p>
</template>

<script>
export default {

    mounted(){

        // call something like /

        this.$get('/api/sessions', {            
            credentials: true
        }).then(r => {
            console.log(r.data);
        })


    }

}
</script>

<style>

</style>
