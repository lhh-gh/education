<script setup lang="ts">
import type { MaterialVersionPayload } from '../../../api/content/version.ts'

defineProps<{ materialId?: number }>()
const visible = defineModel<boolean>('visible', { default: false })
const model = defineModel<MaterialVersionPayload>('model', { required: true })
</script>

<template>
  <el-drawer v-model="visible" title="资料版本" size="420px">
    <el-alert
      v-if="materialId"
      title="保存后会生成新的草稿版本，已发布版本不会被直接覆盖。"
      type="info"
      :closable="false"
      class="mb-3"
    />
    <el-form label-width="96px" :model="model">
      <el-form-item label="版本标题" required>
        <el-input v-model="model.title" clearable placeholder="请输入版本标题" />
      </el-form-item>
      <el-form-item label="版本内容">
        <el-input v-model="model.content" type="textarea" :rows="8" placeholder="请输入版本内容" />
      </el-form-item>
    </el-form>
    <slot name="footer" />
  </el-drawer>
</template>
