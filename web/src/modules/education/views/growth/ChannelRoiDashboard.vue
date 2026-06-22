<script setup lang="ts">
import type { ChannelRoiRecord } from '../../api/growth/channel-roi.ts'
import { getChannelRoi } from '../../api/growth/channel-roi.ts'
import { channelRoiFilterPayload, growthText, roiTagType } from './growthRules.ts'

defineOptions({ name: 'EducationGrowthChannelRoiDashboard' })

const filters = reactive({ source_id: undefined as number | undefined, dateRange: undefined as [string, string] | undefined })
const rows = ref<ChannelRoiRecord[]>([])
const loading = ref(false)

async function loadRows() {
  loading.value = true
  try {
    const response = await getChannelRoi(channelRoiFilterPayload(filters))
    rows.value = response.data.list
  }
  finally {
    loading.value = false
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-growth-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>渠道 ROI</span>
          <el-button type="primary" :loading="loading" @click="loadRows">
            {{ growthText.refresh }}
          </el-button>
        </div>
      </template>
      <el-form inline>
        <el-form-item :label="growthText.fields.source">
          <el-input-number v-model="filters.source_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item label="日期范围">
          <el-date-picker v-model="filters.dateRange" type="daterange" value-format="YYYY-MM-DD" />
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="source_id">
        <el-table-column prop="source_id" :label="growthText.fields.source" width="100" />
        <el-table-column prop="lead_count" :label="growthText.fields.lead" width="100" />
        <el-table-column prop="converted_count" :label="growthText.fields.converted" width="120" />
        <el-table-column prop="cost_cents" :label="growthText.fields.cost" width="120" />
        <el-table-column prop="converted_revenue_cents" :label="growthText.fields.revenue" width="120" />
        <el-table-column label="ROI" width="110">
          <template #default="{ row }">
            <el-tag :type="roiTagType(row.roi)">
              {{ row.roi ?? 'N/A' }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无渠道 ROI 数据" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-growth-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }
}
</style>
