import { defineConfig } from 'vitest/config'

export default defineConfig({
  test: {
    include: ['src/**/*.spec.ts'],
    exclude: ['tests/e2e/**', 'node_modules/**', 'dist/**'],
    environment: 'node',
  },
  resolve: {
    alias: {
      '@': new URL('./src', import.meta.url).pathname,
      '#': new URL('./types', import.meta.url).pathname,
      '~': new URL('./src/modules', import.meta.url).pathname,
    },
  },
})
