<script setup lang="ts">
import type { AuditLogDetail } from '../../../api/foundation/auditLog.ts'
import { auditPayloadSections, formatAuditPayload } from './auditPayloadRules.ts'

const props = defineProps<{
  detail: AuditLogDetail | null
  loading: boolean
  error: string
}>()

const visible = defineModel<boolean>({ default: false })

const sections = computed(() => auditPayloadSections(props.detail))

function actorLabel(detail: AuditLogDetail): string {
  return [
    detail.actor_type,
    detail.actor_role_code,
    detail.actor_user_id ? `#${detail.actor_user_id}` : null,
  ].filter(Boolean).join(' / ')
}
</script>

<template>
  <el-drawer v-model="visible" title="Audit Payload" size="720px" class="audit-payload-drawer">
    <el-alert v-if="error" class="drawer-alert" type="error" show-icon :closable="false" :title="error" />

    <el-skeleton v-if="loading" :rows="8" animated />

    <template v-else-if="detail">
      <el-descriptions class="payload-basic" :column="1" border>
        <el-descriptions-item label="ID">
          {{ detail.id }}
        </el-descriptions-item>
        <el-descriptions-item label="Created">
          {{ detail.created_at }}
        </el-descriptions-item>
        <el-descriptions-item label="Actor">
          {{ actorLabel(detail) || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="Request">
          {{ detail.request_id || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="IP">
          {{ detail.ip_address || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="Method">
          {{ detail.method || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="Path">
          {{ detail.path || '-' }}
        </el-descriptions-item>
      </el-descriptions>

      <section v-for="section in sections" :key="section.key" class="payload-section">
        <h3>{{ section.title }}</h3>
        <pre v-if="section.value" class="payload-json">{{ formatAuditPayload(section.value) }}</pre>
        <el-empty v-else :description="`${section.title} is empty`" :image-size="48" />
      </section>
    </template>

    <el-empty v-else description="No payload selected" />
  </el-drawer>
</template>

<style scoped lang="scss">
.drawer-alert {
  margin-bottom: 12px;
}

.payload-basic {
  margin-bottom: 16px;
}

.payload-section {
  margin-top: 16px;

  h3 {
    margin: 0 0 8px;
    font-size: 15px;
    font-weight: 600;
  }
}

.payload-json {
  max-height: 320px;
  padding: 12px;
  overflow: auto;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 12px;
  line-height: 1.6;
  word-break: break-word;
  white-space: pre-wrap;
  background: var(--el-fill-color-light);
  border: 1px solid var(--el-border-color-lighter);
  border-radius: 6px;
}

@media (width <= 768px) {
  :deep(.el-drawer) {
    width: 100% !important;
  }
}
</style>
