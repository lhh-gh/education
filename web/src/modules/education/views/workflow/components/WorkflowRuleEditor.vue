<script setup lang="ts">
defineOptions({ name: 'EducationWorkflowRuleEditor' })

const model = defineModel<Record<string, unknown>>({ default: () => ({}) })
</script>

<template>
  <el-form label-width="120px">
    <el-form-item label="规则编码">
      <el-input v-model="model.rule_code" />
    </el-form-item>
    <el-form-item label="规则名称">
      <el-input v-model="model.rule_name" />
    </el-form-item>
    <el-form-item label="事件类型">
      <el-input v-model="model.event_type" />
    </el-form-item>
  </el-form>
</template>
