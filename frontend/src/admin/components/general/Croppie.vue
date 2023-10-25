<template>
  <div id="croppieContainer" ref="croppieContainer" :class="props.customClass"></div>

</template>
<script setup>
import {ref, defineProps, onMounted, defineEmits} from 'vue';
/**
 * Used for image uploads
 */
import {Croppie} from 'croppie';
import 'croppie/croppie.css';

const emit = defineEmits(['result', 'update']);

const props = defineProps({
  boundary: {
    type: Object,
    default: function () {
      return {
        width: 400,
        height: 400
      }
    }
  },
  customClass: String,
  enableExif: Boolean,
  enableOrientation: {
    type: Boolean,
    default: true
  },
  enableResize: {
    type: Boolean,
    default: true
  },
  enableZoom: {
    type: Boolean,
    default: true
  },
  enforceBoundary: {
    type: Boolean,
    default: true
  },
  mouseWheelZoom: {
    type: Boolean,
    default: true
  },
  showZoomer: {
    type: Boolean,
    default: true
  },
  viewport: {
    type: Object,
    default: function () {
      return {
        width: 200,
        height: 200,
        type: 'square'
      }
    }
  },
});

const croppie = ref(null);

onMounted(() => {
  initCroppie();
});

function initCroppie() {
  let el = document.getElementById('croppieContainer');

  el.addEventListener('update', (ev) => {
    emit('update', ev.detail);
  });

  croppie.value = new Croppie(el, {
    boundary: props.boundary,
    enableExif: props.enableExif,
    enableOrientation: props.enableOrientation,
    enableZoom: props.enableZoom,
    enableResize: props.enableResize,
    enforceBoundary: props.enforceBoundary,
    mouseWheelZoom: props.mouseWheelZoom,
    viewport: props.viewport,
    showZoomer: props.showZoomer
  });
}

function bind(options) {
  return croppie.value.bind(options)
}

function destroy() {
  croppie.value.destroy();
}

function get(cb) {
  if (cb) {
    cb(croppie.value.get())
  } else {
    return croppie.value.get()
  }
}

function rotate(angle) {
  croppie.value.rotate(angle);
}

function setZoom(value) {
  croppie.value.setZoom(value);
}

function result(options, cb) {
  if (!options) options = {
    type: 'base64'
  }
  return croppie.value.result(options).then(output => {
    if (!cb) {
      emit('result', output);
    } else {
      cb(output);
    }
    return output;
  });
}

function refresh() {
  croppie.value.destroy();
  initCroppie();
}

</script>
