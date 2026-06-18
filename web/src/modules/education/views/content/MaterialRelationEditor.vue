<script setup lang="ts">
import type { MaterialRelationRow } from '../../api/content/types.ts'
import { pageMaterialRelations, saveMaterialRelations } from '../../api/content/relation.ts'

defineOptions({ name: 'EducationContentMaterialRelationEditor' })

const loading = ref(false)
const materialId = ref<number>()
const rows = ref<MaterialRelationRow[]>([])
const draft = reactive({ target_type: 'course', target_id: undefined as number | undefined, relation_note: '' })

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
  await loadRows()
}
</script>

<template>
  <div class="mine-layout pt-3">
    <el-card shadow="never">
      <template #header>
        <span>Material Relations</span>
      </template>
      <el-form inline>
        <el-form-item label="Material ID">
          <el-input-number v-model="materialId" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item label="Target">
          <el-select v-model="draft.target_type" class="w-36">
            <el-option label="Course" value="course" />
            <el-option label="Standard" value="standard" />
            <el-option label="Stage Goal" value="stage_goal" />
          </el-select>
          <el-input-number v-model="draft.target_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-button type="primary" @click="save">
          Save
        </el-button>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="target_type" label="Target Type" width="150" />
        <el-table-column prop="target_id" label="Target ID" width="120" />
        <el-table-column prop="relation_note" label="Note" min-width="180" />
        <template #empty>
          <el-empty description="No relations" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>
