<script setup lang="ts">
import { saveServicePackage } from '../../api/standards/package.ts'

defineOptions({ name: 'EducationStandardsServicePackageList' })

const form = reactive({ package_code: '', package_name: '', course_id: 0, guardian_visible: false })
const rows = ref<any[]>([])

async function save() {
  const response = await saveServicePackage(form)
  rows.value.unshift({ ...form, id: response.data.service_package_id, status: response.data.status, version_no: response.data.version_no })
}
</script>

<template>
  <div class="mine-layout pt-3">
    <el-card shadow="never">
      <template #header>
        <span>Service Packages</span>
      </template>
      <el-form inline>
        <el-form-item label="Code"><el-input v-model="form.package_code" /></el-form-item>
        <el-form-item label="Name"><el-input v-model="form.package_name" /></el-form-item>
        <el-form-item label="Course"><el-input-number v-model="form.course_id" :min="0" /></el-form-item>
        <el-form-item label="Guardian"><el-switch v-model="form.guardian_visible" /></el-form-item>
        <el-button type="primary" @click="save">Save</el-button>
      </el-form>
      <el-table :data="rows" row-key="id">
        <el-table-column prop="package_code" label="Code" />
        <el-table-column prop="package_name" label="Name" />
        <el-table-column prop="version_no" label="Version" width="100" />
        <el-table-column prop="status" label="Status" width="120" />
        <template #empty><el-empty description="No packages" /></template>
      </el-table>
    </el-card>
  </div>
</template>
