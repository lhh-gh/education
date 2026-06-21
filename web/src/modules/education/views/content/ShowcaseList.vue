<script setup lang="ts">
import type { ShowcasePayload } from '../../api/content/showcase.ts'
import type { ShowcaseRow } from '../../api/content/types.ts'
import { pageShowcases, publishShowcase, saveShowcase, withdrawShowcase } from '../../api/content/showcase.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { contentStatusLabel, contentStatusTag, showcaseEditState } from './contentRules.ts'
import ShowcaseEditor from './components/ShowcaseEditor.vue'

defineOptions({ name: 'EducationContentShowcaseList' })

const loading = ref(false)
const rows = ref<ShowcaseRow[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, student_id: undefined as number | undefined })
const form = reactive<ShowcasePayload>({ student_id: 0, title: '', items: [] })
const canSave = computed(() => hasAuth('education:content:showcase:save'))
const canPublish = computed(() => hasAuth('education:content:showcase:publish'))
const canWithdraw = computed(() => hasAuth('education:content:showcase:withdraw'))

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

function resetSearch() {
  search.page = 1
  search.student_id = undefined
  loadRows()
}

async function save() {
  const response = await saveShowcase(form)
  rows.value.unshift({ id: response.data.showcase_id, status: response.data.status as ShowcaseRow['status'], ...form })
  form.student_id = 0
  form.stage_goal_id = undefined
  form.title = ''
  form.summary = ''
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
  <div class="mine-layout education-content-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>成果展陈</span>
        </div>
      </template>

      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="学生 ID">
          <el-input-number v-model="search.student_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadRows">
            查询
          </el-button>
          <el-button @click="resetSearch">
            重置
          </el-button>
        </el-form-item>
      </el-form>

      <ShowcaseEditor v-model="form" />
      <el-button v-if="canSave" type="primary" class="mb-4" @click="save">
        保存展陈
      </el-button>

      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="title" label="展陈标题" min-width="180" />
        <el-table-column prop="student_id" label="学生 ID" width="120" />
        <el-table-column prop="stage_goal_id" label="阶段目标 ID" width="140" />
        <el-table-column label="状态" width="130">
          <template #default="{ row }">
            <el-tag :type="contentStatusTag(row.status)">
              {{ contentStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="编辑状态" width="160">
          <template #default="{ row }">
            <el-tag :type="showcaseEditState(row).canEdit ? 'success' : 'info'">
              {{ showcaseEditState(row).badge }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="190">
          <template #default="{ row }">
            <el-button v-if="canPublish" link type="primary" :disabled="!showcaseEditState(row).canEdit" @click="publish(row)">
              发布
            </el-button>
            <el-button v-if="canWithdraw" link type="warning" @click="withdraw(row)">
              撤回
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无成果展陈" />
        </template>
      </el-table>

      <el-pagination
        v-model:current-page="search.page"
        v-model:page-size="search.pageSize"
        class="page-pagination"
        layout="total, sizes, prev, pager, next"
        :total="total"
        @change="loadRows"
      />
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-content-page {
  .page-pagination {
    justify-content: flex-end;
    margin-top: 16px;
  }
}
</style>
