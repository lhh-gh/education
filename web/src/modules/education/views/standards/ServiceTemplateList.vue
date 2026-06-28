<script setup lang="ts">
import type { ServiceTemplatePayload } from '../../api/standards/template.ts'
import { saveServiceTemplateSet } from '../../api/standards/template.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'

defineOptions({ name: 'EducationStandardsServiceTemplateList' })

interface ServiceTemplateRow extends ServiceTemplatePayload {
  id: number
}

const form = reactive<ServiceTemplatePayload>({
  template_set_code: '',
  template_set_name: '',
  course_id: undefined,
  items: [{ item_type: 'notice', item_title: '', item_content: '', sort_order: 1 }],
})
const rows = ref<ServiceTemplateRow[]>([])
const canSave = computed(() => hasAuth('education:standards:template:save'))

async function save() {
  const response = await saveServiceTemplateSet(form)
  rows.value.unshift({ ...form, id: response.data.template_set_id })
  form.template_set_code = ''
  form.template_set_name = ''
  form.course_id = undefined
  form.items = [{ item_type: 'notice', item_title: '', item_content: '', sort_order: 1 }]
}
</script>

<template>
  <div class="mine-layout education-standards-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>服务模板</span>
        </div>
      </template>

      <el-form v-if="canSave" :model="form" label-width="120px" class="save-form">
        <el-form-item label="模板编码">
          <el-input v-model="form.template_set_code" clearable placeholder="请输入模板编码" />
        </el-form-item>
        <el-form-item label="模板名称">
          <el-input v-model="form.template_set_name" clearable placeholder="请输入模板名称" />
        </el-form-item>
        <el-form-item label="课程 ID">
          <el-input-number v-model="form.course_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="模板类型">
          <el-select v-model="form.items![0].item_type" class="filter-select">
            <el-option label="通知" value="notice" />
            <el-option label="报告" value="report" />
            <el-option label="跟进" value="followup" />
          </el-select>
        </el-form-item>
        <el-form-item label="模板标题">
          <el-input v-model="form.items![0].item_title" clearable placeholder="请输入模板标题" />
        </el-form-item>
        <el-form-item label="模板内容">
          <el-input v-model="form.items![0].item_content" type="textarea" :rows="3" placeholder="请输入模板内容" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="save">
            保存服务模板
          </el-button>
        </el-form-item>
      </el-form>

      <el-table :data="rows" row-key="id">
        <el-table-column prop="template_set_code" label="模板编码" width="160" />
        <el-table-column prop="template_set_name" label="模板名称" min-width="180" />
        <el-table-column prop="course_id" label="课程 ID" width="120" />
        <el-table-column label="模板类型" width="120">
          <template #default="{ row }">
            {{ row.items?.[0]?.item_type ?? '-' }}
          </template>
        </el-table-column>
        <el-table-column label="模板内容" min-width="220" show-overflow-tooltip>
          <template #default="{ row }">
            {{ row.items?.[0]?.item_content ?? '-' }}
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无服务模板" />
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
