<script setup lang="ts">
import type { LearningMaterialPayload } from '../../../api/content/material.ts'

const model = defineModel<LearningMaterialPayload>({ required: true })
</script>

<template>
  <el-form label-width="120px" :model="model">
    <el-form-item label="资料编码" required>
      <el-input v-model="model.material_code" clearable placeholder="如 MAT-001" />
    </el-form-item>
    <el-form-item label="资料名称" required>
      <el-input v-model="model.material_name" clearable placeholder="请输入资料名称" />
    </el-form-item>
    <el-form-item label="课程 ID">
      <el-input-number v-model="model.course_id" :min="1" :controls="false" />
    </el-form-item>
    <el-form-item label="资料类型" required>
      <el-select v-model="model.material_type">
        <el-option label="练习单" value="worksheet" />
        <el-option label="视频" value="video" />
        <el-option label="文件" value="file" />
      </el-select>
    </el-form-item>
    <el-form-item label="可见范围">
      <el-switch v-model="model.guardian_visible" active-text="家长可见" inactive-text="内部使用" />
    </el-form-item>
    <el-form-item label="摘要">
      <el-input v-model="model.summary" type="textarea" :rows="3" placeholder="请输入资料摘要" />
    </el-form-item>
  </el-form>
</template>
