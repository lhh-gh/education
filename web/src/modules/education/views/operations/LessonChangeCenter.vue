<script setup lang="ts">
import type { LessonChangePageParams, LessonChangeRequestRecord } from '../../api/operations/lesson-change.ts'
import { batchChangeLessons, pageLessonChangeRequests } from '../../api/operations/lesson-change.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import LessonChangeForm from './components/LessonChangeForm.vue'
import LessonChangeReviewDrawer from './components/LessonChangeReviewDrawer.vue'
import { conflictErrorText, operationPermissions, operationStatusLabel, operationTagType, operationTypeLabel } from './operationRules.ts'
import { useEducationScope } from '@/composables/education/useEducationScope.ts'

defineOptions({ name: 'EducationOperationLessonChangeCenter' })

const { scope } = useEducationScope()

const loading = ref(false)
const formVisible = ref(false)
const reviewVisible = ref(false)
const reviewAction = ref<'approve' | 'reject' | 'apply'>('approve')
const current = ref<LessonChangeRequestRecord | null>(null)
const rows = ref<LessonChangeRequestRecord[]>([])
const total = ref(0)
const errorText = ref('')
const successText = ref('')
const conflictText = ref('')
const selectedIds = ref<number[]>([])
const search = reactive<LessonChangePageParams>({ page: 1, pageSize: 20, teacher_id: undefined, status: undefined, change_type: undefined, keyword: '' })
const permissions = computed(() => operationPermissions(hasAuth))

async function loadRows() {
  loading.value = true
  try {
    const response = await pageLessonChangeRequests(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = conflictErrorText(error)
  }
  finally {
    loading.value = false
  }
}

function openReview(row: LessonChangeRequestRecord, action: 'approve' | 'reject' | 'apply') {
  current.value = row
  reviewAction.value = action
  reviewVisible.value = true
}

async function batchCancel() {
  if (selectedIds.value.length === 0) {
    return
  }
  try {
    await batchChangeLessons({ tenant_id: scope.tenant_id, campus_id: scope.campus_id, lesson_ids: selectedIds.value, change_type: 'cancel', reason: 'batch cancel' })
    successText.value = '批量调课已提交'
    loadRows()
  }
  catch (error: any) {
    conflictText.value = conflictErrorText(error)
  }
}

function handleSelection(rows: LessonChangeRequestRecord[]) {
  selectedIds.value = rows.map(row => row.lesson_id)
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-operation-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>调课中心</span>
          <div>
            <el-button v-if="permissions.batchLessonChange" @click="batchCancel">
              批量取消
            </el-button>
            <el-button type="primary" @click="formVisible = true">
              新建调课
            </el-button>
          </div>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="true" :title="successText" @close="successText = ''" />
      <el-alert v-if="conflictText" class="page-alert" type="error" show-icon :closable="true" :title="conflictText" @close="conflictText = ''" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="教师">
          <el-input-number v-model="search.teacher_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable style="width: 150px;">
            <el-option v-for="item in ['pending', 'approved', 'rejected', 'applied', 'cancelled']" :key="item" :label="operationStatusLabel(item)" :value="item" />
          </el-select>
        </el-form-item>
        <el-form-item label="类型">
          <el-select v-model="search.change_type" clearable style="width: 180px;">
            <el-option v-for="item in ['reschedule', 'suspend', 'cancel', 'replace_teacher', 'replace_classroom', 'substitute_teacher']" :key="item" :label="operationTypeLabel(item)" :value="item" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadRows">
            查询
          </el-button>
        </el-form-item>
      </el-form>
      <el-skeleton v-if="loading" :rows="5" animated />
      <el-table v-else :data="rows" row-key="id" @selection-change="handleSelection">
        <el-table-column type="selection" width="48" />
        <el-table-column prop="lesson_id" label="课次" width="110" />
        <el-table-column label="类型" width="160">
          <template #default="{ row }">
            {{ operationTypeLabel(row.change_type) }}
          </template>
        </el-table-column>
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="operationTagType(row.status)">
              {{ operationStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="requested_by" label="申请人" width="130" />
        <el-table-column prop="approved_at" label="审批时间" width="180" />
        <el-table-column prop="reason" label="原因" min-width="180" show-overflow-tooltip />
        <el-table-column label="操作" fixed="right" width="220">
          <template #default="{ row }">
            <el-button link type="primary" @click="openReview(row, 'approve')">
              通过
            </el-button>
            <el-button link type="danger" @click="openReview(row, 'reject')">
              驳回
            </el-button>
            <el-button link @click="openReview(row, 'apply')">
              生效
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无调课记录" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <LessonChangeForm v-model="formVisible" :tenant-id="scope.tenant_id" :campus-id="scope.campus_id" @success="loadRows" @conflict="conflictText = $event" />
    <LessonChangeReviewDrawer v-model="reviewVisible" :row="current" :action="reviewAction" :error="conflictText" @success="loadRows" />
  </div>
</template>

<style scoped lang="scss">
.education-operation-page {
  .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 600;
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
