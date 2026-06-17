<script setup lang="ts">
import type { RenewalAlertPageParams, RenewalAlertRecord } from '../../api/operations/renewal.ts'
import { closeRenewalAlert, ignoreRenewalAlert, pageRenewalAlerts } from '../../api/operations/renewal.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import RenewalFollowDrawer from './components/RenewalFollowDrawer.vue'
import { operationPermissions, operationTagType, sortRenewalAlerts } from './operationRules.ts'

defineOptions({ name: 'EducationOperationRenewalAlertList' })

const loading = ref(false)
const drawerVisible = ref(false)
const current = ref<RenewalAlertRecord | null>(null)
const rows = ref<RenewalAlertRecord[]>([])
const total = ref(0)
const errorText = ref('')
const search = reactive<RenewalAlertPageParams>({ page: 1, pageSize: 20, campus_id: undefined, alert_type: undefined, alert_level: undefined, status: 'open', assignee_id: undefined })
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
    errorText.value = error?.message ?? 'Renewal alerts loading failed'
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
          <span>Renewal Alerts</span>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="Campus">
          <el-input-number v-model="search.campus_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Level">
          <el-select v-model="search.alert_level" clearable style="width: 140px;">
            <el-option label="urgent" value="urgent" />
            <el-option label="warning" value="warning" />
            <el-option label="normal" value="normal" />
          </el-select>
        </el-form-item>
        <el-form-item label="Status">
          <el-select v-model="search.status" clearable style="width: 140px;">
            <el-option label="open" value="open" />
            <el-option label="ignored" value="ignored" />
            <el-option label="closed" value="closed" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadRows">
            Search
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="sortedRows" row-key="id">
        <el-table-column prop="student_id" label="Student" width="110" />
        <el-table-column prop="course_id" label="Course" width="110" />
        <el-table-column prop="alert_type" label="Type" width="150" />
        <el-table-column label="Level" width="110">
          <template #default="{ row }">
            <el-tag :type="operationTagType(row.alert_level)">
              {{ row.alert_level }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="Status" width="110" />
        <el-table-column prop="due_date" label="Due Date" width="150" />
        <el-table-column prop="assignee_id" label="Assignee" width="110" />
        <el-table-column label="Actions" fixed="right" width="190">
          <template #default="{ row }">
            <el-button v-if="permissions.renewalFollow" link type="primary" @click="openFollow(row)">
              Follow
            </el-button>
            <el-button link @click="ignore(row)">
              Ignore
            </el-button>
            <el-button link type="success" @click="close(row)">
              Close
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No renewal alerts" />
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
