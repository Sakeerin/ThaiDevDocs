<template>
  <div :class="['group', depth > 0 ? 'ml-12 pl-4 border-l-2 border-gray-100 dark:border-slate-700' : '']">
    <div class="flex items-start gap-4">
      <!-- Avatar -->
      <div
        class="w-10 h-10 rounded-full flex items-center justify-center text-white font-medium shrink-0"
        :class="getAvatarColor(comment.user?.name || 'A')"
      >
        {{ comment.user?.name?.charAt(0).toUpperCase() || 'A' }}
      </div>

      <div class="flex-1 min-w-0">
        <!-- Header -->
        <div class="flex items-center gap-2 flex-wrap">
          <span class="font-medium text-gray-900 dark:text-white">
            {{ comment.user?.name || 'ผู้ใช้' }}
          </span>
          <span class="text-sm text-gray-500">
            {{ formatTimeAgo(comment.created_at) }}
          </span>
          <span v-if="comment.updated_at !== comment.created_at" class="text-sm text-gray-400 italic">
            (แก้ไขแล้ว)
          </span>
        </div>

        <!-- Content -->
        <div class="mt-2 prose prose-sm dark:prose-invert max-w-none">
          <div v-if="isEditing">
            <textarea
              v-model="editContent"
              rows="3"
              class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:text-white resize-none"
            />
            <div class="mt-2 flex gap-2">
              <button
                type="button"
                class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg disabled:opacity-50"
                :disabled="isSaving"
                @click="saveEdit"
              >
                {{ isSaving ? 'กำลังบันทึก...' : 'บันทึก' }}
              </button>
              <button
                type="button"
                class="px-3 py-1.5 text-gray-600 hover:bg-gray-100 dark:hover:bg-slate-700 text-sm rounded-lg"
                @click="cancelEdit"
              >
                ยกเลิก
              </button>
            </div>
          </div>
          <p v-else class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
            {{ comment.content }}
          </p>
        </div>

        <!-- Actions -->
        <div class="mt-3 flex items-center gap-4">
          <!-- Votes -->
          <div class="flex items-center gap-1">
            <button
              type="button"
              class="p-1.5 rounded-lg transition-colors text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20"
              @click="$emit('vote', comment.id, 'up')"
            >
              <Icon name="heroicons:chevron-up" class="w-5 h-5" />
            </button>
            <span
              class="text-sm font-medium"
              :class="comment.upvote_count > 0 ? 'text-emerald-600' : 'text-gray-500'"
            >
              {{ comment.upvote_count }}
            </span>
            <button
              type="button"
              class="p-1.5 rounded-lg transition-colors text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
              @click="$emit('vote', comment.id, 'down')"
            >
              <Icon name="heroicons:chevron-down" class="w-5 h-5" />
            </button>
          </div>

          <!-- Reply -->
          <button
            v-if="depth < 3"
            type="button"
            class="flex items-center gap-1 text-sm text-gray-500 hover:text-primary-600"
            @click="showReplyForm = !showReplyForm"
          >
            <Icon name="heroicons:chat-bubble-left" class="w-4 h-4" />
            ตอบกลับ
          </button>

          <!-- More Actions -->
          <div v-if="comment.is_author" class="relative ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
            <button
              type="button"
              class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg"
              @click="showMenu = !showMenu"
            >
              <Icon name="heroicons:ellipsis-horizontal" class="w-5 h-5" />
            </button>

            <div
              v-if="showMenu"
              class="absolute right-0 top-full mt-1 w-40 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-lg z-10"
            >
              <button
                type="button"
                class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 first:rounded-t-xl"
                @click="startEdit"
              >
                <Icon name="heroicons:pencil" class="w-4 h-4 inline-block mr-2" />
                แก้ไข
              </button>
              <button
                type="button"
                class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 last:rounded-b-xl"
                @click="$emit('delete', comment.id)"
              >
                <Icon name="heroicons:trash" class="w-4 h-4 inline-block mr-2" />
                ลบ
              </button>
            </div>
          </div>
        </div>

        <!-- Reply Form -->
        <div v-if="showReplyForm" class="mt-4">
          <textarea
            v-model="replyContent"
            rows="2"
            class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:text-white resize-none"
            :placeholder="`ตอบกลับ @${comment.user?.name || 'ผู้ใช้'}...`"
          />
          <div class="mt-2 flex justify-end gap-2">
            <button
              type="button"
              class="px-3 py-1.5 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700 text-sm rounded-lg"
              @click="showReplyForm = false"
            >
              ยกเลิก
            </button>
            <button
              type="button"
              :disabled="!replyContent.trim()"
              class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg disabled:opacity-50"
              @click="submitReply"
            >
              ตอบกลับ
            </button>
          </div>
        </div>

        <!-- Replies -->
        <div v-if="comment.replies?.length" class="mt-4 space-y-4">
          <CommentItem
            v-for="reply in comment.replies"
            :key="reply.id"
            :comment="reply"
            :current-user="currentUser"
            :depth="depth + 1"
            @reply="(parentId, content) => $emit('reply', parentId, content)"
            @vote="(commentId, type) => $emit('vote', commentId, type)"
            @delete="(commentId) => $emit('delete', commentId)"
            @updated="(updated) => $emit('updated', updated)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Comment, User } from '~/types'

