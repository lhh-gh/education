<script setup lang="ts">
import { saveFollowupStrategy } from '../../api/growth/followup.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { growthStatusLabel, growthText } from './growthRules.ts'

defineOptions({ name: 'EducationGrowthFollowupStrategyList' })

const form = reactive({ strategy_code: '', strategy_name: '', lead_stage: 'followed', score_level: 'hot', suggestion_template: '', next_follow_hours: 4 })
const rows = ref<Array<typeof form & { status: string }>>([])
const canSave = computed(() => hasAuth('education:growth:strategy:save'))

async function save() {
  const response = await saveFollowupStrategy(form)
  rows.value.unshift({ ...form, status: response.data.status })
}
</script>

<template>
  <div class="mine-layout education-growth-page pt-3">
    <el-card shadow="never">
      <template #header>
        <span>{{ growthText.followupStrategyTitle }}</span>
      </template>
      <el-form inline>
        <el-form-item :label="growthText.fields.code">
          <el-input v-model="form.strategy_code" />
        </el-form-item>
        <el-form-item :label="growthText.fields.name">
          <el-input v-model="form.strategy_name" />
        </el-form-item>
        <el-form-item :label="growthText.fields.template">
          <el-input v-model="form.suggestion_template" />
        </el-form-item>
        <el-form-item v-if="canSave">
          <el-button type="primary" @click="save">
            {{ growthText.save }}
          </el-button>
        </el-form-item>
        <el-form-item v-else>
          <el-tag type="info">
            {{ growthText.noPermission }}
          </el-tag>
        </el-form-item>
      </el-form>
      <el-table :data="rows" row-key="strategy_code">
        <el-table-column prop="strategy_code" :label="growthText.fields.code" width="140" />
        <el-table-column prop="strategy_name" :label="growthText.fields.name" min-width="160" />
        <el-table-column :label="growthText.fields.score" width="120">
          <template #default="{ row }">
            {{ growthStatusLabel(row.score_level) }}
          </template>
        </el-table-column>
        <el-table-column :label="growthText.fields.status" width="120">
          <template #default="{ row }">
            {{ growthStatusLabel(row.status) }}
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>
