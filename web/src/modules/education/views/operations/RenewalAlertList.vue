<script setup lang="ts">
import type { RenewalAlertPageParams, RenewalAlertRecord } from '../../api/operations/renewal.ts'
import { closeRenewalAlert, ignoreRenewalAlert, pageRenewalAlerts } from '../../api/operations/renewal.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import RenewalFollowDrawer from './components/RenewalFollowDrawer.vue'
import { conflictErrorText, operationPermissions, operationStatusLabel, operationTagType, operationTypeLabel, sortRenewalAlerts } from './operationRules.ts'

defineOptions({ name: 'EducationOperationRenewalAlertList' })

const loading = ref(false)
const drawerVisible = ref(false)
const current = ref<RenewalAlertRecord | null>(null)
const rows = ref<RenewalAlertRecord[]>([])
const total = ref(0)
const errorText = ref('')
const search = reactive<RenewalAlertPageParams>({ page: 1, pageSize: 20, alert_type: undefined, alert_level: undefined, status: 'open', assignee_id: undefined })
const permissions = computed(() => operationPermissions(hasAuth))
const sortedRows = computed(() => sortRenewalAlerts(rows.value))

async function loadRows() {
  loading.value = true
  try {
    const response = await pageRenewalAlerts(search)
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

function openFollow(row: RenewalAlertRecord) {
  current.value = row
  drawerVisible.value = true
}

async function ignore(row: RenewalAlertRecord) {
  await ignoreRenewalAlert(row.id, { tenant_id: row.tenant_id, campus_id: row.campus_id ?? undefined })
  loadRows()
}

async function close(row: RenewalAlertRecord) {
  await closeRenewalAlert(row.id, { tenant_id: row.tenant_id, campus_id: row.campus_id ?? undefined })
  loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-operation-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>续费提醒</span>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="级别">
          <el-select v-model="search.alert_level" clearable style="width: 140px;">
            <el-option label="紧急" value="urgent" />
            <el-option label="预警" value="warning" />
            <el-option label="普通" value="normal" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable style="width: 140px;">
            <el-option label="待跟进" value="open" />
            <el-option label="已忽略" value="ignored" />
            <el-option label="已关闭" value="closed" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadRows">
            查询
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="sortedRows" row-key="id">
        <el-table-column prop="student_id" label="学员" width="110" />
        <el-table-column prop="course_id" label="课程" width="110" />
        <el-table-column label="类型" width="150">
          <template #default="{ row }">
            {{ operationTypeLabel(row.alert_type) }}
          </template>
        </el-table-column>
        <el-table-column label="级别" width="110">
          <template #default="{ row }">
            <el-tag :type="operationTagType(row.alert_level)">
              {{ operationStatusLabel(row.alert_level) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="110">
          <template #default="{ row }">
            {{ operationStatusLabel(row.status) }}
          </template>
        </el-table-column>
        <el-table-column prop="due_date" label="到期日期" width="150" />
        <el-table-column prop="assignee_id" label="负责人" width="110" />
        <el-table-column label="操作" fixed="right" width="190">
          <template #default="{ row }">
            <el-button v-if="permissions.renewalFollow" link type="primary" @click="openFollow(row)">
              跟进
            </el-button>
            <el-button link @click="ignore(row)">
              忽略
            </el-button>
            <el-button link type="success" @click="close(row)">
              关闭
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无续费提醒" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <RenewalFollowDrawer v-model="drawerVisible" :row="current" @success="loadRows" />
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