const props = withDefaults(defineProps<{
  comment: Comment
  currentUser: User | null
  depth?: number
}>(), {
  depth: 0,
})

const emit = defineEmits<{
  reply: [parentId: number, content: string]
  vote: [commentId: number, type: 'up' | 'down']
  delete: [commentId: number]
  updated: [comment: Comment]
}>()

const { $api } = useNuxtApp()

const showReplyForm = ref(false)
const replyContent = ref('')
const showMenu = ref(false)
const isEditing = ref(false)
const isSaving = ref(false)
const editContent = ref('')

function submitReply() {
  if (!replyContent.value.trim()) return
  emit('reply', props.comment.id, replyContent.value)
  replyContent.value = ''
  showReplyForm.value = false
}

function startEdit() {
  editContent.value = props.comment.content
  isEditing.value = true
  showMenu.value = false
}

function cancelEdit() {
  isEditing.value = false
  editContent.value = ''
}

async function saveEdit() {
  if (!editContent.value.trim()) return

  isSaving.value = true
  try {
    const response = await $api<{ data: { comment: Comment } }>(`/comments/${props.comment.id}`, {
      method: 'PATCH',
      body: { content: editContent.value },
    })
    emit('updated', response.data.comment)
    isEditing.value = false
  } catch (e: any) {
    alert(e?.error?.message || e?.message || 'ไม่สามารถแก้ไขความคิดเห็นได้')
  } finally {
    isSaving.value = false
  }
}

function getAvatarColor(name: string) {
  const colors = [
    'bg-gradient-to-br from-red-400 to-red-600',
    'bg-gradient-to-br from-orange-400 to-orange-600',
    'bg-gradient-to-br from-amber-400 to-amber-600',
    'bg-gradient-to-br from-emerald-400 to-emerald-600',
    'bg-gradient-to-br from-cyan-400 to-cyan-600',
    'bg-gradient-to-br from-blue-400 to-blue-600',
    'bg-gradient-to-br from-violet-400 to-violet-600',
    'bg-gradient-to-br from-pink-400 to-pink-600',
  ]
  const index = name.charCodeAt(0) % colors.length
  return colors[index]
}

function formatTimeAgo(date: string) {
  const now = new Date()
  const then = new Date(date)
  const seconds = Math.floor((now.getTime() - then.getTime()) / 1000)

  if (seconds < 60) return 'เมื่อสักครู่'
  if (seconds < 3600) return `${Math.floor(seconds / 60)} นาทีที่แล้ว`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)} ชั่วโมงที่แล้ว`
  if (seconds < 604800) return `${Math.floor(seconds / 86400)} วันที่แล้ว`

  return then.toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

onMounted(() => {
  document.addEventListener('click', (e) => {
    if (!(e.target as HTMLElement).closest('.relative')) {
      showMenu.value = false
    }
  })
})
</script>
