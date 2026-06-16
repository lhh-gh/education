<script setup lang="ts">
import type { LessonChangePageParams, LessonChangeRecord, MakeupLessonResult, RescheduleLessonResult } from '../../api/academic/lessonChange.ts'
import { pageLessonChanges } from '../../api/academic/lessonChange.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import LessonChangeDetailDrawer from './components/LessonChangeDetailDrawer.vue'
import MakeupLessonForm from './components/MakeupLessonForm.vue'
import RescheduleLessonForm from './components/RescheduleLessonForm.vue'
import { canRescheduleLesson, lessonChangeStatusType, lessonChangeTypeLabel } from './leaveMakeupRescheduleRules.ts'

defineOptions({ name: 'EducationAcademicLessonChangeList' })

const loading = ref(false)
const makeupVisible = ref(false)
const rescheduleVisible = ref(false)
const detailVisible = ref(false)
const current = ref<LessonChangeRecord | null>(null)
const rows = ref<LessonChangeRecord[]>([])
const total = ref(0)
const errorText = ref('')
const successText = ref('')
const search = reactive<LessonChangePageParams>(defaultSearch())

const canMakeup = computed(() => hasAuth('education:academic:lesson-change:makeup'))
const canReschedule = computed(() => canRescheduleLesson(hasAuth('education:academic:lesson-change:reschedule')))
const canDetail = computed(() => hasAuth('education:academic:lesson-change:detail'))

function defaultSearch(): LessonChangePageParams {
  return { page: 1, pageSize: 20, tenant_id: undefined, campus_id: undefined, change_type: undefined, status: undefined, source_lesson_id: undefined, target_lesson_id: undefined, student_id: undefined, keyword: '' }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageLessonChanges(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Lesson change list loading failed'
  }
  finally {
    loading.value = false
  }
}

function handleSearch() {
  search.page = 1
  loadRows()
}

function handleReset() {
  Object.assign(search, defaultSearch())
  loadRows()
}

function openDetail(row: LessonChangeRecord) {
  current.value = row
  detailVisible.value = true
}

function onMakeupSuccess(result: MakeupLessonResult) {
  successText.value = `Make-up lesson ${result.target_lesson.id}, ${result.change_record.change_no}`
  loadRows()
}

function onRescheduleSuccess(result: RescheduleLessonResult) {
  successText.value = `Rescheduled ${result.lesson.id}`
  loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-academic-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Lesson Changes</span>
          <div>
            <el-button v-if="canMakeup" type="primary" @click="makeupVisible = true">
              Make-up
            </el-button>
            <el-button v-if="canReschedule" @click="rescheduleVisible = true">
              Reschedule
            </el-button>
          </div>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="true" :title="successText" @close="successText = ''" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="Tenant ID">
          <el-input-number v-model="search.tenant_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Campus ID">
          <el-input-number v-model="search.campus_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Type">
          <el-select v-model="search.change_type" clearable style="width: 150px;">
            <el-option label="Make-up" value="makeup" />
            <el-option label="Reschedule" value="reschedule" />
          </el-select>
        </el-form-item>
        <el-form-item label="Status">
          <el-select v-model="search.status" clearable style="width: 150px;">
            <el-option label="Confirmed" value="confirmed" />
            <el-option label="Cancelled" value="cancelled" />
          </el-select>
        </el-form-item>
        <el-form-item label="Source Lesson">
          <el-input-number v-model="search.source_lesson_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Keyword">
          <el-input v-model="search.keyword" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            Search
          </el-button>
          <el-button @click="handleReset">
            Reset
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="change_no" label="Change No" width="190" />
        <el-table-column label="Type" width="120">
          <template #default="{ row }">
            {{ lessonChangeTypeLabel(row.change_type) }}
          </template>
        </el-table-column>
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="lessonChangeStatusType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="source_lesson_id" label="Source Lesson" width="130" />
        <el-table-column prop="target_lesson_id" label="Target Lesson" width="130" />
        <el-table-column prop="student_id" label="Student ID" width="110" />
        <el-table-column prop="source_start_at" label="Source Start" width="180" />
        <el-table-column prop="target_start_at" label="Target Start" width="180" />
        <el-table-column prop="lesson_units" label="Units" width="90" />
        <el-table-column prop="reason" label="Reason" min-width="180" show-overflow-tooltip />
        <el-table-column label="Actions" fixed="right" width="120">
          <template #default="{ row }">
            <el-button v-if="canDetail" link type="primary" @click="openDetail(row)">
              Detail
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No lesson changes" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <MakeupLessonForm v-model="makeupVisible" :tenant-id="search.tenant_id" @success="onMakeupSuccess" />
    <RescheduleLessonForm v-model="rescheduleVisible" :tenant-id="search.tenant_id" @success="onRescheduleSuccess" />
    <LessonChangeDetailDrawer v-model="detailVisible" :row="current" />
  </div>
</template>

<style scoped lang="scss">
.education-academic-page {
  .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .page-alert,
  .search-form {
    margin-bottom: 12px;
  }

  .page-pagination {
    justify-content: flex-end;
    margin-top: 16px;
  }
}
</style>
