<script setup lang="ts">
import type { StandardVersionRow } from '../../api/standards/types.ts'
import { publishStandardVersion, saveLocalizationOverride } from '../../api/standards/version.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { standardStatusLabel, versionActionState } from './standardRules.ts'

defineOptions({ name: 'EducationStandardsStandardVersionList' })

const action = reactive({
  standard_version_id: undefined as number | undefined,
  business_type: 'service_package',
  publish_note: '',
  localization_title: '',
})
const rows = ref<StandardVersionRow[]>([])
const canPublish = computed(() => hasAuth('education:standards:version:publish'))
const canLocalize = computed(() => hasAuth('education:standards:version:localization'))

function upsertActionRow(id: number, status: StandardVersionRow['status']) {
  rows.value.unshift({
    id,
    business_type: action.business_type,
    business_id: id,
    version_no: 1,
    status,
    review_status: status === 'published' ? 'approved' : 'pending',
  })
}

async function publish() {
  if (!action.standard_version_id) {
    return
  }
  await publishStandardVersion(action.standard_version_id, { publish_note: action.publish_note })
  upsertActionRow(action.standard_version_id, 'published')
}

async function localize() {
  if (!action.standard_version_id) {
    return
  }
  await saveLocalizationOverride(action.standard_version_id, {
    override_json: { title: action.localization_title || '本地化标准' },
  })
  upsertActionRow(action.standard_version_id, 'draft')
}
</script>

<template>
  <div class="mine-layout education-standards-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>标准版本</span>
        </div>
      </template>

      <el-form :inline="true" :model="action" class="save-form">
        <el-form-item label="标准版本 ID">
          <el-input-number v-model="action.standard_version_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="业务类型">
          <el-select v-model="action.business_type" class="filter-select">
            <el-option label="服务包" value="service_package" />
            <el-option label="试听标准" value="trial_standard" />
            <el-option label="交付标准" value="delivery_standard" />
          </el-select>
        </el-form-item>
        <el-form-item label="发布备注">
          <el-input v-model="action.publish_note" clearable placeholder="请输入发布备注" />
        </el-form-item>
        <el-form-item label="本地化标题">
          <el-input v-model="action.localization_title" clearable placeholder="请输入本地化标题" />
        </el-form-item>
        <el-form-item>
          <el-button v-if="canPublish" type="primary" @click="publish">
            发布
          </el-button>
          <el-button v-if="canLocalize" @click="localize">
            本地化
          </el-button>
        </el-form-item>
      </el-form>

      <el-table :data="rows" row-key="id">
        <el-table-column prop="id" label="标准版本 ID" width="130" />
        <el-table-column prop="business_type" label="业务类型" width="150" />
        <el-table-column prop="version_no" label="版本" width="100" />
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag>{{ standardStatusLabel(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="评审状态" width="120">
          <template #default="{ row }">
            {{ versionActionState(row).badge }}
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无标准版本" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-standards-page {
  .filter-select {
    width: 140px;
  }

  .save-form {
    margin-bottom: 16px;
  }
}
</style>
