<script setup lang="ts">
defineOptions({ name: 'EducationContentMaterialAttachmentList' })

const rows = ref<Array<{ id: number, file_name: string, file_type: string, permission: string }>>([])
</script>

<template>
  <div class="mine-layout pt-3">
    <el-card shadow="never">
      <template #header>
        <span>Material Attachments</span>
      </template>
      <el-alert class="mb-3" title="Attachment access follows material version permissions." type="info" :closable="false" />
      <el-table :data="rows" row-key="id">
        <el-table-column prop="file_name" label="File" min-width="180" />
        <el-table-column prop="file_type" label="Type" width="120" />
        <el-table-column prop="permission" label="Permission" width="160" />
        <template #empty>
          <el-empty description="No attachments" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>
