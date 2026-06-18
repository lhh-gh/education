<script setup lang="ts">
import type { OrgUnitRecord } from '../../api/group/org.ts'
import { getOrgUnitTree } from '../../api/group/org.ts'
import OrgUnitForm from './components/OrgUnitForm.vue'
import { keepOrgTreeAfterCycleError } from './groupRules.ts'

defineOptions({ name: 'EducationGroupOrgUnitTree' })

const loading = ref(false)
const formVisible = ref(false)
const rows = ref<OrgUnitRecord[]>([])
const scopeText = ref('Current user data scope')
const errorText = ref('')

async function loadRows() {
  loading.value = true
  try {
    const response = await getOrgUnitTree()
    rows.value = response.data
    errorText.value = ''
  }
  catch (error: any) {
    const result = keepOrgTreeAfterCycleError(rows.value, error)
    rows.value = result.rows
    errorText.value = result.errorText || (error?.message ?? 'Load failed')
  }
  finally {
    loading.value = false
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-group-page pt-3">
    <el-alert class="mb-3" type="info" :title="scopeText" show-icon />
    <el-alert v-if="errorText" class="mb-3" type="error" :title="errorText" show-icon />
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Org Units</span>
          <el-button type="primary" @click="formVisible = true">
            New Org
          </el-button>
        </div>
      </template>
      <el-tree v-loading="loading" :data="rows" node-key="id" :props="{ label: 'name', children: 'children' }" default-expand-all>
        <template #empty>
          <el-empty description="No org units" />
        </template>
      </el-tree>
    </el-card>
    <OrgUnitForm v-model="formVisible" @success="loadRows" />
  </div>
</template>
