<script setup lang="ts">
import type { FamilyMessageRecord } from '../../../api/family/message.ts'
import { getFamilyMessageThread, sendFamilyMessage } from '../../../api/family/message.ts'
import { familySenderLabel, messageReplyPayload } from '../familyRules.ts'

const props = defineProps<{ thread?: { student_id: number, thread_id: string } }>()
const visible = defineModel<boolean>({ default: false })
const rows = ref<FamilyMessageRecord[]>([])
const content = ref('')
const loading = ref(false)

async function loadRows() {
  if (!props.thread) {
    rows.value = []
    return
  }
  loading.value = true
  try {
    const response = await getFamilyMessageThread(props.thread)
    rows.value = response.data
  }
  finally {
    loading.value = false
  }
}

async function reply() {
  if (!props.thread || !content.value.trim()) {
    return
  }
  await sendFamilyMessage(messageReplyPayload(props.thread, content.value.trim()))
  content.value = ''
  await loadRows()
}

watch(() => props.thread, loadRows)
</script>

<template>
  <el-drawer v-model="visible" title="消息会话" size="520px">
    <el-table v-loading="loading" :data="rows" row-key="id">
      <el-table-column label="发送方" width="110">
        <template #default="{ row }">
          {{ familySenderLabel(row.sender_type) }}
        </template>
      </el-table-column>
      <el-table-column prop="content" label="内容" min-width="220" />
      <template #empty>
        <el-empty description="暂无消息" />
      </template>
    </el-table>
    <div class="reply-box">
      <el-input v-model="content" type="textarea" :rows="3" />
      <el-button type="primary" @click="reply">
        回复
      </el-button>
    </div>
  </el-drawer>
</template>

<style scoped>
.reply-box {
  display: grid;
  gap: 8px;
  margin-top: 12px;
}
</style>
