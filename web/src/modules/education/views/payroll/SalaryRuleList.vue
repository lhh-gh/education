<script setup lang="ts">
import type { SalaryRuleRecord } from '../../api/payroll/rule.ts'
import { pageSalaryRules } from '../../api/payroll/rule.ts'
import SalaryRuleForm from './components/SalaryRuleForm.vue'
import { canSelectRuleForPreview, centsToYuan, payrollTagType } from './payrollRules.ts'

defineOptions({ name: 'EducationPayrollSalaryRuleList' })

const loading = ref(false)
const rows = ref<SalaryRuleRecord[]>([])
const total = ref(0)
const formVisible = ref(false)
const search = reactive({ page: 1, pageSize: 20, campus_id: undefined as number | undefined, status: '', rule_type: '' } as any)

async function loadRows() {
  loading.value = true
  try {
    const response = await pageSalaryRules(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-payroll-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Salary Rules</span>
          <el-button type="primary" @click="formVisible = true">
            New Rule
          </el-button>
        </div>
      </template>
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="Type">
          <el-input v-model="search.rule_type" clearable />
        </el-form-item>
        <el-form-item label="Status">
          <el-input v-model="search.status" clearable />
        </el-form-item>
        <el-form-item>
          <el-button @click="loadRows">
            Search
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="rule_name" label="Rule" min-width="180" />
        <el-table-column prop="rule_type" label="Type" width="130" />
        <el-table-column label="Base" width="130">
          <template #default="{ row }">
            {{ centsToYuan(row.base_amount_cents) }}
          </template>
        </el-table-column>
        <el-table-column label="Unit" width="130">
          <template #default="{ row }">
            {{ centsToYuan(row.unit_amount_cents) }}
          </template>
        </el-table-column>
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="payrollTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Preview" width="120">
          <template #default="{ row }">
            <el-button link type="primary" :disabled="!canSelectRuleForPreview(row)">
              Select
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No salary rules" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <SalaryRuleForm v-model="formVisible" @success="loadRows" />
  </div>
</template>
