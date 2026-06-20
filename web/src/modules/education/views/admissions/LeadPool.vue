<script setup lang="ts">
import type { LeadPageParams, LeadRecord } from '../../api/admissions/lead.ts'
import { assignLead, createLead, pageLeads } from '../../api/admissions/lead.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { admissionErrorText, admissionPermissions, admissionStageLabel, admissionTagType } from './admissionRules.ts'
import { useEducationScope } from '@/composables/education/useEducationScope.ts'

defineOptions({ name: 'EducationAdmissionLeadPool' })

const { scope } = useEducationScope()

const loading = ref(false)
const rows = ref<LeadRecord[]>([])
const total = ref(0)
const errorText = ref('')
const successText = ref('')
const search = reactive<LeadPageParams>({ page: 1, pageSize: 20, keyword: '', stage: undefined })
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
    await createLead({ tenant_id: scope.tenant_id, campus_id: scope.campus_id, contact_name: form.contact_name, contact_mobile: form.contact_mobile, lead_students: [{ name: form.student_name || form.contact_name }] })
    successText.value = '线索已保存'
    await loadRows()
  }
  catch (error: any) {
    errorText.value = admissionErrorText(error)
  }
}

async function assign(row: LeadRecord) {
  await assignLead(row.id, { tenant_id: scope.tenant_id, campus_id: scope.campus_id, to_user_id: row.owner_user_id || 1, reason: 'manual assign' })
  successText.value = '线索已分配'
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout admission-page pt-3">
    <el-card shadow="never">
      <template #header><div class="page-header"><span>线索池</span><el-button v-if="permissions.createLead" type="primary" @click="submitLead">新增线索</el-button></div></template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="true" :title="successText" @close="successText = ''" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="关键字"><el-input v-model="search.keyword" /></el-form-item>
        <el-form-item label="联系人"><el-input v-model="form.contact_name" /></el-form-item>
        <el-form-item label="手机号"><el-input v-model="form.contact_mobile" /></el-form-item>
        <el-form-item label="学员"><el-input v-model="form.student_name" /></el-form-item>
        <el-form-item><el-button type="primary" @click="loadRows">查询</el-button></el-form-item>
      </el-form>
      <el-skeleton v-if="loading" :rows="5" animated />
      <el-table v-else :data="rows" row-key="id">
        <el-table-column prop="lead_no" label="线索编号" width="180" />
        <el-table-column prop="contact_name" label="联系人" width="150" />
        <el-table-column prop="contact_mobile" label="手机号" width="150" />
        <el-table-column label="阶段" width="150"><template #default="{ row }"><el-tag :type="admissionTagType(row.stage)">{{ admissionStageLabel(row.stage) }}</el-tag></template></el-table-column>
        <el-table-column prop="owner_user_id" label="负责人" width="120" />
        <el-table-column label="操作" fixed="right" width="140"><template #default="{ row }"><el-button v-if="permissions.assignLead" link type="primary" @click="assign(row)">分配</el-button></template></el-table-column>
        <template #empty><el-empty description="暂无线索" /></template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>
