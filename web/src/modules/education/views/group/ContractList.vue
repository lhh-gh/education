<script setup lang="ts">
import type { ContractRecord } from '../../api/group/contract.ts'
import { pageContracts, submitContractReview } from '../../api/group/contract.ts'
import ContractForm from './components/ContractForm.vue'
import { centsToYuan, groupTagType } from './groupRules.ts'

defineOptions({ name: 'EducationGroupContractList' })

const loading = ref(false)
const formVisible = ref(false)
const rows = ref<ContractRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, status: '' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageContracts(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function submitReview(row: ContractRecord) {
  await submitContractReview(row.id)
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-group-page pt-3">
    <el-alert class="mb-3" type="info" title="Contract list is filtered by current group data scope" show-icon />
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Contracts</span>
          <el-button type="primary" @click="formVisible = true">
            New Contract
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="contract_no" label="No." width="160" />
        <el-table-column prop="title" label="Title" min-width="180" />
        <el-table-column label="Amount" width="140">
          <template #default="{ row }">
            {{ centsToYuan(row.amount_cents) }}
          </template>
        </el-table-column>
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="groupTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Risk" width="110">
          <template #default="{ row }">
            <el-tag :type="groupTagType(row.risk_level)">
              {{ row.risk_level ?? 'normal' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Action" width="140">
          <template #default="{ row }">
            <el-button link type="primary" @click="submitReview(row)">
              Submit
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No contracts" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <ContractForm v-model="formVisible" @success="loadRows" />
  </div>
</template>
