<script setup lang="ts">
import type { NoticePageParams, NoticeRecord } from '../../api/academic/notice.ts'
import { pageNotices, publishNotice, withdrawNotice } from '../../api/academic/notice.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import NoticeDetailDrawer from './components/NoticeDetailDrawer.vue'
import NoticeForm from './components/NoticeForm.vue'
import NoticeReceiptDrawer from './components/NoticeReceiptDrawer.vue'
import { canEditNotice, canPublishNotice, canWithdrawNotice, noticePriorityLabel, noticePriorityType, noticeStatusLabel, noticeStatusType, noticeTargetTypeLabel, noticeTypeLabel } from './noticeRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { useEducationScope } from '@/composables/education/useEducationScope.ts'

defineOptions({ name: 'EducationAcademicNoticeList' })

const { scope } = useEducationScope()

const message = useMessage()
const loading = ref(false)
const rows = ref<NoticeRecord[]>([])
const total = ref(0)
const errorText = ref('')
const formVisible = ref(false)
const receiptVisible = ref(false)
const detailVisible = ref(false)
const current = ref<NoticeRecord | null>(null)
const search = reactive<NoticePageParams>(defaultSearch())

const canCreate = computed(() => hasAuth('education:academic:notice:create'))
const canUpdate = computed(() => hasAuth('education:academic:notice:update'))
const canPublish = computed(() => hasAuth('education:academic:notice:publish'))
const canWithdraw = computed(() => hasAuth('education:academic:notice:withdraw'))
const canReceipt = computed(() => hasAuth('education:academic:notice:receipt'))
const canDetail = computed(() => hasAuth('education:academic:notice:detail'))

function defaultSearch(): NoticePageParams {
  return { page: 1, pageSize: 20, notice_type: undefined, target_type: undefined, status: undefined, keyword: '' }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageNotices(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? '通知列表加载失败'
  }
  finally {
    loading.value = false
  }
}

function handleSearch() {
  search.page = 1
  loadRows()
}

function handleReset() {
  Object.assign(search, defaultSearch())
  loadRows()
}

function openCreate() {
  current.value = null
  formVisible.value = true
}

function openEdit(row: NoticeRecord) {
  current.value = row
  formVisible.value = true
}

function openReceipts(row: NoticeRecord) {
  current.value = row
  receiptVisible.value = true
}

function openDetail(row: NoticeRecord) {
  current.value = row
  detailVisible.value = true
}

async function handlePublish(row: NoticeRecord) {
  try {
    await publishNotice(row.id, { tenant_id: scope.tenant_id })
    message.success('通知已发布')
    loadRows()
  }
  catch (error: any) {
    message.error(error?.message ?? '通知发布失败')
  }
}

async function handleWithdraw(row: NoticeRecord) {
  try {
    const response = await withdrawNotice(row.id, { tenant_id: scope.tenant_id, withdraw_reason: '后台撤回' })
    rows.value = rows.value.map(item => item.id === row.id ? response.data : item)
    message.success('通知已撤回')
  }
  catch (error: any) {
    message.error(error?.message ?? '通知撤回失败')
  }
}

function onSaved() {
  loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-academic-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>通知管理</span>
          <el-button v-if="canCreate" type="primary" @click="openCreate">
            新增
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="类型">
          <el-select v-model="search.notice_type" clearable style="width: 150px;">
            <el-option label="教务" value="academic" />
            <el-option label="活动" value="activity" />
            <el-option label="费用" value="fee" />
            <el-option label="系统" value="system" />
          </el-select>
        </el-form-item>
        <el-form-item label="对象">
          <el-select v-model="search.target_type" clearable style="width: 150px;">
            <el-option label="全部" value="all" />
            <el-option label="校区" value="campus" />
            <el-option label="班级" value="class" />
            <el-option label="学员" value="student" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable style="width: 150px;">
            <el-option label="草稿" value="draft" />
            <el-option label="已发布" value="published" />
            <el-option label="已撤回" value="withdrawn" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键字">
          <el-input v-model="search.keyword" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            查询
          </el-button>
          <el-button @click="handleReset">
            重置
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="notice_no" label="通知编号" width="190" />
        <el-table-column prop="title" label="标题" min-width="180" show-overflow-tooltip />
        <el-table-column label="类型" width="110"><template #default="{ row }">{{ noticeTypeLabel(row.notice_type) }}</template></el-table-column>
        <el-table-column label="对象" width="110"><template #default="{ row }">{{ noticeTargetTypeLabel(row.target_type) }}</template></el-table-column>
        <el-table-column label="优先级" width="120">
          <template #default="{ row }">
            <el-tag :type="noticePriorityType(row.priority)">
              {{ noticePriorityLabel(row.priority) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="noticeStatusType(row.status)">
              {{ noticeStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="receipt_count" label="接收数" width="100" />
        <el-table-column prop="read_count" label="已读" width="90" />
        <el-table-column prop="published_at" label="发布时间" width="180" />
        <el-table-column prop="updated_at" label="更新时间" width="180" />
        <el-table-column label="操作" fixed="right" width="260">
          <template #default="{ row }">
            <el-button v-if="canDetail" link type="primary" @click="openDetail(row)">
              详情
            </el-button>
            <el-button v-if="canEditNotice(row, canUpdate)" link type="primary" @click="openEdit(row)">
              编辑
            </el-button>
            <el-button v-if="canPublishNotice(row, canPublish)" link type="success" @click="handlePublish(row)">
              发布
            </el-button>
            <el-button v-if="canWithdrawNotice(row, canWithdraw)" link type="warning" @click="handleWithdraw(row)">
              撤回
            </el-button>
            <el-button v-if="canReceipt" link @click="openReceipts(row)">
              回执
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无通知" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <NoticeForm v-model="formVisible" :row="current" :tenant-id="scope.tenant_id" @saved="onSaved" />
    <NoticeReceiptDrawer v-model="receiptVisible" :notice="current" :tenant-id="scope.tenant_id" />
    <NoticeDetailDrawer v-model="detailVisible" :row="current" />
  </div>
</template>

<style scoped lang="scss">
.education-academic-page {
  .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .page-alert,
  .search-form {
    margin-bottom: 12px;
  }

  .page-pagination {
    justify-content: flex-end;
    margin-top: 16px;
  }
}
</style>
