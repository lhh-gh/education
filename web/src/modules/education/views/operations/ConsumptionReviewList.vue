<script setup lang="ts">
import type { ConsumptionReviewPageParams, ConsumptionReviewRecord } from '../../api/operations/consumption-review.ts'
import { createConsumptionAdjustment, pageConsumptionReviews } from '../../api/operations/consumption-review.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import ConsumptionReviewDrawer from './components/ConsumptionReviewDrawer.vue'
import { conflictErrorText, operationPermissions, operationStatusLabel, operationTagType } from './operationRules.ts'

defineOptions({ name: 'EducationOperationConsumptionReviewList' })

const loading = ref(false)
const drawerVisible = ref(false)
const action = ref<'approve' | 'reject'>('approve')
const current = ref<ConsumptionReviewRecord | null>(null)
const rows = ref<ConsumptionReviewRecord[]>([])
const total = ref(0)
const errorText = ref('')
const search = reactive<ConsumptionReviewPageParams>({ page: 1, pageSize: 20, campus_id: undefined, teacher_id: undefined, lesson_id: undefined, status: undefined, start_at: undefined, end_at: undefined })
const permissions = computed(() => operationPermissions(hasAuth))

async function loadRows() {
  loading.value = true
  try {
    const response = await pageConsumptionReviews(search)
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

function open(row: ConsumptionReviewRecord, next: 'approve' | 'reject') {
  current.value = row
  action.value = next
  drawerVisible.value = true
}

async function adjust(row: ConsumptionReviewRecord) {
  const originalId = row.consumption_ids?.[0]
  if (!originalId) {
    errorText.value = '请选择原消课记录'
    return
  }
  try {
    await createConsumptionAdjustment(originalId, { tenant_id: row.tenant_id, campus_id: row.campus_id ?? undefined, credits: '-1.00', reason: '考勤状态有误' })
    loadRows()
  }
  catch (error: any) {
    errorText.value = conflictErrorText(error)
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-operation-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>消课审核</span>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="校区">
          <el-input-number v-model="search.campus_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="教师">
          <el-input-number v-model="search.teacher_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable style="width: 150px;">
            <el-option label="待处理" value="pending" />
            <el-option label="已通过" value="approved" />
            <el-option label="已驳回" value="rejected" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadRows">
            查询
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="lesson_id" label="课次" width="110" />
        <el-table-column prop="teacher_id" label="教师" width="110" />
        <el-table-column prop="submitted_at" label="提交时间" width="180" />
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="operationTagType(row.status)">
              {{ operationStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="reviewed_by" label="审核人" width="130" />
        <el-table-column prop="review_note" label="审核备注" min-width="180" show-overflow-tooltip />
        <el-table-column label="操作" fixed="right" width="220">
          <template #default="{ row }">
            <el-button v-if="permissions.approveConsumption" link type="primary" @click="open(row, 'approve')">
              通过
            </el-button>
            <el-button v-if="permissions.approveConsumption" link type="danger" @click="open(row, 'reject')">
              驳回
            </el-button>
            <el-button v-if="permissions.adjustConsumption" link @click="adjust(row)">
              调整
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无消课审核记录" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <ConsumptionReviewDrawer v-model="drawerVisible" :row="current" :action="action" :error="errorText" @success="loadRows" />
  </div>
</template>

<style scoped lang="scss">
.education-operation-page {
  .page-header { font-weight: 600; }

  .page-alert,
  .search-form { margin-bottom: 12px; }
  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>
