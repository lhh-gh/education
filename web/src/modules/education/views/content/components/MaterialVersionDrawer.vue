<script setup lang="ts">
import type { MaterialVersionPayload } from '../../../api/content/version.ts'

defineProps<{ materialId?: number }>()
const visible = defineModel<boolean>('visible', { default: false })
const model = defineModel<MaterialVersionPayload>('model', { required: true })
</script>

<template>
  <el-drawer v-model="visible" title="Material Version" size="420px">
    <el-alert v-if="materialId" title="Published versions are immutable; saving creates a draft version." type="info" :closable="false" class="mb-3" />
    <el-form label-width="96px" :model="model">
      <el-form-item label="Title" required>
        <el-input v-model="model.title" />
      </el-form-item>
      <el-form-item label="Content">
        <el-input v-model="model.content" type="textarea" :rows="8" />
      </el-form-item>
    </el-form>
    <slot name="footer" />
  </el-drawer>
</template>
