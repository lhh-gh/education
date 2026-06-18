<script setup lang="ts">
import type { GrowthRecordRecord } from '../../api/family/report.ts'
import { pageGrowthRecords } from '../../api/family/report.ts'
import { familyTagType, guardianVisibleMarker } from './familyRules.ts'

defineOptions({ name: 'EducationFamilyGrowthRecordList' })

const loading = ref(false)
const rows = ref<GrowthRecordRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, student_id: undefined as number | undefined })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageGrowthRecords(search)
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
  <div class="mine-layout education-family-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Growth Records</span>
          <el-button :loading="loading" @click="loadRows">
            Refresh
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="student_id" label="Student" width="120" />
        <el-table-column prop="title" label="Title" min-width="220" />
        <el-table-column prop="record_type" label="Type" width="130" />
        <el-table-column label="Status" width="130">
          <template #default="{ row }">
            <el-tag :type="familyTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Visibility" width="150">
          <template #default="{ row }">
            {{ guardianVisibleMarker(row) }}
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No growth records" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>
