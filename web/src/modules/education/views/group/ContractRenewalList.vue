<script setup lang="ts">
import type { ContractRenewalRecord } from '../../api/group/contract.ts'
import { pageContractRenewals } from '../../api/group/contract.ts'
import ContractRenewalDrawer from './components/ContractRenewalDrawer.vue'
import { groupTagType } from './groupRules.ts'

defineOptions({ name: 'EducationGroupContractRenewalList' })

const loading = ref(false)
const drawerVisible = ref(false)
const rows = ref<ContractRenewalRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, status: '' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageContractRenewals(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-group-page pt-3">
    <el-alert class="mb-3" type="info" title="Renewal reminders use contract and campus scope" show-icon />
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Contract Renewals</span>
          <el-button type="primary" @click="drawerVisible = true">
            Handle
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="contract_id" label="Contract" width="120" />
        <el-table-column prop="renewal_type" label="Type" width="140" />
        <el-table-column prop="due_date" label="Due Date" width="150" />
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="groupTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No renewal reminders" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <ContractRenewalDrawer v-model="drawerVisible" />
  </div>
</template>
