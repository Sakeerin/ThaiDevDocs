<template>
  <Teleport to="body">
    <TransitionRoot :show="modelValue" as="template">
      <Dialog as="div" class="relative z-50" @close="emit('update:modelValue', false)">
        <TransitionChild
          as="template"
          enter="ease-out duration-200"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-150"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto p-4">
          <div class="flex min-h-full items-center justify-center">
            <TransitionChild
              as="template"
              enter="ease-out duration-200"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="ease-in duration-150"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-2xl rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-xl p-6">
                <DialogTitle class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                  แนะนำการแก้ไข
                </DialogTitle>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                  บทความ: {{ articleTitle }}
                </p>

                <form class="space-y-4" @submit.prevent="submit">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      เนื้อหาเดิม
                    </label>
                    <textarea
                      v-model="form.original_content"
                      rows="4"
                      required
                      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl dark:text-white resize-none"
                      placeholder="คัดลอกส่วนที่ต้องการแก้ไข..."
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      เนื้อหาที่แนะนำ
                    </label>
                    <textarea
                      v-model="form.suggested_content"
                      rows="4"
                      required
                      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl dark:text-white resize-none"
                      placeholder="เขียนเนื้อหาที่แนะนำ..."
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      เหตุผล (ไม่บังคับ)
                    </label>
                    <input
                      v-model="form.reason"
                      type="text"
                      maxlength="500"
                      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl dark:text-white"
                      placeholder="อธิบายเหตุผลในการแก้ไข..."
                    />
                  </div>

                  <div v-if="errorMessage" class="text-sm text-red-600">
                    {{ errorMessage }}
                  </div>

                  <div class="flex justify-end gap-3 pt-2">
                    <button
                      type="button"
                      class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl"
                      @click="emit('update:modelValue', false)"
                    >
                      ยกเลิก
                    </button>
                    <button
                      type="submit"
                      :disabled="isSubmitting"
                      class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium disabled:opacity-50"
                    >
                      {{ isSubmitting ? 'กำลังส่ง...' : 'ส่งคำแนะนำ' }}
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </Teleport>
</template>

<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'

const props = defineProps<{
  modelValue: boolean
  articleSlug: string
  articleTitle: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  submitted: []
}>()

const { $api } = useNuxtApp()

const isSubmitting = ref(false)
const errorMessage = ref('')

const form = reactive({
  original_content: '',
  suggested_content: '',
  reason: '',
})

watch(() => props.modelValue, (open) => {
  if (!open) {
    form.original_content = ''
    form.suggested_content = ''
    form.reason = ''
    errorMessage.value = ''
  }
})

async function submit() {
  if (!form.original_content.trim() || !form.suggested_content.trim()) return

  isSubmitting.value = true
  errorMessage.value = ''

  try {
    await $api(`/articles/${props.articleSlug}/suggest-edit`, {
      method: 'POST',
      body: {
        original_content: form.original_content,
        suggested_content: form.suggested_content,
        reason: form.reason || undefined,
      },
    })
    emit('submitted')
    emit('update:modelValue', false)
  } catch (e: any) {
    errorMessage.value = e?.error?.message || e?.message || 'ไม่สามารถส่งคำแนะนำได้'
  } finally {
    isSubmitting.value = false
  }
}
</script>
