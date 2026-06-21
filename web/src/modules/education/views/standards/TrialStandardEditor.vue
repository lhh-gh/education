<script setup lang="ts">
import type { TrialStandardPayload } from '../../api/standards/trial-standard.ts'
import { saveTrialStandard } from '../../api/standards/trial-standard.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { standardStatusLabel } from './standardRules.ts'

defineOptions({ name: 'EducationStandardsTrialStandardEditor' })

interface TrialStandardRow extends TrialStandardPayload {
  id: number
  status: string
}

const form = reactive<TrialStandardPayload>({
  course_id: 0,
  standard_code: '',
  standard_name: '',
  guardian_visible: false,
  items: [{ item_name: '', item_content: '', score_weight: 100, sort_order: 1 }],
})
const rows = ref<TrialStandardRow[]>([])
const canSave = computed(() => hasAuth('education:standards:trial:save'))

async function save() {
  const response = await saveTrialStandard(form)
  rows.value.unshift({ ...form, id: response.data.trial_standard_id, status: response.data.status ?? 'draft' })
  form.standard_code = ''
  form.standard_name = ''
  form.items = [{ item_name: '', item_content: '', score_weight: 100, sort_order: 1 }]
}
</script>

<template>
  <div class="mine-layout education-standards-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>试听标准</span>
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
        <el-form-item label="家长可见">
          <el-switch v-model="form.guardian_visible" />
        </el-form-item>
        <el-form-item label="评价项目">
          <div class="standard-item">
            <el-input v-model="form.items![0].item_name" clearable placeholder="项目名称" />
            <el-input v-model="form.items![0].item_content" clearable placeholder="项目内容" />
            <el-input-number v-model="form.items![0].score_weight" :min="0" :max="100" :controls="false" />
          </div>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="save">
            保存试听标准
          </el-button>
        </el-form-item>
      </el-form>

      <el-table :data="rows" row-key="id">
        <el-table-column prop="standard_code" label="标准编码" width="150" />
        <el-table-column prop="standard_name" label="标准名称" min-width="180" />
        <el-table-column prop="course_id" label="课程 ID" width="120" />
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
          <el-empty description="暂无试听标准" />
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

  .standard-item {
    display: grid;
    grid-template-columns: minmax(160px, 1fr) minmax(220px, 2fr) 120px;
    gap: 8px;
    width: min(720px, 100%);
  }
}
</style>
