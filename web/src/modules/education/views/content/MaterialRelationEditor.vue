<script setup lang="ts">
import type { MaterialRelationRow } from '../../api/content/types.ts'
import { pageMaterialRelations, saveMaterialRelations } from '../../api/content/relation.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'

defineOptions({ name: 'EducationContentMaterialRelationEditor' })

const loading = ref(false)
const materialId = ref<number>()
const rows = ref<MaterialRelationRow[]>([])
const draft = reactive({ target_type: 'course', target_id: undefined as number | undefined, relation_note: '' })
const canSave = computed(() => hasAuth('education:content:relation:save'))

const targetTypeOptions = [
  { label: '课程', value: 'course' },
  { label: '标准', value: 'standard' },
  { label: '阶段目标', value: 'stage_goal' },
]

function targetTypeLabel(value: string) {
  return targetTypeOptions.find(item => item.value === value)?.label ?? value
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageMaterialRelations({ material_id: materialId.value })
    rows.value = response.data.list
  }
  finally {
    loading.value = false
  }
}

async function save() {
  if (!materialId.value || !draft.target_id) {
    return
  }
  await saveMaterialRelations(materialId.value, { relations: [{ target_type: draft.target_type, target_id: draft.target_id, relation_note: draft.relation_note }] })
  draft.target_id = undefined
  draft.relation_note = ''
  await loadRows()
}
</script>

<template>
  <div class="mine-layout education-content-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>资料关联</span>
        </div>
      </template>

      <el-form :inline="true" class="search-form">
        <el-form-item label="资料 ID">
          <el-input-number v-model="materialId" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="关联对象">
          <el-select v-model="draft.target_type" class="filter-select">
            <el-option v-for="item in targetTypeOptions" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="对象 ID">
          <el-input-number v-model="draft.target_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="备注">
          <el-input v-model="draft.relation_note" clearable placeholder="请输入关联备注" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadRows">
            查询
          </el-button>
          <el-button v-if="canSave" type="primary" @click="save">
            保存关联
          </el-button>
        </el-form-item>
      </el-form>

      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="material_id" label="资料 ID" width="120" />
        <el-table-column label="对象类型" width="150">
          <template #default="{ row }">
            {{ targetTypeLabel(row.target_type) }}
          </template>
        </el-table-column>
        <el-table-column prop="target_id" label="对象 ID" width="120" />
        <el-table-column prop="relation_note" label="备注" min-width="180" show-overflow-tooltip />
        <template #empty>
          <el-empty description="暂无关联资料" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-content-page {
  .filter-select {
    width: 140px;
  }
}
</style>
