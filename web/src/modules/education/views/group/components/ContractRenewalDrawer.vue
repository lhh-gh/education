<script setup lang="ts">
const props = defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()

const visible = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})
</script>

<template>
  <el-drawer v-model="visible" title="Renewal" size="420px">
    <el-form label-width="112px">
      <el-form-item label="Contract ID">
        <el-input-number :min="1" />
      </el-form-item>
      <el-form-item label="Due Date">
        <el-date-picker value-format="YYYY-MM-DD" />
      </el-form-item>
      <el-form-item label="Result">
        <el-input type="textarea" />
      </el-form-item>
    </el-form>
  </el-drawer>
</template>
