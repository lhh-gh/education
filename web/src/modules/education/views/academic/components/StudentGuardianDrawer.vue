<script setup lang="ts">
import type { GuardianRecord, GuardianRelation, StudentGuardianPayload, StudentGuardianRecord } from '../../../api/academic/profile.ts'
import { listStudentGuardians, pageGuardians, saveStudentGuardians } from '../../../api/academic/profile.ts'
import { normalizePrimaryRelations, relationLabel } from '../actionRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'

const props = defineProps<{
  modelValue: boolean
  studentId?: number
  tenantId?: number
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  'success': []
}>()

const message = useMessage()
const loading = ref(false)
const submitting = ref(false)
const guardians = ref<GuardianRecord[]>([])
const relations = ref<StudentGuardianPayload[]>([])
const errorText = ref('')
const relationOptions: GuardianRelation[] = ['father', 'mother', 'grandfather', 'grandmother', 'guardian', 'other']

watch(() => props.modelValue, (visible) => {
  if (visible && props.studentId) {
    load()
  }
})

async function load() {
  if (!props.studentId) {
    return
  }
  loading.value = true
  try {
    const [relationResponse, guardianResponse] = await Promise.all([
      listStudentGuardians(props.studentId, props.tenantId),
      pageGuardians({ page: 1, page_size: 100, tenant_id: props.tenantId, status: 'enabled' }),
    ])
    relations.value = relationResponse.data.list.map((item: StudentGuardianRecord) => ({
      guardian_id: item.guardian_id,
      relation: item.relation,
      is_primary: item.is_primary,
      can_receive_notice: item.can_receive_notice,
      can_submit_leave: item.can_submit_leave,
      remark: item.remark,
    }))
    guardians.value = guardianResponse.data.list
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Guardian relations loading failed'
  }
  finally {
    loading.value = false
  }
}

function addRelation() {
  const first = guardians.value.find(guardian => !relations.value.some(item => item.guardian_id === guardian.id))
  if (!first) {
    return
  }
  relations.value.push({
    guardian_id: first.id,
    relation: 'guardian',
    is_primary: relations.value.length === 0,
    can_receive_notice: true,
    can_submit_leave: true,
  })
}

function close() {
  emit('update:modelValue', false)
}

async function submit() {
  if (!props.studentId) {
    return
  }
  submitting.value = true
  try {
    await saveStudentGuardians(props.studentId, normalizePrimaryRelations(relations.value), props.tenantId)
    emit('success')
    close()
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Guardian relations save failed'
    message.error(errorText.value)
  }
  finally {
    submitting.value = false
  }
}

defineExpose({ addRelation, load, submit, relations })
</script>

<template>
  <el-drawer :model-value="modelValue" title="Student Guardians" size="560px" @close="close">
    <el-alert v-if="errorText" class="drawer-alert" type="error" show-icon :closable="false" :title="errorText" />
    <el-table v-loading="loading" :data="relations" row-key="guardian_id">
      <el-table-column label="Guardian" min-width="160">
        <template #default="{ row }">
          <el-select v-model="row.guardian_id" filterable>
            <el-option v-for="guardian in guardians" :key="guardian.id" :label="`${guardian.name} ${guardian.mobile}`" :value="guardian.id" />
          </el-select>
        </template>
      </el-table-column>
      <el-table-column label="Relation" width="140">
        <template #default="{ row }">
          <el-select v-model="row.relation">
            <el-option v-for="value in relationOptions" :key="value" :label="relationLabel(value)" :value="value" />
          </el-select>
        </template>
      </el-table-column>
      <el-table-column label="Primary" width="100">
        <template #default="{ row }">
          <el-checkbox v-model="row.is_primary" />
        </template>
      </el-table-column>
      <template #empty>
        <el-empty description="No guardians" />
      </template>
    </el-table>
    <div class="drawer-actions">
      <el-button @click="addRelation">
        Add
      </el-button>
      <el-button type="primary" :loading="submitting" @click="submit">
        Save
      </el-button>
    </div>
  </el-drawer>
</template>

<style scoped lang="scss">
.drawer-alert {
  margin-bottom: 12px;
}

.drawer-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  margin-top: 16px;
}
</style>
