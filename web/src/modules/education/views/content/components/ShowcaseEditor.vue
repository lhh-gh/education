<script setup lang="ts">
import type { ShowcasePayload } from '../../../api/content/showcase.ts'

const model = defineModel<ShowcasePayload>({ required: true })
</script>

<template>
  <el-form label-width="120px" :model="model">
    <el-form-item label="学生 ID" required>
      <el-input-number v-model="model.student_id" :min="1" controls-position="right" />
    </el-form-item>
    <el-form-item label="阶段目标 ID">
      <el-input-number v-model="model.stage_goal_id" :min="1" controls-position="right" />
    </el-form-item>
    <el-form-item label="展陈标题" required>
      <el-input v-model="model.title" />
    </el-form-item>
    <el-form-item label="摘要">
      <el-input v-model="model.summary" type="textarea" :rows="4" />
    </el-form-item>
  </el-form>
</template>
