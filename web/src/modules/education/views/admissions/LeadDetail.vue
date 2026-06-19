<script setup lang="ts">
import { useRoute } from 'vue-router'
import { addFollowRecord, getLeadDetail } from '../../api/admissions/lead.ts'
import { admissionErrorText, admissionStageLabel } from './admissionRules.ts'

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
    errorText.value = admissionErrorText(error)
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
      <template #header><div class="page-header"><span>线索详情</span><el-button type="primary" @click="saveFollow">保存跟进</el-button></div></template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-skeleton v-if="loading" :rows="5" animated />
      <el-descriptions v-else-if="detail" :column="2" border>
        <el-descriptions-item label="线索编号">{{ detail.lead_no }}</el-descriptions-item>
        <el-descriptions-item label="联系人">{{ detail.contact_name }}</el-descriptions-item>
        <el-descriptions-item label="手机号">{{ detail.contact_mobile }}</el-descriptions-item>
        <el-descriptions-item label="阶段">{{ admissionStageLabel(detail.stage) }}</el-descriptions-item>
      </el-descriptions>
      <el-input v-model="content" class="mt-3" type="textarea" placeholder="跟进内容" />
      <el-empty v-if="!loading && !detail" description="暂无线索详情" />
    </el-card>
  </div>
</template>
