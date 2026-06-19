<script setup lang="ts">
import type { TrialLessonRecord } from '../../api/admissions/trial.ts'
import { createTrialLesson, pageTrialLessons, saveTrialAttendance } from '../../api/admissions/trial.ts'
import { admissionErrorText, admissionStatusLabel, admissionTagType } from './admissionRules.ts'

defineOptions({ name: 'EducationAdmissionTrialCalendar' })

const loading = ref(false)
const rows = ref<TrialLessonRecord[]>([])
const total = ref(0)
const errorText = ref('')
const conflictText = ref('')
const search = reactive({ page: 1, pageSize: 20, tenant_id: undefined as number | undefined, campus_id: undefined as number | undefined, date: '' })
const form = reactive({ lead_id: undefined as number | undefined, lead_student_id: undefined as number | undefined, course_id: undefined as number | undefined, teacher_id: undefined as number | undefined, start_time: '', end_time: '' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageTrialLessons(search)
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

async function submitTrial() {
  try {
    await createTrialLesson({ ...form, tenant_id: search.tenant_id, campus_id: search.campus_id })
    await loadRows()
  }
  catch (error: any) {
    conflictText.value = admissionErrorText(error)
  }
}

async function markAttended(row: TrialLessonRecord) {
  await saveTrialAttendance(row.id, { tenant_id: search.tenant_id, campus_id: search.campus_id, lead_student_id: row.lead_student_id, attendance_status: 'attended' })
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout admission-page pt-3">
    <el-card shadow="never">
      <template #header><div class="page-header"><span>试听日历</span><el-button type="primary" @click="submitTrial">预约试听</el-button></div></template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="conflictText" class="page-alert" type="error" show-icon :closable="true" :title="conflictText" @close="conflictText = ''" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="日期"><el-input v-model="search.date" placeholder="YYYY-MM-DD" /></el-form-item>
        <el-form-item label="线索"><el-input-number v-model="form.lead_id" :controls="false" /></el-form-item>
        <el-form-item label="学员"><el-input-number v-model="form.lead_student_id" :controls="false" /></el-form-item>
        <el-form-item label="教师"><el-input-number v-model="form.teacher_id" :controls="false" /></el-form-item>
        <el-form-item><el-button @click="loadRows">查询</el-button></el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="lead_id" label="线索" width="100" />
        <el-table-column prop="teacher_id" label="教师" width="120" />
        <el-table-column prop="start_time" label="开始时间" width="180" />
        <el-table-column prop="end_time" label="结束时间" width="180" />
        <el-table-column label="状态" width="120"><template #default="{ row }"><el-tag :type="admissionTagType(row.status)">{{ admissionStatusLabel(row.status) }}</el-tag></template></el-table-column>
        <el-table-column label="操作" fixed="right" width="140"><template #default="{ row }"><el-button link type="primary" @click="markAttended(row)">到课</el-button></template></el-table-column>
        <template #empty><el-empty description="暂无试听记录" /></template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>
