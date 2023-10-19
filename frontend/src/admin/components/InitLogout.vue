<template></template>

<script setup>

import { onActivated } from 'vue';

onActivated(() => {
    document.location = `${window.manageClient.end_session_endpoint}?post_logout_redirect_uri=${encodeURIComponent(window.manageClient.post_logout_redirect_uri)}`;
});

</script>

<style></style>
