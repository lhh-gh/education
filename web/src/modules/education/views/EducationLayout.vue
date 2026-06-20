<script setup lang="ts">
import EducationScopeContextBar from './components/EducationScopeContextBar.vue'
import { shouldShowEducationScopeContext } from './components/educationScopeContextRules.ts'

defineOptions({ name: 'EducationLayout' })

const route = useRoute()
const showScopeContext = computed(() => shouldShowEducationScopeContext(route.path))
</script>

<template>
  <div class="education-layout">
    <EducationScopeContextBar v-if="showScopeContext" />
    <RouterView />
  </div>
</template>
