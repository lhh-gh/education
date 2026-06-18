<script setup lang="ts">
import type { ShowcasePayload } from '../../api/content/showcase.ts'
import type { ShowcaseRow } from '../../api/content/types.ts'
import { pageShowcases, publishShowcase, saveShowcase, withdrawShowcase } from '../../api/content/showcase.ts'
import { showcaseEditState } from './contentRules.ts'
import ShowcaseEditor from './components/ShowcaseEditor.vue'

defineOptions({ name: 'EducationContentShowcaseList' })

const loading = ref(false)
const rows = ref<ShowcaseRow[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, student_id: undefined as number | undefined })
const form = reactive<ShowcasePayload>({ student_id: 0, title: '', items: [] })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageShowcases(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function save() {
  const response = await saveShowcase(form)
  rows.value.unshift({ id: response.data.showcase_id, status: 'draft', ...form })
}

async function publish(row: ShowcaseRow) {
  await publishShowcase(row.id)
  await loadRows()
}

async function withdraw(row: ShowcaseRow) {
  await withdrawShowcase(row.id)
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout pt-3">
    <el-card shadow="never">
      <template #header>
        <span>Stage Achievement Showcases</span>
      </template>
      <ShowcaseEditor v-model="form" />
      <el-button type="primary" @click="save">
        Save Showcase
      </el-button>
      <el-table v-loading="loading" class="mt-4" :data="rows" row-key="id">
        <el-table-column prop="title" label="Title" min-width="180" />
        <el-table-column prop="student_id" label="Student" width="120" />
        <el-table-column prop="status" label="Status" width="130" />
        <el-table-column label="Edit State" width="190">
          <template #default="{ row }">
            <el-tag :type="showcaseEditState(row).canEdit ? 'success' : 'info'">
              {{ showcaseEditState(row).badge }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="190">
          <template #default="{ row }">
            <el-button link type="primary" :disabled="!showcaseEditState(row).canEdit" @click="publish(row)">
              Publish
            </el-button>
            <el-button link type="warning" @click="withdraw(row)">
              Withdraw
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No showcases" />
        </template>
      </el-table>
      <el-pagination class="mt-4 justify-end" layout="total" :total="total" />
    </el-card>
  </div>
</template>
