import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import { describe, expect, it } from 'vitest'

const pages = [
  {
    file: 'ServicePackageList.vue',
    title: '服务包',
    auth: 'education:standards:package:save',
    empty: '暂无服务包',
    markers: ['saveServicePackage', '服务包编码', '家长可见'],
  },
  {
    file: 'StageGoalEditor.vue',
    title: '阶段目标',
    auth: 'education:standards:stage-goal:save',
    empty: '暂无阶段目标',
    markers: ['saveStageGoal', '目标内容', '能力点'],
  },
  {
    file: 'AbilityPointList.vue',
    title: '能力点',
    auth: 'education:standards:ability:save',
    empty: '暂无能力点',
    markers: ['saveAbilityPoint', '能力分组', '能力说明'],
  },
  {
    file: 'TrialStandardEditor.vue',
    title: '试听标准',
    auth: 'education:standards:trial:save',
    empty: '暂无试听标准',
    markers: ['saveTrialStandard', '评价项目', '家长可见'],
  },
  {
    file: 'DeliveryStandardEditor.vue',
    title: '交付标准',
    auth: 'education:standards:delivery:save',
    empty: '暂无交付标准',
    markers: ['saveDeliveryStandard', '课型', '交付内容'],
  },
  {
    file: 'ServiceTemplateList.vue',
    title: '服务模板',
    auth: 'education:standards:template:save',
    empty: '暂无服务模板',
    markers: ['saveServiceTemplateSet', '模板内容', '模板类型'],
  },
  {
    file: 'CourseFeedbackList.vue',
    title: '课程反馈',
    auth: 'education:standards:feedback:save',
    empty: '暂无课程反馈',
    markers: ['saveCourseFeedbackRecord', '反馈类型', '反馈内容'],
  },
  {
    file: 'CourseQualityDashboard.vue',
    title: '质量看板',
    empty: '暂无质量指标',
    markers: ['getCourseQualityMetrics', '平均分', '反馈数'],
  },
  {
    file: 'StandardVersionList.vue',
    title: '标准版本',
    auth: 'education:standards:version:publish',
    empty: '暂无标准版本',
    markers: ['publishStandardVersion', 'saveLocalizationOverride', '本地化'],
  },
  {
    file: 'StandardReviewList.vue',
    title: '标准评审',
    auth: 'education:standards:review:handle',
    empty: '暂无评审记录',
    markers: ['reviewStandardVersion', '通过', '驳回'],
  },
]

describe('standards mineadmin alignment', () => {
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
})
