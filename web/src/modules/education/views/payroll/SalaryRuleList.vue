<script setup lang="ts">
import type { SalaryRuleRecord } from '../../api/payroll/rule.ts'
import { pageSalaryRules } from '../../api/payroll/rule.ts'
import SalaryRuleForm from './components/SalaryRuleForm.vue'
import { canSelectRuleForPreview, centsToYuan, payrollStatusLabel, payrollTagType, payrollTypeLabel } from './payrollRules.ts'

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
          <span>薪酬规则</span>
          <el-button type="primary" @click="formVisible = true">
            新增规则
          </el-button>
        </div>
      </template>
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="类型">
          <el-input v-model="search.rule_type" clearable />
        </el-form-item>
        <el-form-item label="状态">
          <el-input v-model="search.status" clearable />
        </el-form-item>
        <el-form-item>
          <el-button @click="loadRows">
            查询
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="rule_name" label="规则" min-width="180" />
        <el-table-column label="类型" width="130">
          <template #default="{ row }">
            {{ payrollTypeLabel(row.rule_type) }}
          </template>
        </el-table-column>
        <el-table-column label="基础金额" width="130">
          <template #default="{ row }">
            {{ centsToYuan(row.base_amount_cents) }}
          </template>
        </el-table-column>
        <el-table-column label="单价" width="130">
          <template #default="{ row }">
            {{ centsToYuan(row.unit_amount_cents) }}
          </template>
        </el-table-column>
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="payrollTagType(row.status)">
              {{ payrollStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="预览" width="120">
          <template #default="{ row }">
            <el-button link type="primary" :disabled="!canSelectRuleForPreview(row)">
              选择
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无薪酬规则" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <SalaryRuleForm v-model="formVisible" @success="loadRows" />
  </div>
</template>
