<script setup lang="ts">
import { useRoute } from 'vue-router'
import { addFollowRecord, getLeadDetail } from '../../api/admissions/lead.ts'

defineOptions({ name: 'EducationAdmissionLeadDetail' })

const route = useRoute()
const loading = ref(false)
const detail = ref<any>(null)
const errorText = ref('')
const content = ref('')
const params = reactive({ tenant_id: undefined as number | undefined, campus_id: undefined as number | undefined })

async function loadDetail() {
  loading.value = true
  try {
    const response = await getLeadDetail(Number(route.params.id), params)
    detail.value = response.data
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Lead detail loading failed'
  }
  finally {
    loading.value = false
  }
}

async function saveFollow() {
  await addFollowRecord(Number(route.params.id), { ...params, follow_type: 'phone', content: content.value })
  content.value = ''
  await loadDetail()
}

onMounted(loadDetail)
</script>

<template>
  <div class="mine-layout admission-page pt-3">
    <el-card shadow="never">
      <template #header><div class="page-header"><span>Lead Detail</span><el-button type="primary" @click="saveFollow">Save Follow</el-button></div></template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-skeleton v-if="loading" :rows="5" animated />
      <el-descriptions v-else-if="detail" :column="2" border>
        <el-descriptions-item label="Lead No">{{ detail.lead_no }}</el-descriptions-item>
        <el-descriptions-item label="Contact">{{ detail.contact_name }}</el-descriptions-item>
        <el-descriptions-item label="Mobile">{{ detail.contact_mobile }}</el-descriptions-item>
        <el-descriptions-item label="Stage">{{ detail.stage }}</el-descriptions-item>
      </el-descriptions>
      <el-input v-model="content" class="mt-3" type="textarea" placeholder="Follow content" />
      <el-empty v-if="!loading && !detail" description="No lead detail" />
    </el-card>
  </div>
</template>
