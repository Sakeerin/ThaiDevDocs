export default defineNuxtPlugin(async () => {
  const preferencesStore = usePreferencesStore()
  await preferencesStore.fetchFromApi()
})
