<script setup lang="ts">
import type { NoticeReceiptPageParams, NoticeReceiptRecord, NoticeRecord } from '../../../api/academic/notice.ts'
import { pageNoticeReceipts } from '../../../api/academic/notice.ts'
import { receiptReadSummary } from '../noticeRules.ts'

defineOptions({ name: 'EducationNoticeReceiptDrawer' })

const props = defineProps<{
  modelValue: boolean
  notice?: NoticeRecord | null
  tenantId?: number
}>()

const emit = defineEmits<{
  (event: 'update:modelValue', value: boolean): void
}>()

const loading = ref(false)
const rows = ref<NoticeReceiptRecord[]>([])
const total = ref(0)
const errorText = ref('')
const search = reactive<NoticeReceiptPageParams>({ page: 1, pageSize: 20, tenant_id: props.tenantId, status: undefined, student_id: undefined, guardian_id: undefined, keyword: '' })

watch(() => props.modelValue, (visible) => {
  if (visible) {
    search.page = 1
    search.tenant_id = props.tenantId
    loadRows()
  }
})

async function loadRows() {
  if (!props.notice) {
    return
  }
  loading.value = true
  try {
    const response = await pageNoticeReceipts(props.notice.id, search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Notice receipts loading failed'
  }
  finally {
    loading.value = false
  }
}

function handleSearch() {
  search.page = 1
  loadRows()
}
</script>

<template>
  <el-drawer :model-value="modelValue" title="Notice Receipts" size="720px" @update:model-value="emit('update:modelValue', $event)">
    <el-alert v-if="errorText" class="mb-3" type="error" show-icon :closable="false" :title="errorText" />
    <div v-if="notice" class="receipt-summary">
      <el-tag type="info">
        {{ receiptReadSummary(notice.receipt_count, notice.read_count) }}
      </el-tag>
    </div>
    <el-form :inline="true" :model="search" class="search-form">
      <el-form-item label="Status">
        <el-select v-model="search.status" clearable style="width: 140px;">
          <el-option label="Unread" value="unread" />
          <el-option label="Read" value="read" />
        </el-select>
      </el-form-item>
      <el-form-item label="Student ID">
        <el-input-number v-model="search.student_id" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item label="Guardian ID">
        <el-input-number v-model="search.guardian_id" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item label="Keyword">
        <el-input v-model="search.keyword" clearable />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="handleSearch">
          Search
        </el-button>
      </el-form-item>
    </el-form>
    <el-table v-loading="loading" :data="rows" row-key="id">
      <el-table-column prop="guardian_name_snapshot" label="Guardian" min-width="140" />
      <el-table-column prop="student_name_snapshot" label="Student" min-width="140" />
      <el-table-column prop="relation" label="Relation" width="110" />
      <el-table-column prop="status" label="Status" width="100" />
      <el-table-column prop="delivered_at" label="Delivered At" width="180" />
      <el-table-column prop="read_at" label="Read At" width="180" />
      <template #empty>
        <el-empty description="No receipts" />
      </template>
    </el-table>
    <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
  </el-drawer>
</template>

<style scoped lang="scss">
.receipt-summary,
.search-form {
  margin-bottom: 12px;
}

.page-pagination {
  justify-content: flex-end;
  margin-top: 16px;
}
</style>
