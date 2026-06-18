<script setup lang="ts">
const emit = defineEmits<{ submit: [payload: { to_user_id: number, reason: string }] }>()
const model = defineModel<boolean>({ default: false })
const form = reactive({ to_user_id: 0, reason: '' })
</script>

<template>
  <el-drawer v-model="model" title="Assign Lead" size="420px">
    <el-form :model="form" label-width="100px">
      <el-form-item label="User"><el-input-number v-model="form.to_user_id" :min="1" :controls="false" /></el-form-item>
      <el-form-item label="Reason"><el-input v-model="form.reason" type="textarea" /></el-form-item>
    </el-form>
    <el-button type="primary" @click="emit('submit', { ...form })">Assign</el-button>
  </el-drawer>
</template>
