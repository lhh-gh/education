<script setup lang="ts">
import type { StandardVersionRow } from '../../api/standards/types.ts'
import { publishStandardVersion, saveLocalizationOverride } from '../../api/standards/version.ts'
import { versionActionState } from './standardRules.ts'

defineOptions({ name: 'EducationStandardsStandardVersionList' })

const rows = ref<StandardVersionRow[]>([])

async function publish(row: StandardVersionRow) {
  await publishStandardVersion(row.id, { publish_note: 'approved' })
}

async function localize(row: StandardVersionRow) {
  await saveLocalizationOverride(row.id, { override_json: { localized: true } })
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>Standard Versions</span></template><el-table :data="rows"><el-table-column prop="business_type" label="Type" /><el-table-column prop="version_no" label="Version" /><el-table-column label="State"><template #default="{ row }"><el-tag>{{ versionActionState(row).badge }}</el-tag></template></el-table-column><el-table-column label="Actions"><template #default="{ row }"><el-button :disabled="!versionActionState(row).canPublish" @click="publish(row)">Publish</el-button><el-button @click="localize(row)">Localize</el-button></template></el-table-column></el-table></el-card></div>
</template>
