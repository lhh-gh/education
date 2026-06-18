<script setup lang="ts">
defineProps<{ records?: Array<{ id: number, follow_type: string, content: string, created_at?: string }> }>()
</script>

<template>
  <el-timeline>
    <el-timeline-item v-for="record in records ?? []" :key="record.id" :timestamp="record.created_at">
      <strong>{{ record.follow_type }}</strong>
      <p>{{ record.content }}</p>
    </el-timeline-item>
  </el-timeline>
</template>
