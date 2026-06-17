<script setup lang="ts">
import type { PaymentChannelRecord } from '../../api/finance/payment.ts'
import { pagePaymentChannels, savePaymentChannel } from '../../api/finance/payment.ts'
import { financeTagType } from './financeRules.ts'

defineOptions({ name: 'EducationFinancePaymentChannelList' })

const rows = ref<PaymentChannelRecord[]>([])
const form = reactive({ channel_code: '', channel_name: '', channel_type: 'offline_cash', status: 'enabled', sort_order: 0 })

async function loadRows() {
  const response = await pagePaymentChannels({})
  rows.value = response.data
}

async function save() {
  await savePaymentChannel(form)
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-finance-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Payment Channels</span>
        </div>
      </template>
      <el-form :inline="true" :model="form" class="search-form">
        <el-form-item label="Code">
          <el-input v-model="form.channel_code" />
        </el-form-item>
        <el-form-item label="Name">
          <el-input v-model="form.channel_name" />
        </el-form-item>
        <el-form-item label="Type">
          <el-select v-model="form.channel_type" style="width: 160px;">
            <el-option label="Cash" value="offline_cash" />
            <el-option label="Bank" value="offline_bank" />
            <el-option label="POS" value="offline_pos" />
            <el-option label="WeChat" value="wechat" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="save">
            Save
          </el-button>
        </el-form-item>
      </el-form>
      <el-table :data="rows" row-key="id">
        <el-table-column prop="channel_code" label="Code" />
        <el-table-column prop="channel_name" label="Name" />
        <el-table-column prop="channel_type" label="Type" />
        <el-table-column label="Status">
          <template #default="{ row }">
            <el-tag :type="financeTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>
