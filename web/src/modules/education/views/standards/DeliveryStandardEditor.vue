<script setup lang="ts">
import type { DeliveryStandardPayload } from '../../api/standards/delivery-standard.ts'
import { saveDeliveryStandard } from '../../api/standards/delivery-standard.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'

defineOptions({ name: 'EducationStandardsDeliveryStandardEditor' })

interface DeliveryStandardRow extends DeliveryStandardPayload {
  id: number
}

const form = reactive<DeliveryStandardPayload>({
  course_id: 0,
  standard_code: '',
  standard_name: '',
  lesson_type: 'regular',
  content: '',
})
const rows = ref<DeliveryStandardRow[]>([])
const canSave = computed(() => hasAuth('education:standards:delivery:save'))

async function save() {
  const response = await saveDeliveryStandard(form)
  rows.value.unshift({ ...form, id: response.data.delivery_standard_id })
  form.standard_code = ''
  form.standard_name = ''
  form.content = ''
}
</script>

<template>
  <div class="mine-layout education-standards-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>交付标准</span>
        </div>
      </template>

      <el-form v-if="canSave" :model="form" label-width="120px" class="save-form">
        <el-form-item label="课程 ID">
          <el-input-number v-model="form.course_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="标准编码">
          <el-input v-model="form.standard_code" clearable placeholder="请输入标准编码" />
        </el-form-item>
        <el-form-item label="标准名称">
          <el-input v-model="form.standard_name" clearable placeholder="请输入标准名称" />
        </el-form-item>
        <el-form-item label="课型">
          <el-select v-model="form.lesson_type" class="filter-select">
            <el-option label="常规课" value="regular" />
            <el-option label="试听课" value="trial" />
            <el-option label="补课" value="makeup" />
          </el-select>
        </el-form-item>
        <el-form-item label="交付内容">
          <el-input v-model="form.content" type="textarea" :rows="3" placeholder="请输入交付内容" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="save">
            保存交付标准
          </el-button>
        </el-form-item>
      </el-form>

      <el-table :data="rows" row-key="id">
        <el-table-column prop="standard_code" label="标准编码" width="150" />
        <el-table-column prop="standard_name" label="标准名称" min-width="180" />
        <el-table-column prop="course_id" label="课程 ID" width="120" />
        <el-table-column prop="lesson_type" label="课型" width="120" />
        <el-table-column prop="content" label="交付内容" min-width="260" show-overflow-tooltip />
        <template #empty>
          <el-empty description="暂无交付标准" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-standards-page {
  .filter-select {
    width: 140px;
  }

  .save-form {
    margin-bottom: 16px;
  }
}
</style>
