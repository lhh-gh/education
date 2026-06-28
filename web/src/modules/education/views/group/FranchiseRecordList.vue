<script setup lang="ts">
import type { FranchiseRecord } from '../../api/group/franchise.ts'
import { pageFranchiseRecords, saveFranchiseRecord } from '../../api/group/franchise.ts'
import { groupStatusLabel, groupTagType } from './groupRules.ts'

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
  await saveFranchiseRecord({ franchise_code: `F${Date.now()}`, franchise_name: '潜在加盟商' })
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-group-page pt-3">
    <el-alert class="mb-3" type="info" title="加盟备案仅集团管理员可维护" show-icon />
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>加盟管理</span>
          <el-button type="primary" @click="createPotential">
            新增记录
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="franchise_code" label="编码" width="160" />
        <el-table-column prop="franchise_name" label="名称" min-width="180" />
        <el-table-column prop="region" label="区域" width="140" />
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="groupTagType(row.status)">
              {{ groupStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无加盟记录" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>
