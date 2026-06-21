import type { MineResult } from '../foundation/types.ts'
import type { ContentPage, ContentScopedParams, MaterialAttachmentRow } from './types.ts'
import { contentGetOptions } from './types.ts'

export function pageMaterialAttachments(params: ContentScopedParams): Promise<MineResult<ContentPage<MaterialAttachmentRow>>> {
  return useHttp().get('/admin/education/content/attachments', contentGetOptions(params))
}
