<script setup lang="ts">
import type { MaterialRelationRow } from '../../api/content/types.ts'
import { pageMaterialRelations, saveMaterialRelations } from '../../api/content/relation.ts'

defineOptions({ name: 'EducationContentMaterialRelationEditor' })

const loading = ref(false)
const materialId = ref<number>()
const rows = ref<MaterialRelationRow[]>([])
const draft = reactive({ target_type: 'course', target_id: undefined as number | undefined, relation_note: '' })
const canSave = computed(() => hasAuth('education:content:relation:save'))

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
        <span>资料关联</span>
      </template>
      <el-form inline>
        <el-form-item label="资料 ID">
          <el-input-number v-model="materialId" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item label="关联对象">
          <el-select v-model="draft.target_type" class="w-36">
            <el-option label="课程" value="course" />
            <el-option label="标准" value="standard" />
            <el-option label="阶段目标" value="stage_goal" />
          </el-select>
          <el-input-number v-model="draft.target_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-button v-if="canSave" type="primary" @click="save">
          保存关联
        </el-button>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="target_type" label="对象类型" width="150" />
        <el-table-column prop="target_id" label="对象 ID" width="120" />
        <el-table-column prop="relation_note" label="备注" min-width="180" />
        <template #empty>
          <el-empty description="暂无关联资料" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>
