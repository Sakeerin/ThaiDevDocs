<template>
  <div>
    <div class="text-center">
      <h2 class="text-3xl font-bold text-white">{{ title }}</h2>
      <p v-if="$slots.subtitle" class="mt-2 text-gray-400">
        <slot name="subtitle" />
      </p>
    </div>

    <AuthFormCard class="mt-8" @submit="$emit('submit')">
      <slot />
    </AuthFormCard>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string
}>()

defineEmits<{
  submit: []
}>()
</script>
