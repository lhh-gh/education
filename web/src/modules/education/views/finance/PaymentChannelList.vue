<script setup lang="ts">
import type { PaymentChannelRecord } from '../../api/finance/payment.ts'
import { pagePaymentChannels, savePaymentChannel } from '../../api/finance/payment.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { financeErrorMessage, financePageText, financePermissions, financeStatusLabel, financeTagType, paymentChannelTypeLabel } from './financeRules.ts'

defineOptions({ name: 'EducationFinancePaymentChannelList' })

const message = useMessage()
const rows = ref<PaymentChannelRecord[]>([])
const errorText = ref('')
const form = reactive({ channel_code: '', channel_name: '', channel_type: 'offline_cash', status: 'enabled', sort_order: 0 })
const permissions = computed(() => financePermissions(hasAuth))

async function loadRows() {
  try {
    const response = await pagePaymentChannels({})
    rows.value = response.data
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = financeErrorMessage(error, '支付渠道加载失败')
    message.error(errorText.value)
  }
}

async function save() {
  try {
    await savePaymentChannel(form)
    message.success(financePageText.channels.saved)
    await loadRows()
  }
  catch (error: any) {
    message.error(financeErrorMessage(error, '支付渠道保存失败'))
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-finance-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>{{ financePageText.channels.title }}</span>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="form" class="search-form">
        <el-form-item :label="financePageText.channels.fields.code">
          <el-input v-model="form.channel_code" />
        </el-form-item>
        <el-form-item :label="financePageText.channels.fields.name">
          <el-input v-model="form.channel_name" />
        </el-form-item>
        <el-form-item :label="financePageText.channels.fields.type">
          <el-select v-model="form.channel_type" style="width: 160px;">
            <el-option :label="paymentChannelTypeLabel('offline_cash')" value="offline_cash" />
            <el-option :label="paymentChannelTypeLabel('offline_bank')" value="offline_bank" />
            <el-option label="POS" value="offline_pos" />
            <el-option :label="paymentChannelTypeLabel('wechat')" value="wechat" />
          </el-select>
        </el-form-item>
        <el-form-item v-if="permissions.paymentChannelSave">
          <el-button type="primary" @click="save">
            {{ financePageText.channels.save }}
          </el-button>
        </el-form-item>
      </el-form>
      <el-table :data="rows" row-key="id">
        <el-table-column prop="channel_code" :label="financePageText.channels.columns.code" />
        <el-table-column prop="channel_name" :label="financePageText.channels.columns.name" />
        <el-table-column :label="financePageText.channels.columns.type">
          <template #default="{ row }">
            {{ paymentChannelTypeLabel(row.channel_type) }}
          </template>
        </el-table-column>
        <el-table-column :label="financePageText.channels.columns.status">
          <template #default="{ row }">
            <el-tag :type="financeTagType(row.status)">
              {{ financeStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty :description="financePageText.channels.empty" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-finance-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }

  .page-alert,
  .search-form { margin-bottom: 12px; }
}
</style>
