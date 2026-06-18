<script setup lang="ts">
import type { LearningMaterialPayload } from '../../api/content/material.ts'
import type { LearningMaterialRow } from '../../api/content/types.ts'
import { pageLearningMaterials, publishLearningMaterial, saveLearningMaterial, withdrawLearningMaterial } from '../../api/content/material.ts'
import { guardianVisibleLabel, materialPublishState, publishFailureNotice } from './contentRules.ts'
import LearningMaterialForm from './components/LearningMaterialForm.vue'

defineOptions({ name: 'EducationContentLearningMaterialList' })

const loading = ref(false)
const saving = ref(false)
const rows = ref<LearningMaterialRow[]>([])
const total = ref(0)
const errorMessage = ref('')
const search = reactive({ page: 1, pageSize: 20, status: '' })
const form = reactive<LearningMaterialPayload>({ material_code: '', material_name: '', material_type: 'worksheet', guardian_visible: false })

async function loadRows() {
  loading.value = true
  errorMessage.value = ''
  try {
    const response = await pageLearningMaterials(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  catch (error: any) {
    errorMessage.value = publishFailureNotice(error)
  }
  finally {
    loading.value = false
  }
}

async function save() {
  saving.value = true
  try {
    const response = await saveLearningMaterial(form)
    rows.value.unshift({ id: response.data.material_id, status: 'draft', current_version_id: undefined, ...form })
  }
  finally {
    saving.value = false
  }
}

async function publish(row: LearningMaterialRow) {
  try {
    await publishLearningMaterial(row.id, { publish_note: 'pc publish' })
    await loadRows()
  }
  catch (error: any) {
    errorMessage.value = publishFailureNotice(error)
  }
}

async function withdraw(row: LearningMaterialRow) {
  await withdrawLearningMaterial(row.id)
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-content-page pt-3">
    <el-alert v-if="errorMessage" class="mb-3" :title="errorMessage" type="error" show-icon />
    <el-card shadow="never">
      <template #header>
        <span>Learning Materials</span>
      </template>
      <LearningMaterialForm v-model="form" />
      <el-button type="primary" :loading="saving" @click="save">
        Save
      </el-button>
      <el-table v-loading="loading" class="mt-4" :data="rows" row-key="id">
        <el-table-column prop="material_code" label="Code" width="150" />
        <el-table-column prop="material_name" label="Name" min-width="180" />
        <el-table-column prop="material_type" label="Type" width="120" />
        <el-table-column label="Visibility" width="150">
          <template #default="{ row }">
            <el-tag :type="row.guardian_visible ? 'success' : 'info'">
              {{ guardianVisibleLabel(row) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Status" width="150">
          <template #default="{ row }">
            <el-tag>{{ materialPublishState(row).badge }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="180">
          <template #default="{ row }">
            <el-button link type="primary" :disabled="!materialPublishState(row).canPublish" @click="publish(row)">
              Publish
            </el-button>
            <el-button link type="warning" @click="withdraw(row)">
              Withdraw
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No learning materials" />
        </template>
      </el-table>
      <el-pagination class="page-pagination" layout="total" :total="total" />
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-content-page {
  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>
