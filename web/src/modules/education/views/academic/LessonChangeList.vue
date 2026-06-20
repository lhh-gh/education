<script setup lang="ts">
import type { LessonChangePageParams, LessonChangeRecord, MakeupLessonResult, RescheduleLessonResult } from '../../api/academic/lessonChange.ts'
import { pageLessonChanges } from '../../api/academic/lessonChange.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import LessonChangeDetailDrawer from './components/LessonChangeDetailDrawer.vue'
import MakeupLessonForm from './components/MakeupLessonForm.vue'
import RescheduleLessonForm from './components/RescheduleLessonForm.vue'
import { canRescheduleLesson, lessonChangeStatusLabel, lessonChangeStatusType, lessonChangeTypeLabel } from './leaveMakeupRescheduleRules.ts'
import { useEducationScope } from '@/composables/education/useEducationScope.ts'

defineOptions({ name: 'EducationAcademicLessonChangeList' })

const { scope } = useEducationScope()

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
  return { page: 1, pageSize: 20, change_type: undefined, status: undefined, source_lesson_id: undefined, target_lesson_id: undefined, student_id: undefined, keyword: '' }
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
    errorText.value = error?.message ?? '调补课列表加载失败'
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
  successText.value = `补课课次 ${result.target_lesson.id}，${result.change_record.change_no}`
  loadRows()
}

function onRescheduleSuccess(result: RescheduleLessonResult) {
  successText.value = `已调课 ${result.lesson.id}`
  loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-academic-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>调补课管理</span>
          <div>
            <el-button v-if="canMakeup" type="primary" @click="makeupVisible = true">
              补课
            </el-button>
            <el-button v-if="canReschedule" @click="rescheduleVisible = true">
              调课
            </el-button>
          </div>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="true" :title="successText" @close="successText = ''" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="类型">
          <el-select v-model="search.change_type" clearable style="width: 150px;">
            <el-option label="补课" value="makeup" />
            <el-option label="调课" value="reschedule" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable style="width: 150px;">
            <el-option label="已确认" value="confirmed" />
            <el-option label="已取消" value="cancelled" />
          </el-select>
        </el-form-item>
        <el-form-item label="原课次">
          <el-input-number v-model="search.source_lesson_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="关键字">
          <el-input v-model="search.keyword" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            查询
          </el-button>
          <el-button @click="handleReset">
            重置
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="change_no" label="变更编号" width="190" />
        <el-table-column label="类型" width="120">
          <template #default="{ row }">
            {{ lessonChangeTypeLabel(row.change_type) }}
          </template>
        </el-table-column>
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="lessonChangeStatusType(row.status)">
              {{ lessonChangeStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="source_lesson_id" label="原课次" width="130" />
        <el-table-column prop="target_lesson_id" label="目标课次" width="130" />
        <el-table-column prop="student_id" label="学员ID" width="110" />
        <el-table-column prop="source_start_at" label="原开始时间" width="180" />
        <el-table-column prop="target_start_at" label="目标开始时间" width="180" />
        <el-table-column prop="lesson_units" label="课时" width="90" />
        <el-table-column prop="reason" label="原因" min-width="180" show-overflow-tooltip />
        <el-table-column label="操作" fixed="right" width="120">
          <template #default="{ row }">
            <el-button v-if="canDetail" link type="primary" @click="openDetail(row)">
              详情
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无调补课记录" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <MakeupLessonForm v-model="makeupVisible" :tenant-id="scope.tenant_id" @success="onMakeupSuccess" />
    <RescheduleLessonForm v-model="rescheduleVisible" :tenant-id="scope.tenant_id" @success="onRescheduleSuccess" />
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
