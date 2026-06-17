<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { reactive } from 'vue'
import type { TeacherCommentTemplate, TeacherPerformanceTag } from '@/api/family/teacher'
import { createTeacherLessonComment, getTeacherCommentTemplates, getTeacherPerformanceTags } from '@/api/family/teacher'
import TeacherStateBlock from '@/pages/teacher/components/TeacherStateBlock.vue'

const state = reactive({
  status: 'loading' as 'loading' | 'success' | 'empty' | 'error' | 'forbidden',
  message: '',
  lessonId: 0,
  studentId: 0,
  content: '',
  publish: true,
  templateIndex: -1,
  templates: [] as TeacherCommentTemplate[],
  tags: [] as TeacherPerformanceTag[],
  selectedTagIds: [] as number[],
  submitted: false,
})

onLoad((query = {}) => {
  state.lessonId = Number((query as Record<string, string>).lesson_id || 0)
  state.studentId = Number((query as Record<string, string>).student_id || 0)
  loadOptions()
})

async function loadOptions(): Promise<void> {
  if (state.lessonId <= 0 || state.studentId <= 0) {
    state.status = 'empty'
    state.message = 'Assigned lesson and student are required'
    return
  }
  state.status = 'loading'
  try {
    const [templates, tags] = await Promise.all([
      getTeacherCommentTemplates(),
      getTeacherPerformanceTags(),
    ])
    state.templates = templates.list
    state.tags = tags.list
    state.status = 'success'
  }
  catch (error) {
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

function applyTemplate(index: number): void {
  state.templateIndex = index
  state.content = state.templates[index]?.content || state.content
}

function toggleTag(tagId: number): void {
  state.selectedTagIds = state.selectedTagIds.includes(tagId)
    ? state.selectedTagIds.filter(id => id !== tagId)
    : [...state.selectedTagIds, tagId]
}

function setPublish(event: Event): void {
  state.publish = Boolean((event as { detail?: { value?: boolean } }).detail?.value)
}

async function submit(): Promise<void> {
  try {
    await createTeacherLessonComment({
      lesson_id: state.lessonId,
      student_id: state.studentId,
      content: state.content,
      tag_ids: state.selectedTagIds,
      publish: state.publish,
    })
    state.submitted = true
    state.message = state.publish ? 'Comment published' : 'Draft saved'
  }
  catch (error) {
    state.status = isForbidden(error) ? 'forbidden' : 'error'
    state.message = errorMessage(error)
  }
}

function isForbidden(error: unknown): boolean {
  const code = (error as { code?: number })?.code

  return code === 401 || code === 403
}

function errorMessage(error: unknown): string {
  return (error as { message?: string })?.message || 'Request failed'
}
</script>

<template>
  <view class="page">
    <TeacherStateBlock v-if="state.status === 'loading'" status="loading" message="Loading templates" />
    <TeacherStateBlock v-else-if="state.status === 'empty'" status="empty" :message="state.message" />
    <TeacherStateBlock v-else-if="state.status === 'forbidden'" status="forbidden" :message="state.message" @retry="loadOptions" />
    <TeacherStateBlock v-else-if="state.status === 'error'" status="error" :message="state.message" @retry="loadOptions" />
    <view v-else class="form">
      <text class="title">Student {{ state.studentId }}</text>
      <view class="chips">
        <button v-for="(template, index) in state.templates" :key="template.id" class="chip" @tap="applyTemplate(index)">
          {{ template.template_name }}
        </button>
      </view>
      <view class="chips">
        <button v-for="tag in state.tags" :key="tag.id" class="chip" :class="{ active: state.selectedTagIds.includes(tag.id) }" @tap="toggleTag(tag.id)">
          {{ tag.tag_name }}
        </button>
      </view>
      <textarea v-model="state.content" class="textarea" placeholder="Comment content" />
      <label class="toggle"><switch :checked="state.publish" @change="setPublish" />Publish now</label>
      <button class="primary" :disabled="state.submitted || !state.content" @tap="submit">{{ state.submitted ? 'Submitted' : 'Submit' }}</button>
      <text v-if="state.message" class="message">{{ state.message }}</text>
    </view>
  </view>
</template>

<style scoped>
.page { min-height: 100vh; padding: 24rpx; background: #f6f8fb; color: #172033; }
.form { display: flex; flex-direction: column; gap: 18rpx; padding: 24rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; background: #ffffff; }
.title { font-size: 30rpx; font-weight: 700; }
.chips { display: flex; flex-wrap: wrap; gap: 12rpx; }
.chip { min-width: 132rpx; border-radius: 8rpx; background: #edf1f6; color: #172033; }
.chip.active { background: #2456a6; color: #ffffff; }
.textarea { min-height: 220rpx; padding: 18rpx; border: 1rpx solid #dce4ef; border-radius: 8rpx; }
.toggle { display: flex; align-items: center; gap: 12rpx; color: #5f6f86; }
.primary { background: #2456a6; color: #ffffff; }
.message { color: #17623a; }
</style>
