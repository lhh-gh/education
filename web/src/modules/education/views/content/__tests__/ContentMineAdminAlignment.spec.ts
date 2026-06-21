import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import { describe, expect, it } from 'vitest'

const pages = [
  {
    file: 'MaterialVersionList.vue',
    title: '资料版本',
    empty: '暂无资料版本',
    auth: 'education:content:version:create',
    markers: ['pageMaterialVersions', 'createMaterialVersion', 'v-loading="loading"', 'v-model:current-page="search.page"'],
  },
  {
    file: 'MaterialRelationEditor.vue',
    title: '资料关联',
    empty: '暂无关联资料',
    auth: 'education:content:relation:save',
    markers: ['pageMaterialRelations', 'saveMaterialRelations', '关联对象', '保存关联'],
  },
  {
    file: 'StudentWorkList.vue',
    title: '学生作品',
    empty: '暂无学生作品',
    auth: 'education:content:student-work:publish',
    markers: ['pageStudentWorks', 'publishStudentWork', 'withdrawStudentWork', 'v-model:current-page="search.page"'],
  },
  {
    file: 'ContentReviewList.vue',
    title: '内容审核',
    empty: '暂无审核记录',
    auth: 'education:content:review:handle',
    markers: ['pageContentReviews', 'reviewContent', '审核意见', '驳回'],
  },
  {
    file: 'MaterialUsageDashboard.vue',
    title: '使用看板',
    empty: '暂无资料指标',
    markers: ['getMaterialUsageMetrics', 'getStudentWorkMetrics', '教师使用', '作品指标'],
  },
  {
    file: 'ShowcaseList.vue',
    title: '成果展陈',
    empty: '暂无成果展陈',
    auth: 'education:content:showcase:save',
    markers: ['pageShowcases', 'saveShowcase', 'publishShowcase', 'withdrawShowcase'],
  },
]

const components = [
  { file: 'components/MaterialVersionDrawer.vue', markers: ['资料版本', '版本标题', '版本内容'] },
  { file: 'components/ShowcaseEditor.vue', markers: ['学生 ID', '阶段目标 ID', '展陈标题'] },
  { file: 'components/LearningMaterialForm.vue', markers: ['资料编码', '资料名称', '资料类型'] },
]

describe('content mineadmin alignment', () => {
  it.each(pages)('$file uses chinese mineadmin structure and permission guards', (page) => {
    const source = readFileSync(resolve(__dirname, `../${page.file}`), 'utf8')

    expect(source).toContain('mine-layout')
    expect(source).toContain('<el-card')
    expect(source).toContain(page.title)
    expect(source).toContain(page.empty)
    expect(source).toContain('<el-empty')
    for (const marker of page.markers) {
      expect(source).toContain(marker)
    }
    if (page.auth) {
      expect(source).toContain(`hasAuth('${page.auth}')`)
    }
  })

  it.each(components)('$file uses readable chinese form copy', (component) => {
    const source = readFileSync(resolve(__dirname, `../${component.file}`), 'utf8')

    for (const marker of component.markers) {
      expect(source).toContain(marker)
    }
  })
})
