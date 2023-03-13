<template>
  <div div class="container-fluid">
    <slot name="header"></slot>

    <h4 class="c-grey-900 mt-1 mb-3">{{ title }}</h4>

    <div class="row">
      <div class="col-md-12">
        <div class="bgc-white bd bdrs-3 p-3 mt-2">
          <slot name="body"></slot>
        </div>
      </div>
    </div>

    <slot name="footer"></slot>

  </div>
</template>

<script>
export default {
  name: "main",
  props: ['title'],
};
</script>

<style>
</style>