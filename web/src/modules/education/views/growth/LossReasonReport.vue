<script setup lang="ts">
import { createLeadLossRecord, saveLossReason } from '../../api/growth/loss.ts'

defineOptions({ name: 'EducationGrowthLossReasonReport' })

const reasonForm = reactive({ reason_code: '', reason_name: '', category: 'price' })
const lossForm = reactive({ lead_id: undefined as number | undefined, loss_reason_id: undefined as number | undefined, detail: '' })
const rows = ref<Array<{ lead_id: number, status: string }>>([])

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
        <span>Loss Reasons</span>
      </template>
      <el-form inline>
        <el-form-item label="Code">
          <el-input v-model="reasonForm.reason_code" />
        </el-form-item>
        <el-form-item label="Name">
          <el-input v-model="reasonForm.reason_name" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="saveReason">
            Save Reason
          </el-button>
        </el-form-item>
      </el-form>
      <el-divider />
      <el-form inline>
        <el-form-item label="Lead">
          <el-input-number v-model="lossForm.lead_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item label="Reason">
          <el-input-number v-model="lossForm.loss_reason_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item label="Detail">
          <el-input v-model="lossForm.detail" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="createLoss">
            Mark Lost
          </el-button>
        </el-form-item>
      </el-form>
      <el-table :data="rows" row-key="lead_id">
        <el-table-column prop="lead_id" label="Lead" width="120" />
        <el-table-column prop="status" label="Status" width="120" />
      </el-table>
    </el-card>
  </div>
</template>
