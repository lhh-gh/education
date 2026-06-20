<script setup lang="ts">
const props = defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()

const visible = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})
</script>

<template>
  <el-drawer v-model="visible" title="续签处理" size="420px">
    <el-form label-width="112px">
      <el-form-item label="合同ID">
        <el-input-number :min="1" />
      </el-form-item>
      <el-form-item label="到期日期">
        <el-date-picker value-format="YYYY-MM-DD" />
      </el-form-item>
      <el-form-item label="处理结果">
        <el-input type="textarea" />
      </el-form-item>
    </el-form>
  </el-drawer>
</template>
