<template>
  <div>
    <button @click="goToPage(1)" :disabled="currentPage === 1">&lt;&lt;</button>
    <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1">&lt;</button>
    <button v-for="page in pages" :key="page" @click="goToPage(page)" :disabled="currentPage === page">{{ page }}</button>
    <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages">&gt;</button>
    <button @click="goToPage(totalPages)" :disabled="currentPage === totalPages">&gt;&gt;</button>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'


const emit = defineEmits(['update:modelValue', 'input']);

// properties: per-page, total-rows, modelValue
const props = defineProps({
  perPage: {
    type: Number,
    required: true
  },
  totalRows: {
    type: Number,
    required: true
  },
  modelValue: {
    type: Number,
    required: true
  }
})

const totalPages = computed(() => Math.ceil(props.totalRows / props.perPage));

const currentPage = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('input', value);
    emit('update:modelValue', value);
  }
})

/**
 * Computes the range of pages to display in the pagination component.
 * @returns {Array} The array of page numbers to display.
 */
const pages = computed(() => {
  const range = []
  for (let i = Math.max(2, currentPage.value - 2); i <= Math.min(totalPages.value - 1, currentPage.value + 2); i++) {
    range.push(i)
  }
  if (range[0] > 2) {
    range.unshift('...')
    range.unshift(1)
  } else if (range[0] === 2) {
    range.unshift(1)
  }
  if (range[range.length - 1] < totalPages.value - 1) {
    range.push('...')
    range.push(totalPages.value)
  } else if (range[range.length - 1] === totalPages.value - 1) {
    range.push(totalPages.value)
  }
  return range
})

function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}
</script>
