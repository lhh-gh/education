<script setup lang="ts">
defineOptions({ name: 'EducationWorkflowTaskCommentPanel' })

const content = defineModel<string>({ default: '' })
</script>

<template>
  <el-input v-model="content" type="textarea" :rows="4" placeholder="Comment" />
</template>
