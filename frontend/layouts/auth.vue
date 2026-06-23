<template>
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <div class="max-w-md w-full space-y-8">
      <div class="text-center">
        <NuxtLink to="/" class="inline-flex items-center gap-3">
          <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl flex items-center justify-center">
            <span class="text-white font-bold text-xl">ไทย</span>
          </div>
          <span class="text-2xl font-bold text-white">DevDocs</span>
        </NuxtLink>
      </div>

      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
// Auth layout shell — pages should set middleware: 'guest'
</script>
