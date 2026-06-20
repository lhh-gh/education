<script setup lang="ts">
import { createLeadLossRecord, saveLossReason } from '../../api/growth/loss.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { growthStatusLabel, growthText } from './growthRules.ts'

defineOptions({ name: 'EducationGrowthLossReasonReport' })

const reasonForm = reactive({ reason_code: '', reason_name: '', category: 'price' })
const lossForm = reactive({ lead_id: undefined as number | undefined, loss_reason_id: undefined as number | undefined, detail: '' })
const rows = ref<Array<{ lead_id: number, status: string }>>([])
const canSaveReason = computed(() => hasAuth('education:growth:loss-reason:save'))
const canCreateLoss = computed(() => hasAuth('education:growth:loss:create'))

async function saveReason() {
  await saveLossReason(reasonForm)
}

async function createLoss() {
  if (!lossForm.lead_id || !lossForm.loss_reason_id) {
    return
  }
  const response = await createLeadLossRecord(lossForm.lead_id, { loss_reason_id: lossForm.loss_reason_id, detail: lossForm.detail })
  rows.value.unshift(response.data)
}
</script>

<template>
  <div class="mine-layout education-growth-page pt-3">
    <el-card shadow="never">
      <template #header>
        <span>{{ growthText.lossReasonTitle }}</span>
      </template>
      <el-form inline>
        <el-form-item :label="growthText.fields.code">
          <el-input v-model="reasonForm.reason_code" />
        </el-form-item>
        <el-form-item :label="growthText.fields.name">
          <el-input v-model="reasonForm.reason_name" />
        </el-form-item>
        <el-form-item v-if="canSaveReason">
          <el-button type="primary" @click="saveReason">
            {{ growthText.saveReason }}
          </el-button>
        </el-form-item>
        <el-form-item v-else>
          <el-tag type="info">
            {{ growthText.noPermission }}
          </el-tag>
        </el-form-item>
      </el-form>
      <el-divider />
      <el-form inline>
        <el-form-item :label="growthText.fields.lead">
          <el-input-number v-model="lossForm.lead_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item :label="growthText.fields.reason">
          <el-input-number v-model="lossForm.loss_reason_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item :label="growthText.fields.detail">
          <el-input v-model="lossForm.detail" />
        </el-form-item>
        <el-form-item v-if="canCreateLoss">
          <el-button type="primary" @click="createLoss">
            {{ growthText.markLost }}
          </el-button>
        </el-form-item>
        <el-form-item v-else>
          <el-tag type="info">
            {{ growthText.noPermission }}
          </el-tag>
        </el-form-item>
      </el-form>
      <el-table :data="rows" row-key="lead_id">
        <el-table-column prop="lead_id" :label="growthText.fields.lead" width="120" />
        <el-table-column :label="growthText.fields.status" width="120">
          <template #default="{ row }">
            {{ growthStatusLabel(row.status) }}
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>
