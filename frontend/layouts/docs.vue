<template>
  <div class="min-h-screen flex flex-col">
    <AppHeader />
    <div class="flex-1 flex">
      <!-- Sidebar -->
      <aside 
        class="hidden lg:block w-64 xl:w-72 flex-shrink-0 border-r border-gray-200 dark:border-gray-800"
      >
        <div class="sticky top-16 h-[calc(100vh-4rem)] overflow-y-auto scrollbar-custom p-4">
          <DocsSidebar />
        </div>
      </aside>

      <!-- Main Content -->
      <div class="flex-1 min-w-0">
        <slot />
      </div>

      <!-- Table of Contents -->
      <aside 
        class="hidden xl:block w-64 flex-shrink-0 border-l border-gray-200 dark:border-gray-800"
      >
        <div class="sticky top-16 h-[calc(100vh-4rem)] overflow-y-auto scrollbar-custom p-4">
          <DocsToc />
        </div>
      </aside>
    </div>
    <AppFooter />
  </div>
</template>

<script setup lang="ts">
// Documentation layout with sidebar and TOC
</script>
