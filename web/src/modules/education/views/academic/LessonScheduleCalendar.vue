<script setup lang="ts">
import type { CalendarLessonParams, LessonRecord } from '../../api/academic/classSchedule.ts'
import { calendarLessons } from '../../api/academic/classSchedule.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import BatchLessonScheduleDrawer from './components/BatchLessonScheduleDrawer.vue'
import ScheduleConflictDrawer from './components/ScheduleConflictDrawer.vue'
import SingleLessonScheduleDrawer from './components/SingleLessonScheduleDrawer.vue'
import { calendarEventSummary } from './classScheduleRules.ts'

defineOptions({ name: 'EducationAcademicLessonScheduleCalendar' })

const loading = ref(false)
const errorText = ref('')
const rows = ref<LessonRecord[]>([])
const singleVisible = ref(false)
const batchVisible = ref(false)
const conflictVisible = ref(false)
const conflictResult = ref<Record<string, unknown> | null>(null)
const search = reactive<CalendarLessonParams>(defaultSearch())

const canSingle = computed(() => hasAuth('education:academic:lesson-schedule:create'))
const canBatch = computed(() => hasAuth('education:academic:lesson-schedule:batch'))

function defaultSearch(): CalendarLessonParams {
  return {
    tenant_id: undefined,
    campus_id: undefined,
    class_id: undefined,
    teacher_id: undefined,
    classroom_id: undefined,
    status: undefined,
    start_at: '',
    end_at: '',
  }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await calendarLessons(search)
    rows.value = response.data.list
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Schedule calendar loading failed'
  }
  finally {
    loading.value = false
  }
}

function handleReset() {
  Object.assign(search, defaultSearch())
  loadRows()
}

function openConflict(result: Record<string, unknown>) {
  conflictResult.value = result
  conflictVisible.value = true
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-academic-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Lesson Schedule</span>
          <div class="page-actions">
            <el-button v-if="canSingle" type="primary" @click="singleVisible = true">
              Single
            </el-button>
            <el-button v-if="canBatch" @click="batchVisible = true">
              Batch
            </el-button>
          </div>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="Tenant ID">
          <el-input-number v-model="search.tenant_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Campus ID">
          <el-input-number v-model="search.campus_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Class ID">
          <el-input-number v-model="search.class_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Teacher ID">
          <el-input-number v-model="search.teacher_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Classroom ID">
          <el-input-number v-model="search.classroom_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Status">
          <el-select v-model="search.status" clearable style="width: 130px;">
            <el-option label="Scheduled" value="scheduled" />
            <el-option label="Cancelled" value="cancelled" />
            <el-option label="Completed" value="completed" />
          </el-select>
        </el-form-item>
        <el-form-item label="Range">
          <el-date-picker v-model="search.start_at" value-format="YYYY-MM-DD HH:mm:ss" type="datetime" placeholder="Start" />
          <el-date-picker v-model="search.end_at" value-format="YYYY-MM-DD HH:mm:ss" type="datetime" placeholder="End" class="ml-2" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadRows">
            Search
          </el-button>
          <el-button @click="handleReset">
            Reset
          </el-button>
        </el-form-item>
      </el-form>
      <el-skeleton v-if="loading" :rows="6" animated />
      <div v-else class="calendar-list">
        <el-empty v-if="rows.length === 0" description="No lessons in selected range" />
        <div v-for="lesson in rows" v-else :key="lesson.id" class="calendar-row">
          <div>
            <strong>{{ lesson.title }}</strong>
            <span>{{ calendarEventSummary(lesson) }}</span>
          </div>
          <div class="calendar-time">
            {{ lesson.start_at }} - {{ lesson.end_at }}
          </div>
          <el-tag size="small" :type="lesson.status === 'cancelled' ? 'danger' : 'success'">
            {{ lesson.status }}
          </el-tag>
        </div>
      </div>
    </el-card>
    <SingleLessonScheduleDrawer v-model="singleVisible" :tenant-id="search.tenant_id" :campus-id="search.campus_id" :class-id="search.class_id" @success="loadRows" @conflict="openConflict" />
    <BatchLessonScheduleDrawer v-model="batchVisible" :tenant-id="search.tenant_id" :campus-id="search.campus_id" :class-id="search.class_id" @success="loadRows" @conflict="openConflict" />
    <ScheduleConflictDrawer v-model="conflictVisible" :result="conflictResult" />
  </div>
</template>

<style scoped lang="scss">
.education-academic-page {
  .page-header,
  .page-actions,
  .calendar-row {
    display: flex;
    align-items: center;
  }

  .page-header {
    justify-content: space-between;
  }

  .page-actions {
    gap: 8px;
  }

  .page-alert,
  .search-form {
    margin-bottom: 12px;
  }

  .calendar-list {
    display: grid;
    gap: 8px;
  }

  .calendar-row {
    justify-content: space-between;
    min-height: 56px;
    padding: 10px 12px;
    border: 1px solid var(--el-border-color-lighter);
    border-radius: 6px;

    span {
      display: block;
      margin-top: 4px;
      color: var(--el-text-color-secondary);
    }
  }

  .calendar-time {
    color: var(--el-text-color-regular);
    white-space: nowrap;
  }
}
</style>
