<script setup lang="ts">
import type { MaterialVersionPayload } from '../../api/content/version.ts'
import type { MaterialVersionRow } from '../../api/content/types.ts'
import { createMaterialVersion, pageMaterialVersions } from '../../api/content/version.ts'
import MaterialVersionDrawer from './components/MaterialVersionDrawer.vue'

defineOptions({ name: 'EducationContentMaterialVersionList' })

const loading = ref(false)
const rows = ref<MaterialVersionRow[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, material_id: undefined as number | undefined })
const drawerVisible = ref(false)
const form = reactive<MaterialVersionPayload>({ title: '', content: '' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageMaterialVersions(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function createVersion() {
  if (!search.material_id) {
    return
  }
  await createMaterialVersion(search.material_id, form)
  drawerVisible.value = false
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout pt-3">
    <el-card shadow="never">
      <template #header>
        <span>Material Versions</span>
      </template>
      <el-form inline>
        <el-form-item label="Material ID">
          <el-input-number v-model="search.material_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-button type="primary" @click="loadRows">
          Search
        </el-button>
        <el-button @click="drawerVisible = true">
          New Version
        </el-button>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="version_no" label="Version" width="120" />
        <el-table-column prop="title" label="Title" min-width="180" />
        <el-table-column prop="status" label="Status" width="130" />
        <template #empty>
          <el-empty description="No versions" />
        </template>
      </el-table>
      <el-pagination class="mt-4 justify-end" layout="total" :total="total" />
    </el-card>
    <MaterialVersionDrawer v-model:visible="drawerVisible" v-model:model="form" :material-id="search.material_id">
      <template #footer>
        <el-button type="primary" @click="createVersion">
          Save Draft Version
        </el-button>
      </template>
    </MaterialVersionDrawer>
  </div>
</template>
