<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>

<script setup lang="ts">
useHead({
  titleTemplate: (titleChunk) => {
    return titleChunk ? `${titleChunk} | ThaiDevDocs` : 'ThaiDevDocs'
  },
})

// Auth is initialized in plugins/auth.ts before render
const { user } = useAuth()

// Sync theme preference from user profile when available
const colorMode = useColorMode()

watch(
  () => user.value?.preferences?.theme,
  (theme) => {
    if (theme && theme !== 'system') {
      colorMode.preference = theme
    }
  },
  { immediate: true },
)
</script>
