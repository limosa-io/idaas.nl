<template>
  <Main title="Media Manager">
    <Picker />
  </Main>
</template>

<script>
export default {
  components: {
    Picker: () => import("./Picker.vue"),
  },

  data() {
    return {
      mediaItems: null,
    };
  },

  methods: {
    loadImages() {},
  },

  mounted() {

  },
};
</script>

<style lang="scss">
.mediaItem {
  position: relative;
  float: left;
  margin: 5px;

  .buttons {
    position: absolute;
    display: flex;
    visibility: hidden;
    bottom: 0px;
    width: 100%;
    height: 50px;
    background-color: rgba(0, 0, 0, 0.8);

    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: visibility 0s, opacity 0.2s linear;
  }

  &:hover .buttons {
    visibility: visible;
    opacity: 1;
  }

  > .image {
    width: 200px;
    height: 200px;
    max-width: 27vw;
    max-height: 27vw;
    background-size: cover;
  }
}
</style>
