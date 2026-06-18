<script setup lang="ts">
import type { LearningMaterialPayload } from '../../../api/content/material.ts'

const model = defineModel<LearningMaterialPayload>({ required: true })
</script>

<template>
  <el-form label-width="120px" :model="model">
    <el-form-item label="Code" required>
      <el-input v-model="model.material_code" placeholder="MAT-001" />
    </el-form-item>
    <el-form-item label="Name" required>
      <el-input v-model="model.material_name" placeholder="Line practice" />
    </el-form-item>
    <el-form-item label="Course ID">
      <el-input-number v-model="model.course_id" :min="1" controls-position="right" />
    </el-form-item>
    <el-form-item label="Type" required>
      <el-select v-model="model.material_type">
        <el-option label="Worksheet" value="worksheet" />
        <el-option label="Video" value="video" />
        <el-option label="File" value="file" />
      </el-select>
    </el-form-item>
    <el-form-item label="Visibility">
      <el-switch v-model="model.guardian_visible" active-text="Guardian visible" inactive-text="Internal only" />
    </el-form-item>
    <el-form-item label="Summary">
      <el-input v-model="model.summary" type="textarea" :rows="3" />
    </el-form-item>
  </el-form>
</template>
