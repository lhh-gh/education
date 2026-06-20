<script setup lang="ts">
import type { StandardVersionRow } from '../../api/standards/types.ts'
import { publishStandardVersion, saveLocalizationOverride } from '../../api/standards/version.ts'
import { versionActionState } from './standardRules.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'

defineOptions({ name: 'EducationStandardsStandardVersionList' })

const rows = ref<StandardVersionRow[]>([])
const canPublish = computed(() => hasAuth('education:standards:version:publish'))
const canLocalize = computed(() => hasAuth('education:standards:version:localization'))

async function publish(row: StandardVersionRow) {
  await publishStandardVersion(row.id, { publish_note: 'approved' })
}

async function localize(row: StandardVersionRow) {
  await saveLocalizationOverride(row.id, { override_json: { localized: true } })
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>标准版本</span></template><el-table :data="rows"><el-table-column prop="business_type" label="类型" /><el-table-column prop="version_no" label="版本" /><el-table-column label="状态"><template #default="{ row }"><el-tag>{{ versionActionState(row).badge }}</el-tag></template></el-table-column><el-table-column label="操作"><template #default="{ row }"><el-button v-if="canPublish" :disabled="!versionActionState(row).canPublish" @click="publish(row)">发布</el-button><el-button v-if="canLocalize" @click="localize(row)">本地化</el-button></template></el-table-column></el-table></el-card></div>
</template>
