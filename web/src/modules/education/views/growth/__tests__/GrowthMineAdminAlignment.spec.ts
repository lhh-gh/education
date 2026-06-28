import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import { describe, expect, it } from 'vitest'

const pages = [
  {
    file: 'GrowthWorkbench.vue',
    title: '增长工作台',
    empty: '暂无高意向线索',
    markers: ['getGrowthWorkbench', 'FollowupSuggestionPanel', 'v-loading="loading"', '跟进建议'],
  },
  {
    file: 'LeadScoreList.vue',
    title: '线索评分',
    empty: '暂无评分记录',
    auth: 'education:growth:score:recalculate',
    markers: ['recalculateLeadScore', 'v-loading="loading"', '重新计算'],
  },
  {
    file: 'AiTalkScriptWorkbench.vue',
    title: 'AI 话术',
    auth: 'education:growth:ai-script:generate',
    markers: ['generateAiTalkScript', 'confirmAiTalkScript', 'AI 不允许承诺自动优惠', '生成话术'],
  },
  {
    file: 'FollowupStrategyList.vue',
    title: '跟进策略',
    empty: '暂无跟进策略',
    auth: 'education:growth:strategy:save',
    markers: ['saveFollowupStrategy', '策略编码', '建议模板'],
  },
  {
    file: 'TrialConversionList.vue',
    title: '试听转化',
    empty: '暂无转化链路',
    markers: ['getTrialConversionLink', '加载转化链路', '转化链路'],
  },
  {
    file: 'ChannelRoiDashboard.vue',
    title: '渠道 ROI',
    empty: '暂无渠道 ROI 数据',
    markers: ['getChannelRoi', 'v-loading="loading"', '日期范围'],
  },
  {
    file: 'ConsultantMetricDashboard.vue',
    title: '顾问指标',
    empty: '暂无顾问指标',
    markers: ['getConsultantMetrics', 'v-loading="loading"', '转化率'],
  },
  {
    file: 'LossReasonReport.vue',
    title: '流失原因',
    empty: '暂无流失记录',
    auth: 'education:growth:loss:create',
    markers: ['saveLossReason', 'createLeadLossRecord', '标记流失'],
  },
]

const components = [
  { file: 'components/AiTalkScriptEditor.vue', markers: ['AI 不允许承诺自动优惠', '请先生成或编辑话术内容', '确认话术'] },
  { file: 'components/FollowupSuggestionPanel.vue', markers: ['跟进建议', '截止时间', '采纳'] },
  { file: 'components/LeadScoreFactorDrawer.vue', markers: ['评分因子', '因子名称', '因子值', '分值'] },
]

describe('growth mineadmin alignment', () => {
  it.each(pages)('$file uses chinese mineadmin structure and permission guards', (page) => {
    const source = readFileSync(resolve(__dirname, `../${page.file}`), 'utf8')

    expect(source).toContain('mine-layout')
    expect(source).toContain('<el-card')
    expect(source).toContain(page.title)
    if (page.empty) {
      expect(source).toContain(page.empty)
      expect(source).toContain('<el-empty')
    }
    for (const marker of page.markers) {
      expect(source).toContain(marker)
    }
    if (page.auth) {
      expect(source).toContain(`hasAuth('${page.auth}')`)
    }
  })

  it.each(components)('$file uses readable chinese component copy', (component) => {
    const source = readFileSync(resolve(__dirname, `../${component.file}`), 'utf8')

    for (const marker of component.markers) {
      expect(source).toContain(marker)
    }
  })
})
