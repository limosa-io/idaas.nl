<template>
  <div class="row">

    <div class="col-md-6 col-sm-12">

      <div class="DashboardContainer"></div>

    </div>

    <div class="col-md-6 col-sm-12" :class="{ 'picker': picker }">
      <template v-if="mediaItems">
        <div class="mediaItem img-thumbnail" v-for="m in mediaItems" :key="m.id" @click="pick(m)">
          <div class="image" :style="{ 'background-image': `url(${m.url})` }">
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

<script setup>

import { ref, onMounted, defineProps, defineEmits } from 'vue';
import Uppy from '@uppy/core'
import AwsS3 from '@uppy/aws-s3'
import Dashboard from '@uppy/dashboard'
import Webcam from '@uppy/webcam'
import { maxios, notify } from '@/admin/helpers.js';

const emit = defineEmits(['picked'])

import 'uppy/dist/uppy.min.css'

const props = defineProps({
  picker: {
    type: Boolean,
    default: false
  }
})

const mediaItems = ref(null);

function deleteMedia(m) {
  maxios.delete('/api/mediaItems/' + m.id).then(response => {
    mediaItems.value.splice(mediaItems.value.indexOf(m), 1);
  });
}

function pick(m) {
  emit('picked', m);
}

function loadImages() {
  maxios.get('/api/mediaItems').then(response => {
    mediaItems.value = response.data;
  }).catch(e => {

  });

}

onMounted(() => {
  loadImages();

  new Uppy({
    debug: true
  }).use(AwsS3, {
    getUploadParameters: (file) => {
      // Send a request to our PHP signing endpoint.
      return maxios.post('/api/s3sign', {
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
        };
      });
    }
  }).use(Dashboard, {
    inline: true,
    target: '.DashboardContainer',
    replaceTargetContent: true,
    note: 'Images only, 1–2 files, up to 1 MB',
  }).use(Webcam, { target: Dashboard }).on('upload-success', (file, resp, uploadURL) => {
    maxios.post('/api/mediaItems', {
      url: resp.uploadURL,
      external_id: file.id,
      size: file.size,
      meta: {
        // width: result.meta.width,
        // height: result.meta.height
      }
    }).then(response => {

      notify({ text: 'We have succesfully uploaded this file!' });

      loadImages();

    }, response => {
      // error callback

      notify({ text: 'We could not save this.', type: 'error' });

    });

  });
});

</script>

<style lang="scss">
.picker .mediaItem:hover {
  border-color: #4285f4;
  background-color: #4285f4;
}

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

  >.image {
    width: 200px;
    height: 200px;
    max-width: 27vw;
    max-height: 27vw;
    background-size: cover;
  }

}
</style>
