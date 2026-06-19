<script setup lang="ts">
import type { LeadSourceRecord } from '../../api/admissions/lead-source.ts'
import { createLeadSource, pageLeadSources } from '../../api/admissions/lead-source.ts'
import { admissionErrorText, admissionStatusLabel } from './admissionRules.ts'

defineOptions({ name: 'EducationAdmissionLeadSourceList' })

const loading = ref(false)
const rows = ref<LeadSourceRecord[]>([])
const total = ref(0)
const errorText = ref('')
const successText = ref('')
const search = reactive({ page: 1, pageSize: 20, tenant_id: undefined as number | undefined, campus_id: undefined as number | undefined, keyword: '' })
const form = reactive({ code: '', name: '', channel_type: 'offline' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageLeadSources(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = admissionErrorText(error)
  }
  finally {
    loading.value = false
  }
}

async function createSource() {
  try {
    await createLeadSource({ ...form, tenant_id: search.tenant_id, campus_id: search.campus_id })
    successText.value = '线索来源已保存'
    await loadRows()
  }
  catch (error: any) {
    errorText.value = admissionErrorText(error)
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout admission-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>线索来源</span>
          <el-button type="primary" @click="createSource">
            保存来源
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="true" :title="successText" @close="successText = ''" />
      <el-form :inline="true" :model="form" class="search-form">
        <el-form-item label="编码"><el-input v-model="form.code" /></el-form-item>
        <el-form-item label="名称"><el-input v-model="form.name" /></el-form-item>
        <el-form-item label="渠道"><el-input v-model="form.channel_type" /></el-form-item>
      </el-form>
      <el-skeleton v-if="loading" :rows="4" animated />
      <el-table v-else :data="rows" row-key="id">
        <el-table-column prop="code" label="编码" width="160" />
        <el-table-column prop="name" label="名称" min-width="180" />
        <el-table-column prop="channel_type" label="渠道" width="140" />
        <el-table-column label="状态" width="120"><template #default="{ row }">{{ admissionStatusLabel(row.status) }}</template></el-table-column>
        <template #empty><el-empty description="暂无线索来源" /></template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>
