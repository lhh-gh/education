<script setup lang="ts">
import type { AbilityPointPayload } from '../../api/standards/stage-goal.ts'
import { saveAbilityPoint } from '../../api/standards/stage-goal.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'

defineOptions({ name: 'EducationStandardsAbilityPointList' })

interface AbilityPointRow extends AbilityPointPayload {
  id: number
  status?: string
}

const form = reactive<AbilityPointPayload>({
  ability_code: '',
  ability_name: '',
  ability_group: '',
  description: '',
})
const rows = ref<AbilityPointRow[]>([])
const canSave = computed(() => hasAuth('education:standards:ability:save'))

async function save() {
  const response = await saveAbilityPoint(form)
  rows.value.unshift({ ...form, id: response.data.ability_point_id, status: response.data.status })
  form.ability_code = ''
  form.ability_name = ''
  form.description = ''
}
</script>

<template>
  <div class="mine-layout education-standards-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>能力点</span>
        </div>
      </template>

      <el-form v-if="canSave" :inline="true" :model="form" class="save-form">
        <el-form-item label="能力点编码">
          <el-input v-model="form.ability_code" clearable placeholder="请输入能力点编码" />
        </el-form-item>
        <el-form-item label="能力点名称">
          <el-input v-model="form.ability_name" clearable placeholder="请输入能力点名称" />
        </el-form-item>
        <el-form-item label="能力分组">
          <el-input v-model="form.ability_group" clearable placeholder="请输入能力分组" />
        </el-form-item>
        <el-form-item label="能力说明">
          <el-input v-model="form.description" clearable placeholder="请输入能力说明" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="save">
            保存能力点
          </el-button>
        </el-form-item>
      </el-form>

      <el-table :data="rows" row-key="id">
        <el-table-column prop="ability_code" label="能力点编码" width="160" />
        <el-table-column prop="ability_name" label="能力点名称" min-width="180" />
        <el-table-column prop="ability_group" label="能力分组" width="160" />
        <el-table-column prop="description" label="能力说明" min-width="220" show-overflow-tooltip />
        <template #empty>
          <el-empty description="暂无能力点" />
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
