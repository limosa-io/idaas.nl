<template>
    
<div class="container-fluid">

  <h4 class="c-grey-900 mt-1 mb-3">Media Manager</h4>

  <div class="bgc-white bd bdrs-3 p-3 mt-2">
    <Picker />
  </div>

</div>

</template>

<script>

//TODO: on upload finished, save urls and ids in Media Object. Only expose public url for now.

// Use digital ocean s3 storage => https://www.digitalocean.com/community/questions/signed-urls-for-private-objects-in-spaces
// https://github.com/transloadit/uppy/tree/master/examples/digitalocean-spaces

export default {

  components: {
    Picker: () => import('./Picker')
  },

  data(){
    return {
      mediaItems: null
    }
  },

  methods: {
    loadImages(){
      

    }
  },

    mounted(){

      console.log('media!!');
      
        //   var file = uppy.getFile(result.localId)
        //   var div = document.createElement('div');
        //   div.innerHTML = `
        //     <div>
        //       <h2>Name: ${file.name}</h2>
        //       <img src="${result.ssl_url}" /> <br />
        //       <a href="${result.ssl_url}"> View </a>
        //     </div>
        //   `
        //   document.body.appendChild(div)

        // });



    }

}
</script>

<style lang="scss">

.mediaItem {

  position: relative;
  float: left;
  margin: 5px;

  .buttons{
    position: absolute;
    display: flex;
    visibility: hidden;
    bottom: 0px;
    width: 100%;
    height: 50px;
    background-color: rgba(0,0,0,0.8);
    
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
