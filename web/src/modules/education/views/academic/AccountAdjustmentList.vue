<script setup lang="ts">
import type { AccountAdjustmentPageParams, AccountAdjustmentRecord, RollbackResult } from '../../api/academic/attendanceConsumption.ts'
import { pageAccountAdjustments } from '../../api/academic/attendanceConsumption.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import AccountAdjustmentForm from './components/AccountAdjustmentForm.vue'
import AccountAdjustmentRollbackDialog from './components/AccountAdjustmentRollbackDialog.vue'
import { adjustmentTypeLabel, canRollbackAdjustment, ledgerDirectionLabel, ledgerStatusLabel, markAdjustmentRollbackSuccess } from './attendanceConsumptionRules.ts'
import { useEducationScope } from '@/composables/education/useEducationScope.ts'

defineOptions({ name: 'EducationAcademicAccountAdjustmentList' })

const { scope } = useEducationScope()

const loading = ref(false)
const formVisible = ref(false)
const rollbackVisible = ref(false)
const current = ref<AccountAdjustmentRecord | null>(null)
const errorText = ref('')
const rows = ref<AccountAdjustmentRecord[]>([])
const total = ref(0)
const search = reactive<AccountAdjustmentPageParams>(defaultSearch())

const canCreate = computed(() => hasAuth('education:academic:account-adjustment:create'))
const canRollback = computed(() => hasAuth('education:academic:account-adjustment:rollback'))

function defaultSearch(): AccountAdjustmentPageParams {
  return { page: 1, page_size: 20, account_id: undefined, student_id: undefined, course_id: undefined, adjustment_type: undefined, status: undefined, keyword: '' }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageAccountAdjustments(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? '账户调整列表加载失败'
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

function openRollback(row: AccountAdjustmentRecord) {
  current.value = row
  rollbackVisible.value = true
}

function onCreated(row: AccountAdjustmentRecord) {
  rows.value = [row, ...rows.value]
  loadRows()
}

function onRollback(result: RollbackResult<AccountAdjustmentRecord>) {
  rows.value = markAdjustmentRollbackSuccess(rows.value, result.original)
  loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-academic-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>账户调整</span>
          <el-button v-if="canCreate" type="primary" @click="formVisible = true">补扣课时</el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="账户ID"><el-input-number v-model="search.account_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="学员ID"><el-input-number v-model="search.student_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="课程ID"><el-input-number v-model="search.course_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="类型">
          <el-select v-model="search.adjustment_type" clearable style="width: 180px;"><el-option label="补扣课时" value="supplement_deduction" /><el-option label="回滚" value="rollback" /></el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable style="width: 130px;"><el-option label="已确认" value="confirmed" /><el-option label="已回滚" value="rolled_back" /></el-select>
        </el-form-item>
        <el-form-item label="关键字"><el-input v-model="search.keyword" clearable /></el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">查询</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="adjustment_no" label="调整编号" width="180" />
        <el-table-column prop="student_name" label="学员" min-width="130" />
        <el-table-column prop="course_name" label="课程" min-width="140" />
        <el-table-column label="类型" width="170"><template #default="{ row }">{{ adjustmentTypeLabel(row.adjustment_type) }}</template></el-table-column>
        <el-table-column label="方向" width="110"><template #default="{ row }">{{ ledgerDirectionLabel(row.direction) }}</template></el-table-column>
        <el-table-column prop="units" label="课时" width="90" />
        <el-table-column prop="before_available_units" label="变更前" width="110" />
        <el-table-column prop="after_available_units" label="变更后" width="110" />
        <el-table-column label="状态" width="120"><template #default="{ row }">{{ ledgerStatusLabel(row.status) }}</template></el-table-column>
        <el-table-column prop="reason" label="原因" min-width="180" />
        <el-table-column prop="created_at" label="创建时间" width="170" />
        <el-table-column label="操作" fixed="right" width="140">
          <template #default="{ row }">
            <el-button v-if="canRollbackAdjustment(row, canRollback)" link type="warning" @click="openRollback(row)">回滚</el-button>
          </template>
        </el-table-column>
        <template #empty><el-empty description="暂无账户调整" /></template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <AccountAdjustmentForm v-model="formVisible" :tenant-id="scope.tenant_id" @success="onCreated" />
    <AccountAdjustmentRollbackDialog v-model="rollbackVisible" :row="current" :tenant-id="scope.tenant_id" @success="onRollback" />
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
