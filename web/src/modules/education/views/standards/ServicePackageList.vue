<script setup lang="ts">
import type { ServicePackagePayload, ServicePackageRow } from '../../api/standards/package.ts'
import { saveServicePackage } from '../../api/standards/package.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { standardStatusLabel } from './standardRules.ts'

defineOptions({ name: 'EducationStandardsServicePackageList' })

const form = reactive<ServicePackagePayload>({
  package_code: '',
  package_name: '',
  course_id: 0,
  guardian_visible: false,
  description: '',
})
const rows = ref<ServicePackageRow[]>([])
const canSave = computed(() => hasAuth('education:standards:package:save'))

async function save() {
  const response = await saveServicePackage(form)
  rows.value.unshift({
    ...form,
    id: response.data.service_package_id,
    version_no: response.data.version_no,
    status: response.data.status,
  })
  form.package_code = ''
  form.package_name = ''
  form.description = ''
}
</script>

<template>
  <div class="mine-layout education-standards-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>服务包</span>
        </div>
      </template>

      <el-form v-if="canSave" :inline="true" :model="form" class="save-form">
        <el-form-item label="服务包编码">
          <el-input v-model="form.package_code" clearable placeholder="请输入服务包编码" />
        </el-form-item>
        <el-form-item label="服务包名称">
          <el-input v-model="form.package_name" clearable placeholder="请输入服务包名称" />
        </el-form-item>
        <el-form-item label="课程 ID">
          <el-input-number v-model="form.course_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="家长可见">
          <el-switch v-model="form.guardian_visible" />
        </el-form-item>
        <el-form-item label="说明">
          <el-input v-model="form.description" clearable placeholder="请输入说明" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="save">
            保存服务包
          </el-button>
        </el-form-item>
      </el-form>

      <el-table :data="rows" row-key="id">
        <el-table-column prop="package_code" label="服务包编码" width="160" />
        <el-table-column prop="package_name" label="服务包名称" min-width="180" />
        <el-table-column prop="course_id" label="课程 ID" width="120" />
        <el-table-column prop="version_no" label="版本" width="100" />
        <el-table-column label="家长可见" width="120">
          <template #default="{ row }">
            <el-tag :type="row.guardian_visible ? 'success' : 'info'">
              {{ row.guardian_visible ? '可见' : '不可见' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            {{ standardStatusLabel(row.status) }}
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无服务包" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-standards-page {
  .save-form {
    margin-bottom: 16px;
  }
}
</style>
