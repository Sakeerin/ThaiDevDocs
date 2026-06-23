<template>
  <form
    class="space-y-6 bg-slate-800/50 backdrop-blur-xl p-8 rounded-2xl border border-slate-700"
    @submit.prevent="$emit('submit')"
  >
    <slot />
  </form>
</template>

<script setup lang="ts">
defineEmits<{
  submit: []
}>()
</script>
