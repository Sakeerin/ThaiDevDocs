<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>

<script setup lang="ts">
// Global app setup
useHead({
  titleTemplate: (titleChunk) => {
    return titleChunk ? `${titleChunk} | ThaiDevDocs` : 'ThaiDevDocs'
  },
})
</script>
