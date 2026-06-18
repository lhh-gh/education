<script setup lang="ts">
import type { ChannelRoiRecord } from '../../api/growth/channel-roi.ts'
import { getChannelRoi } from '../../api/growth/channel-roi.ts'
import { channelRoiFilterPayload, roiTagType } from './growthRules.ts'

defineOptions({ name: 'EducationGrowthChannelRoiDashboard' })

const filters = reactive({ tenant_id: undefined as number | undefined, campus_id: undefined as number | undefined, source_id: undefined as number | undefined, dateRange: undefined as [string, string] | undefined })
const rows = ref<ChannelRoiRecord[]>([])

async function loadRows() {
  const response = await getChannelRoi(channelRoiFilterPayload(filters))
  rows.value = response.data.list
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-growth-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Channel ROI</span>
          <el-button type="primary" @click="loadRows">
            Refresh
          </el-button>
        </div>
      </template>
      <el-form inline>
        <el-form-item label="Source">
          <el-input-number v-model="filters.source_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item label="Date">
          <el-date-picker v-model="filters.dateRange" type="daterange" value-format="YYYY-MM-DD" />
        </el-form-item>
      </el-form>
      <el-table :data="rows" row-key="source_id">
        <el-table-column prop="source_id" label="Source" width="100" />
        <el-table-column prop="lead_count" label="Leads" width="100" />
        <el-table-column prop="converted_count" label="Converted" width="120" />
        <el-table-column prop="cost_cents" label="Cost" width="120" />
        <el-table-column prop="converted_revenue_cents" label="Revenue" width="120" />
        <el-table-column label="ROI" width="110">
          <template #default="{ row }">
            <el-tag :type="roiTagType(row.roi)">
              {{ row.roi ?? 'N/A' }}
            </el-tag>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-growth-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }
}
</style>
