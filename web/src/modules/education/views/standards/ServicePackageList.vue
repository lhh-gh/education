<script setup lang="ts">
import { saveServicePackage } from '../../api/standards/package.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { standardStatusLabel } from './standardRules.ts'

defineOptions({ name: 'EducationStandardsServicePackageList' })

const form = reactive({ package_code: '', package_name: '', course_id: 0, guardian_visible: false })
const rows = ref<any[]>([])
const canSave = computed(() => hasAuth('education:standards:package:save'))

async function save() {
  const response = await saveServicePackage(form)
  rows.value.unshift({ ...form, id: response.data.service_package_id, status: response.data.status, version_no: response.data.version_no })
}
</script>

<template>
  <div class="mine-layout pt-3">
    <el-card shadow="never">
      <template #header>
        <span>服务包</span>
      </template>
      <el-form inline>
        <el-form-item label="服务包编码"><el-input v-model="form.package_code" /></el-form-item>
        <el-form-item label="服务包名称"><el-input v-model="form.package_name" /></el-form-item>
        <el-form-item label="课程 ID"><el-input-number v-model="form.course_id" :min="0" /></el-form-item>
        <el-form-item label="家长可见"><el-switch v-model="form.guardian_visible" /></el-form-item>
        <el-button v-if="canSave" type="primary" @click="save">保存</el-button>
      </el-form>
      <el-table :data="rows" row-key="id">
        <el-table-column prop="package_code" label="编码" />
        <el-table-column prop="package_name" label="名称" />
        <el-table-column prop="version_no" label="版本" width="100" />
        <el-table-column label="状态" width="120">
          <template #default="{ row }">{{ standardStatusLabel(row.status) }}</template>
        </el-table-column>
        <template #empty><el-empty description="暂无服务包" /></template>
      </el-table>
    </el-card>
  </div>
</template>
