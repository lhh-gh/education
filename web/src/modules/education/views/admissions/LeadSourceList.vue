<script setup lang="ts">
import type { LeadSourceRecord } from '../../api/admissions/lead-source.ts'
import { createLeadSource, pageLeadSources } from '../../api/admissions/lead-source.ts'

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
    errorText.value = error?.message ?? 'Lead sources loading failed'
  }
  finally {
    loading.value = false
  }
}

async function createSource() {
  try {
    await createLeadSource({ ...form, tenant_id: search.tenant_id, campus_id: search.campus_id })
    successText.value = 'Lead source saved'
    await loadRows()
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Lead source save failed'
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout admission-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Lead Sources</span>
          <el-button type="primary" @click="createSource">
            Save Source
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="true" :title="successText" @close="successText = ''" />
      <el-form :inline="true" :model="form" class="search-form">
        <el-form-item label="Code"><el-input v-model="form.code" /></el-form-item>
        <el-form-item label="Name"><el-input v-model="form.name" /></el-form-item>
        <el-form-item label="Channel"><el-input v-model="form.channel_type" /></el-form-item>
      </el-form>
      <el-skeleton v-if="loading" :rows="4" animated />
      <el-table v-else :data="rows" row-key="id">
        <el-table-column prop="code" label="Code" width="160" />
        <el-table-column prop="name" label="Name" min-width="180" />
        <el-table-column prop="channel_type" label="Channel" width="140" />
        <el-table-column prop="status" label="Status" width="120" />
        <template #empty><el-empty description="No lead sources" /></template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>
