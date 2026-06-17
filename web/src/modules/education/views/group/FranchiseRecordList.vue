<script setup lang="ts">
import type { FranchiseRecord } from '../../api/group/franchise.ts'
import { pageFranchiseRecords, saveFranchiseRecord } from '../../api/group/franchise.ts'
import { groupTagType } from './groupRules.ts'

defineOptions({ name: 'EducationGroupFranchiseRecordList' })

const loading = ref(false)
const rows = ref<FranchiseRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, status: '' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageFranchiseRecords(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function createPotential() {
  await saveFranchiseRecord({ franchise_code: `F${Date.now()}`, franchise_name: 'Potential Franchise' })
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-group-page pt-3">
    <el-alert class="mb-3" type="info" title="Franchise reservations are group-admin only" show-icon />
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Franchise Records</span>
          <el-button type="primary" @click="createPotential">
            New Record
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="franchise_code" label="Code" width="160" />
        <el-table-column prop="franchise_name" label="Name" min-width="180" />
        <el-table-column prop="region" label="Region" width="140" />
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="groupTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No franchise records" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>
