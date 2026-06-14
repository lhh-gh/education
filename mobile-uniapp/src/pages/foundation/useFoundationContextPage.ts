import { computed, reactive } from 'vue'
import type { MobileFoundationContext } from '@/api/foundation/types'

export type FoundationPageStatus = 'loading' | 'success' | 'empty' | 'forbidden' | 'error'

export interface FoundationPageOptions<T extends MobileFoundationContext> {
  emptyMessage: (context: T) => string | null
  forbiddenMessage?: (error: unknown) => string | null
}

export interface FoundationPageState<T extends MobileFoundationContext> {
  status: FoundationPageStatus
  context: T | null
  message: string
}

export function createFoundationContextPage<T extends MobileFoundationContext>(
  loader: () => Promise<T>,
  options: FoundationPageOptions<T>
) {
  const state = reactive<FoundationPageState<T>>({
    status: 'loading',
    context: null,
    message: '',
  })

  const enabledFeatureCodes = computed(() => Object.entries(state.context?.feature_flags || {})
    .filter(([, enabled]) => enabled)
    .map(([featureCode]) => featureCode))

  const currentCampusName = computed(() => state.context?.campus_scopes.find((campus) => campus.is_current)?.campus_name || '')

  async function load(): Promise<void> {
    state.status = 'loading'
    state.message = ''

    try {
      const context = await loader()
      const emptyMessage = options.emptyMessage(context)
      state.context = context
      state.status = emptyMessage === null ? 'success' : 'empty'
      state.message = emptyMessage || ''
    } catch (error) {
      state.context = null
      state.status = isForbidden(error) ? 'forbidden' : 'error'
      state.message = options.forbiddenMessage?.(error) || errorMessage(error)
    }
  }

  async function retry(): Promise<void> {
    await load()
  }

  async function refresh(): Promise<void> {
    try {
      await load()
    } finally {
      uni.stopPullDownRefresh?.()
    }
  }

  return {
    state,
    enabledFeatureCodes,
    currentCampusName,
    load,
    retry,
    refresh,
  }
}

function isForbidden(error: unknown): boolean {
  const code = (error as { code?: number })?.code

  return code === 401 || code === 403
}

function errorMessage(error: unknown): string {
  return (error as { message?: string })?.message || 'Request failed'
}
