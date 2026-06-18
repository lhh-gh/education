<script setup lang="ts">
import type { CampusRecord } from '../../../api/foundation/campus.ts'
import type { UserProfileRecord } from '../../../api/foundation/userProfile.ts'
import { pageCampuses } from '../../../api/foundation/campus.ts'
import { getCampusScopes, saveCampusScopes } from '../../../api/foundation/userProfile.ts'
import { campusScopeSavePayload, campusScopeValidationError } from '../actionRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'

const props = defineProps<{
  profile: UserProfileRecord | null
  canSubmit: boolean
}>()

const emit = defineEmits<{
  success: []
}>()

const message = useMessage()
const loading = ref(false)
const submitting = ref(false)
const selectedCampusIds = ref<number[]>([])
const campusOptions = ref<CampusRecord[]>([])
const validationError = computed(() => props.profile
  ? campusScopeValidationError(props.profile.role_code, selectedCampusIds.value)
  : null)

async function loadScope() {
  if (!props.profile?.tenant_id) {
    selectedCampusIds.value = []
    campusOptions.value = []
    return
  }
  loading.value = true
  try {
    const [scopeResponse, campusResponse] = await Promise.all([
      getCampusScopes(props.profile.id),
      pageCampuses(props.profile.tenant_id, { page: 1, page_size: 200 }),
    ])
    selectedCampusIds.value = scopeResponse.data.campus_ids
    campusOptions.value = campusResponse.data.list
  }
  catch (error: any) {
    message.error(error?.message ?? 'Campus scope loading failed')
  }
  finally {
    loading.value = false
  }
}

async function submit() {
  if (!props.profile) {
    return
  }
  if (!props.canSubmit) {
    message.warning('No permission')
    return
  }
  if (validationError.value) {
    message.warning(validationError.value)
    return
  }
  submitting.value = true
  try {
    await saveCampusScopes(props.profile.id, campusScopeSavePayload(selectedCampusIds.value))
    emit('success')
  }
  catch (error: any) {
    message.error(error?.message ?? 'Campus scope save failed')
  }
  finally {
    submitting.value = false
  }
}

watch(() => props.profile?.id, loadScope, { immediate: true })
defineExpose({ loadScope, submit })
</script>

<template>
  <div v-loading="loading" class="campus-scope-form">
    <el-empty v-if="!profile" description="No profile selected" />
    <template v-else>
      <el-alert
        v-if="campusOptions.length === 0"
        class="scope-alert"
        type="info"
        :closable="false"
        title="No campus options"
      />
      <el-form label-width="112px">
        <el-form-item label="Campuses" :error="validationError ?? undefined">
          <el-select v-model="selectedCampusIds" class="scope-select" multiple filterable clearable>
            <el-option
              v-for="campus in campusOptions"
              :key="campus.id"
              :label="campus.name"
              :value="campus.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :disabled="!canSubmit" :loading="submitting" @click="submit">
            Save
          </el-button>
        </el-form-item>
      </el-form>
    </template>
  </div>
</template>

<style scoped lang="scss">
.campus-scope-form {
  min-height: 120px;
}

.scope-alert {
  margin-bottom: 12px;
}

.scope-select {
  width: 100%;
}
</style>
