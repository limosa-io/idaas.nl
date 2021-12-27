<template>
    

  <div class="row">

    <div class="col-md-6 col-sm-12">

      <div class="DashboardContainer"></div>

    </div>
    
    <div class="col-md-6 col-sm-12" :class="{'picker': picker}">
       <template v-if="mediaItems" >
          <div class="mediaItem img-thumbnail" v-for="m in mediaItems" :key="m.id" @click="pick(m)">
              <div class="image" :style="{'background-image': `url(${m.url})`}">
              </div>
              
              <div class="buttons" v-if="!picker">
                <a class="btn btn-sm btn-secondary" target="_blank" :href="m.url">open</a>
                <button class="btn btn-sm btn-danger ml-2" @click="deleteMedia(m)">delete</button>
              </div>
              
          </div>
      </template>

    </div>

  </div>


</template>

<script>

//TODO: on upload finished, save urls and ids in Media Object. Only expose public url for now.

// Use digital ocean s3 storage => https://www.digitalocean.com/community/questions/signed-urls-for-private-objects-in-spaces
// https://github.com/transloadit/uppy/tree/master/examples/digitalocean-spaces

import Uppy from '@uppy/core'
import AwsS3 from '@uppy/aws-s3'
// import Transloadit from '@uppy/transloadit'
import Dashboard from '@uppy/dashboard'
import Webcam from '@uppy/webcam'

import 'uppy/dist/uppy.min.css'

export default {
  props : {
      picker: {
          type: Boolean,
          default: false
      }
  },

  data(){
    return {
      mediaItems: null
    }
  },

  methods: {

    deleteMedia(m){
      this.$http.delete(this.$murl('api/mediaItems/' + m.id)).then(response => {
        this.mediaItems.splice(this.mediaItems.indexOf(m), 1);
      });
    },
    
    pick(m){
        this.$emit('picked', m);
    },

    loadImages(){
      this.$http.get(this.$murl('api/mediaItems')).then(response => {
        this.mediaItems = response.data;
      }).catch(e => {

      });

    }
  },

    mounted(){

      this.loadImages();
      
        Uppy({
          debug: true
        })
        .use(AwsS3, {
        getUploadParameters: (file) => {
          // Send a request to our PHP signing endpoint.
          return this.$http.post(this.$murl('api/s3sign'),{
            filename: file.name,
            contentType: file.type
          }).then(response => {
            return response.data;
          }).then(data => {
            return {
              method: data.method,
              url: data.url,
              fields: data.fields,
              headers: {
                'x-amz-acl': 'public-read'
              }
            }
          });
          
        }
      })
        .use(Dashboard, {
          inline: true,
          target: '.DashboardContainer',
          replaceTargetContent: true,
          note: 'Images only, 1–2 files, up to 1 MB',
        })
       
        .use(Webcam, { target: Dashboard })
        .on('upload-success', (file, resp, uploadURL) => {
          this.$http.post(this.$murl('api/mediaItems'), {
            url: resp.uploadURL,
            external_id: file.id,
            size: file.size,
            meta: {
              // width: result.meta.width,
              // height: result.meta.height
            }
          }).then(response => {
            
            this.$noty({text: 'We have succesfully uploaded this file!'});

            this.loadImages();

          }, response => {
            // error callback

            this.$noty({text: 'We could not save this.', type: 'error'});

          });


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

        });



    }

}
</script>

<style lang="scss">

.picker .mediaItem:hover{
    border-color: #4285f4;
    background-color: #4285f4;
}

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
