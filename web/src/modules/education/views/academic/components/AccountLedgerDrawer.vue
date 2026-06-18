<script setup lang="ts">
import type { AccountLedgerPageParams, AccountLedgerRecord, AccountLedgerSourceType } from '../../../api/academic/courseAccount.ts'
import { getAccountLedger } from '../../../api/academic/courseAccount.ts'
import { ledgerRowsBySource } from '../courseAccountRules.ts'

const props = defineProps<{
  modelValue: boolean
  accountId?: number
  tenantId?: number
}>()

const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()

const loading = ref(false)
const errorText = ref('')
const rows = ref<AccountLedgerRecord[]>([])
const total = ref(0)
const search = reactive<AccountLedgerPageParams>({ page: 1, page_size: 20, tenant_id: props.tenantId, source_type: undefined })
const visibleRows = computed(() => ledgerRowsBySource(rows.value, search.source_type as AccountLedgerSourceType | undefined))

watch(() => props.modelValue, (visible) => {
  if (visible && props.accountId) {
    search.tenant_id = props.tenantId
    loadRows()
  }
})

async function loadRows() {
  if (!props.accountId) {
    return
  }
  loading.value = true
  try {
    const response = await getAccountLedger(props.accountId, search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Ledger loading failed'
  }
  finally {
    loading.value = false
  }
}

function close() {
  emit('update:modelValue', false)
}

defineExpose({ loadRows, rows })
</script>

<template>
  <el-drawer :model-value="modelValue" title="Account Ledger" size="760px" @close="close">
    <el-alert v-if="errorText" class="drawer-alert" type="error" show-icon :closable="false" :title="errorText" />
    <el-form :inline="true" :model="search" class="drawer-filter">
      <el-form-item label="Source">
        <el-select v-model="search.source_type" clearable style="width: 180px;" @change="loadRows">
          <el-option label="Enrollment" value="enrollment" />
          <el-option label="Enrollment Cancel" value="enrollment_cancel" />
          <el-option label="Consumption" value="consumption" />
          <el-option label="Adjustment" value="adjustment" />
        </el-select>
      </el-form-item>
    </el-form>
    <el-table v-loading="loading" :data="visibleRows" row-key="source_no">
      <el-table-column prop="source_type" label="Source" width="150" />
      <el-table-column prop="source_no" label="No" width="180" />
      <el-table-column prop="direction" label="Direction" width="100" />
      <el-table-column prop="units" label="Units" width="100" />
      <el-table-column prop="before_available_units" label="Before" width="110" />
      <el-table-column prop="after_available_units" label="After" width="110" />
      <el-table-column prop="occurred_at" label="Occurred" width="180" />
      <el-table-column prop="remark" label="Remark" min-width="180" />
      <template #empty>
        <el-empty description="No ledger rows" />
      </template>
    </el-table>
    <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="drawer-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
  </el-drawer>
</template>

<style scoped lang="scss">
.drawer-alert,
.drawer-filter {
  margin-bottom: 12px;
}

.drawer-pagination {
  justify-content: flex-end;
  margin-top: 16px;
}
</style>
