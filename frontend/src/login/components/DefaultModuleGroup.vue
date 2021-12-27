
<template>

<div>

<h1 v-if="$store.state.activeModule == null && authRequest.next.lenght > 1">Sign in to {{ authRequest.info.nam }}</h1>

  <div class="panel panel-default large-form-items">

    <div class="panel-body">

    <slot></slot>

    </div>

  </div>
  
  </div>

</template>


<script>


export default  {

    props: ['authRequest','customerstyle'],

}

</script>