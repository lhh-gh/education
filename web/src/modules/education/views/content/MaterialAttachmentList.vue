<script setup lang="ts">
defineOptions({ name: 'EducationContentMaterialAttachmentList' })

const rows = ref<Array<{ id: number, file_name: string, file_type: string, permission: string }>>([])
</script>

<template>
  <div class="mine-layout pt-3">
    <el-card shadow="never">
      <template #header>
        <span>资料附件</span>
      </template>
      <el-alert class="mb-3" title="附件访问范围跟随资料版本权限。" type="info" :closable="false" />
      <el-table :data="rows" row-key="id">
        <el-table-column prop="file_name" label="文件名" min-width="180" />
        <el-table-column prop="file_type" label="类型" width="120" />
        <el-table-column prop="permission" label="权限范围" width="160" />
        <template #empty>
          <el-empty description="暂无附件" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>
