<script setup lang="ts">
import type { ConsumptionPageParams, ConsumptionRecord, RollbackResult } from '../../api/academic/attendanceConsumption.ts'
import { pageConsumptions } from '../../api/academic/attendanceConsumption.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import ConsumptionRollbackDialog from './components/ConsumptionRollbackDialog.vue'
import { canRollbackConsumption, consumptionSourceLabel, ledgerDirectionLabel, ledgerStatusLabel, markConsumptionRollbackSuccess } from './attendanceConsumptionRules.ts'

defineOptions({ name: 'EducationAcademicConsumptionLedgerList' })

const loading = ref(false)
const rollbackVisible = ref(false)
const current = ref<ConsumptionRecord | null>(null)
const errorText = ref('')
const rows = ref<ConsumptionRecord[]>([])
const total = ref(0)
const search = reactive<ConsumptionPageParams>(defaultSearch())

const canRollback = computed(() => hasAuth('education:academic:consumption:rollback'))

function defaultSearch(): ConsumptionPageParams {
  return { page: 1, page_size: 20, tenant_id: undefined, campus_id: undefined, account_id: undefined, student_id: undefined, course_id: undefined, lesson_id: undefined, source_type: undefined, status: undefined, keyword: '' }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageConsumptions(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? '课消流水加载失败'
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

function openRollback(row: ConsumptionRecord) {
  current.value = row
  rollbackVisible.value = true
}

function onRollback(result: RollbackResult<ConsumptionRecord>) {
  rows.value = markConsumptionRollbackSuccess(rows.value, result.original)
  loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-academic-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>课消流水</span>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="机构ID"><el-input-number v-model="search.tenant_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="校区ID"><el-input-number v-model="search.campus_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="账户ID"><el-input-number v-model="search.account_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="学员ID"><el-input-number v-model="search.student_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="课程ID"><el-input-number v-model="search.course_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="课次ID"><el-input-number v-model="search.lesson_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="来源">
          <el-select v-model="search.source_type" clearable style="width: 130px;"><el-option label="考勤消课" value="attendance" /><el-option label="回滚" value="rollback" /></el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable style="width: 120px;"><el-option label="有效" value="active" /><el-option label="已回滚" value="reversed" /></el-select>
        </el-form-item>
        <el-form-item label="关键字"><el-input v-model="search.keyword" clearable /></el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">查询</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="consumption_no" label="流水编号" width="180" />
        <el-table-column prop="student_name" label="学员" min-width="130" />
        <el-table-column prop="course_name" label="课程" min-width="140" />
        <el-table-column prop="lesson_no" label="课次" width="150" />
        <el-table-column label="来源" width="110"><template #default="{ row }">{{ consumptionSourceLabel(row.source_type) }}</template></el-table-column>
        <el-table-column label="方向" width="110"><template #default="{ row }">{{ ledgerDirectionLabel(row.direction) }}</template></el-table-column>
        <el-table-column prop="units" label="课时" width="90" />
        <el-table-column prop="before_available_units" label="变更前" width="110" />
        <el-table-column prop="after_available_units" label="变更后" width="110" />
        <el-table-column label="状态" width="100"><template #default="{ row }">{{ ledgerStatusLabel(row.status) }}</template></el-table-column>
        <el-table-column prop="created_at" label="创建时间" width="170" />
        <el-table-column label="操作" fixed="right" width="140">
          <template #default="{ row }">
            <el-button v-if="canRollbackConsumption(row, canRollback)" link type="warning" @click="openRollback(row)">回滚</el-button>
          </template>
        </el-table-column>
        <template #empty><el-empty description="暂无课消流水" /></template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <ConsumptionRollbackDialog v-model="rollbackVisible" :row="current" :tenant-id="search.tenant_id" @success="onRollback" />
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
