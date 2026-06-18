<script setup lang="ts">
import type { LeadPageParams, LeadRecord } from '../../api/admissions/lead.ts'
import { assignLead, createLead, pageLeads } from '../../api/admissions/lead.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { admissionErrorText, admissionPermissions, admissionTagType } from './admissionRules.ts'

defineOptions({ name: 'EducationAdmissionLeadPool' })

const loading = ref(false)
const rows = ref<LeadRecord[]>([])
const total = ref(0)
const errorText = ref('')
const successText = ref('')
const search = reactive<LeadPageParams>({ page: 1, pageSize: 20, tenant_id: undefined, campus_id: undefined, keyword: '', stage: undefined })
const form = reactive({ contact_name: '', contact_mobile: '', student_name: '' })
const permissions = computed(() => admissionPermissions(hasAuth))

async function loadRows() {
  loading.value = true
  try {
    const response = await pageLeads(search)
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

async function submitLead() {
  try {
    await createLead({ tenant_id: search.tenant_id, campus_id: search.campus_id, contact_name: form.contact_name, contact_mobile: form.contact_mobile, lead_students: [{ name: form.student_name || form.contact_name }] })
    successText.value = 'Lead saved'
    await loadRows()
  }
  catch (error: any) {
    errorText.value = admissionErrorText(error)
  }
}

async function assign(row: LeadRecord) {
  await assignLead(row.id, { tenant_id: search.tenant_id, campus_id: search.campus_id, to_user_id: row.owner_user_id || 1, reason: 'manual assign' })
  successText.value = 'Lead assigned'
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout admission-page pt-3">
    <el-card shadow="never">
      <template #header><div class="page-header"><span>Lead Pool</span><el-button v-if="permissions.createLead" type="primary" @click="submitLead">New Lead</el-button></div></template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="true" :title="successText" @close="successText = ''" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="Keyword"><el-input v-model="search.keyword" /></el-form-item>
        <el-form-item label="Contact"><el-input v-model="form.contact_name" /></el-form-item>
        <el-form-item label="Mobile"><el-input v-model="form.contact_mobile" /></el-form-item>
        <el-form-item label="Student"><el-input v-model="form.student_name" /></el-form-item>
        <el-form-item><el-button type="primary" @click="loadRows">Search</el-button></el-form-item>
      </el-form>
      <el-skeleton v-if="loading" :rows="5" animated />
      <el-table v-else :data="rows" row-key="id">
        <el-table-column prop="lead_no" label="Lead No" width="180" />
        <el-table-column prop="contact_name" label="Contact" width="150" />
        <el-table-column prop="contact_mobile" label="Mobile" width="150" />
        <el-table-column label="Stage" width="150"><template #default="{ row }"><el-tag :type="admissionTagType(row.stage)">{{ row.stage }}</el-tag></template></el-table-column>
        <el-table-column prop="owner_user_id" label="Owner" width="120" />
        <el-table-column label="Actions" fixed="right" width="140"><template #default="{ row }"><el-button v-if="permissions.assignLead" link type="primary" @click="assign(row)">Assign</el-button></template></el-table-column>
        <template #empty><el-empty description="No leads" /></template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>
