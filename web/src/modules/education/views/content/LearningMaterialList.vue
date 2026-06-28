<script setup lang="ts">
import type { LearningMaterialPayload } from '../../api/content/material.ts'
import type { LearningMaterialRow } from '../../api/content/types.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
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
const canSave = computed(() => hasAuth('education:content:material:save'))
const canPublish = computed(() => hasAuth('education:content:material:publish'))
const canWithdraw = computed(() => hasAuth('education:content:material:withdraw'))

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
        <span>学习资料</span>
      </template>
      <LearningMaterialForm v-model="form" />
      <el-button v-if="canSave" type="primary" :loading="saving" @click="save">
        保存资料
      </el-button>
      <el-table v-loading="loading" class="mt-4" :data="rows" row-key="id">
        <el-table-column prop="material_code" label="资料编码" width="150" />
        <el-table-column prop="material_name" label="资料名称" min-width="180" />
        <el-table-column prop="material_type" label="资料类型" width="120" />
        <el-table-column label="可见范围" width="150">
          <template #default="{ row }">
            <el-tag :type="row.guardian_visible ? 'success' : 'info'">
              {{ guardianVisibleLabel(row) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="发布状态" width="150">
          <template #default="{ row }">
            <el-tag>{{ materialPublishState(row).badge }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="180">
          <template #default="{ row }">
            <el-button v-if="canPublish" link type="primary" :disabled="!materialPublishState(row).canPublish" @click="publish(row)">
              发布
            </el-button>
            <el-button v-if="canWithdraw" link type="warning" @click="withdraw(row)">
              撤回
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无学习资料" />
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
