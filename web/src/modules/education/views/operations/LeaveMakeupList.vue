<script setup lang="ts">
import type { MakeupEntitlementPageParams, MakeupEntitlementRecord } from '../../api/operations/makeup.ts'
import { cancelMakeupRecord, pageMakeupEntitlements } from '../../api/operations/makeup.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import MakeupArrangeForm from './components/MakeupArrangeForm.vue'
import { operationPermissions, operationTagType } from './operationRules.ts'

defineOptions({ name: 'EducationOperationMakeupList' })

const loading = ref(false)
const arrangeVisible = ref(false)
const current = ref<MakeupEntitlementRecord | null>(null)
const rows = ref<MakeupEntitlementRecord[]>([])
const total = ref(0)
const errorText = ref('')
const search = reactive<MakeupEntitlementPageParams>({ page: 1, pageSize: 20, campus_id: undefined, student_id: undefined, course_id: undefined, status: undefined, keyword: '' })
const permissions = computed(() => operationPermissions(hasAuth))

async function loadRows() {
  loading.value = true
  try {
    const response = await pageMakeupEntitlements(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Make-up list loading failed'
  }
  finally {
    loading.value = false
  }
}

function openArrange(row: MakeupEntitlementRecord) {
  current.value = row
  arrangeVisible.value = true
}

async function cancelArrangement(row: MakeupEntitlementRecord) {
  try {
    await cancelMakeupRecord(row.id, { tenant_id: row.tenant_id, campus_id: row.campus_id ?? undefined, reason: 'cancel arrangement' })
    loadRows()
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Cancel failed'
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-operation-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Leave Make-up Closure</span>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="Campus">
          <el-input-number v-model="search.campus_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Student">
          <el-input-number v-model="search.student_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Course">
          <el-input-number v-model="search.course_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Status">
          <el-select v-model="search.status" clearable style="width: 150px;">
            <el-option v-for="item in ['available', 'arranged', 'used', 'expired', 'cancelled']" :key="item" :label="item" :value="item" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadRows">
            Search
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="student_id" label="Student" width="110" />
        <el-table-column prop="course_id" label="Course" width="110" />
        <el-table-column prop="source_lesson_id" label="Source Lesson" width="130" />
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="operationTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="expires_at" label="Expires At" width="180" />
        <el-table-column label="Actions" fixed="right" width="180">
          <template #default="{ row }">
            <el-button v-if="permissions.arrangeMakeup && row.status === 'available'" link type="primary" @click="openArrange(row)">
              Arrange
            </el-button>
            <el-button v-if="row.status === 'arranged'" link type="danger" @click="cancelArrangement(row)">
              Cancel
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No make-up entitlement" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <MakeupArrangeForm v-model="arrangeVisible" :row="current" @success="loadRows" />
  </div>
</template>

<style scoped lang="scss">
.education-operation-page {
  .page-header { font-weight: 600; }

  .page-alert,
  .search-form { margin-bottom: 12px; }
  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>
