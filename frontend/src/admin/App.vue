<template>
  <div id="app">
   <router-view></router-view>
  </div>
</template>

<script>

export default {

}

</script>

<style lang="scss">

body {
  text-align: left;
}

.container-fluid h4{
  padding-top: 10px;
}

.multiselect.form-control{
  padding: 0px;
  border-style: none;
  height: auto;

  &.is-invalid .multiselect__tags {
    border-color: red;
  }
}

button { 
  white-space: nowrap;
}

</style>
