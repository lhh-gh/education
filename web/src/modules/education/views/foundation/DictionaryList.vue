<script setup lang="ts">
import type {
  ConfigOwnerType,
  DictItemPageParams,
  DictItemRecord,
  DictTypePageParams,
  DictTypeRecord,
} from '../../api/foundation/dictionary.ts'
import {
  deleteDictItem,
  deleteDictType,
  pageDictItems,
  pageDictTypes,
  updateDictItemStatus,
  updateDictTypeStatus,
} from '../../api/foundation/dictionary.ts'
import useUserStore from '@/store/modules/useUserStore.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import {
  configOwnerTypeLabel,
  defaultDictItemSearch,
  defaultDictTypeSearch,
  dictionaryActionsByPermission,
  dictionaryItemParamsForType,
  dictionaryOwnerTypeOptions,
  extractApiErrorMessage,
  foundationStatusLabel,
  normalizeDictTypeSearch,
} from './actionRules.ts'
import DictItemForm from './components/DictItemForm.vue'
import DictTypeForm from './components/DictTypeForm.vue'
import { useMessage } from '@/hooks/useMessage.ts'

defineOptions({ name: 'EducationFoundationDictionaryList' })

const message = useMessage()
const userStore = useUserStore()
const typeLoading = ref(false)
const itemLoading = ref(false)
const typeDialogVisible = ref(false)
const itemDialogVisible = ref(false)
const typeDialogMode = ref<'create' | 'edit'>('create')
const itemDialogMode = ref<'create' | 'edit'>('create')
const currentType = ref<DictTypeRecord | null>(null)
const currentItem = ref<DictItemRecord | null>(null)
const selectedType = ref<DictTypeRecord | null>(null)
const typeRows = ref<DictTypeRecord[]>([])
const itemRows = ref<DictItemRecord[]>([])
const typeTotal = ref(0)
const itemTotal = ref(0)
const typeError = ref('')
const itemError = ref('')
const typeSearch = reactive<DictTypePageParams>(defaultDictTypeSearch())
const itemSearch = reactive<DictItemPageParams>(defaultDictItemSearch())

const permissions = computed(() => userStore.getPermissions())
const isPlatformContext = computed(() => {
  const roles = userStore.getRoles()
  return permissions.value.includes('*')
    || roles.includes('SuperAdmin')
    || roles.includes('education_platform_operator')
    || roles.includes('platform_operator')
    || roles.includes('platform_super_admin')
    || hasAuth('education:foundation:tenant:page')
})
const ownerOptions = computed(() => dictionaryOwnerTypeOptions(isPlatformContext.value))
const canCreateType = computed(() => dictionaryActionsByPermission(permissions.value, false).canCreateType)
const canCreateItem = computed(() => Boolean(selectedType.value) && dictionaryActionsByPermission(permissions.value, Boolean(selectedType.value?.is_locked)).canCreateItem)

watch(isPlatformContext, (platform) => {
  if (!platform && typeSearch.owner_type !== 'tenant') {
    typeSearch.owner_type = 'tenant'
  }
}, { immediate: true })

watch(() => typeSearch.owner_type, (ownerType) => {
  if (ownerType === 'system') {
    typeSearch.tenant_id = undefined
  }
})

function normalizeTypeSearch(): DictTypePageParams {
  return normalizeDictTypeSearch(typeSearch)
}

function ownerTypeLabel(ownerType: ConfigOwnerType): string {
  return configOwnerTypeLabel(ownerType)
}

function statusLabel(status: string): string {
  return foundationStatusLabel(status as 'enabled' | 'disabled')
}

function typeActions(row: DictTypeRecord) {
  return dictionaryActionsByPermission(permissions.value, row.is_locked, row.status)
}

function itemActions(row: DictItemRecord) {
  return dictionaryActionsByPermission(permissions.value, Boolean(selectedType.value?.is_locked), undefined, row.status)
}

async function loadTypes() {
  typeLoading.value = true
  try {
    const response = await pageDictTypes(normalizeTypeSearch())
    typeRows.value = response.data.list
    typeTotal.value = response.data.total
    typeError.value = ''

    const previousId = selectedType.value?.id
    selectedType.value = typeRows.value.find(row => row.id === previousId) ?? typeRows.value[0] ?? null
    await loadItems()
  }
  catch (error: any) {
    typeError.value = error?.message ?? '字典列表加载失败'
    typeError.value = extractApiErrorMessage(error, typeError.value)
    message.error(typeError.value)
  }
  finally {
    typeLoading.value = false
  }
}

async function loadItems() {
  if (!selectedType.value) {
    itemRows.value = []
    itemTotal.value = 0
    return
  }

  itemLoading.value = true
  try {
    const response = await pageDictItems(dictionaryItemParamsForType(itemSearch, selectedType.value))
    itemRows.value = response.data.list
    itemTotal.value = response.data.total
    itemError.value = ''
  }
  catch (error: any) {
    itemError.value = error?.message ?? '字典项加载失败'
    itemError.value = extractApiErrorMessage(error, itemError.value)
    message.error(itemError.value)
  }
  finally {
    itemLoading.value = false
  }
}

function selectType(row?: DictTypeRecord) {
  selectedType.value = row ?? null
  itemSearch.page = 1
  loadItems()
}

function handleTypeSearch() {
  typeSearch.page = 1
  loadTypes()
}

function handleTypeReset() {
  Object.assign(typeSearch, defaultDictTypeSearch(isPlatformContext.value))
  loadTypes()
}

function handleItemSearch() {
  itemSearch.page = 1
  loadItems()
}

function handleItemReset() {
  Object.assign(itemSearch, defaultDictItemSearch())
  loadItems()
}

function openCreateType() {
  typeDialogMode.value = 'create'
  currentType.value = null
  typeDialogVisible.value = true
}

function openEditType(row: DictTypeRecord) {
  typeDialogMode.value = 'edit'
  currentType.value = row
  typeDialogVisible.value = true
}

function openCreateItem() {
  if (!selectedType.value) {
    return
  }
  itemDialogMode.value = 'create'
  currentItem.value = null
  itemDialogVisible.value = true
}

function openEditItem(row: DictItemRecord) {
  itemDialogMode.value = 'edit'
  currentItem.value = row
  itemDialogVisible.value = true
}

async function changeTypeStatus(row: DictTypeRecord) {
  try {
    const nextStatus = row.status === 'enabled' ? 'disabled' : 'enabled'
    await updateDictTypeStatus(row.id, nextStatus)
    await loadTypes()
  }
  catch (error: any) {
    message.error(extractApiErrorMessage(error, '字典状态更新失败'))
  }
}

async function changeItemStatus(row: DictItemRecord) {
  try {
    const nextStatus = row.status === 'enabled' ? 'disabled' : 'enabled'
    await updateDictItemStatus(row.id, nextStatus)
    await loadItems()
  }
  catch (error: any) {
    message.error(extractApiErrorMessage(error, '字典项状态更新失败'))
  }
}

async function removeType(row: DictTypeRecord) {
  try {
    await message.confirm('确认删除该字典？')
    await deleteDictType(row.id)
    await loadTypes()
  }
  catch (error: any) {
    message.error(extractApiErrorMessage(error, '字典删除失败'))
  }
}

async function removeItem(row: DictItemRecord) {
  try {
    await message.confirm('确认删除该字典项？')
    await deleteDictItem(row.id)
    await loadItems()
  }
  catch (error: any) {
    message.error(extractApiErrorMessage(error, '字典项删除失败'))
  }
}

function onTypeFormSuccess() {
  typeDialogVisible.value = false
  loadTypes()
}

function onItemFormSuccess() {
  itemDialogVisible.value = false
  loadItems()
}

onMounted(loadTypes)
</script>

<template>
  <div class="mine-layout education-foundation-page dictionary-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>字典配置</span>
          <el-button v-if="canCreateType" type="primary" @click="openCreateType">
            新建字典
          </el-button>
        </div>
      </template>

      <el-alert v-if="typeError" class="page-alert" type="error" show-icon :closable="false" :title="typeError" />

      <el-form :inline="true" :model="typeSearch" class="search-form">
        <el-form-item label="归属">
          <el-select v-model="typeSearch.owner_type" clearable :disabled="!isPlatformContext" placeholder="全部" style="width: 120px;">
            <el-option v-for="option in ownerOptions" :key="option.value" :label="option.label" :value="option.value" />
          </el-select>
        </el-form-item>
        <el-form-item v-if="isPlatformContext && typeSearch.owner_type === 'tenant'" label="机构ID">
          <el-input-number v-model="typeSearch.tenant_id" :min="1" :controls="false" placeholder="机构ID" />
        </el-form-item>
        <el-form-item label="关键字">
          <el-input v-model="typeSearch.keyword" clearable placeholder="字典编码/名称" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="typeSearch.status" clearable placeholder="全部" style="width: 120px;">
            <el-option label="启用" value="enabled" />
            <el-option label="停用" value="disabled" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleTypeSearch">
            查询
          </el-button>
        </el-form-item>
        <el-form-item>
          <el-button @click="handleTypeReset">
            重置
          </el-button>
        </el-form-item>
      </el-form>

      <el-table
        v-loading="typeLoading"
        :data="typeRows"
        row-key="id"
        highlight-current-row
        @current-change="selectType"
      >
        <el-table-column prop="owner_type" label="归属" width="90">
          <template #default="{ row }">
            {{ ownerTypeLabel(row.owner_type) }}
          </template>
        </el-table-column>
        <el-table-column prop="tenant_id" label="机构ID" width="100" />
        <el-table-column prop="code" label="编码" min-width="160" />
        <el-table-column prop="name" label="名称" min-width="150" />
        <el-table-column prop="status" label="状态" width="90">
          <template #default="{ row }">
            <el-tag :type="row.status === 'enabled' ? 'success' : 'info'">
              {{ statusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="item_count" label="条目数" width="90" />
        <el-table-column prop="sort_order" label="排序" width="90" />
        <el-table-column prop="is_locked" label="锁定" width="90">
          <template #default="{ row }">
            <el-tag :type="row.is_locked ? 'warning' : 'info'">
              {{ row.is_locked ? '是' : '否' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="updated_at" label="更新时间" width="180" />
        <el-table-column label="操作" fixed="right" width="230">
          <template #default="{ row }">
            <el-button v-if="typeActions(row).canEditType" link type="primary" @click="openEditType(row)">
              编辑
            </el-button>
            <el-button v-if="typeActions(row).typeStatusAction" link type="primary" @click="changeTypeStatus(row)">
              {{ row.status === 'enabled' ? '停用' : '启用' }}
            </el-button>
            <el-button v-if="typeActions(row).canDeleteType" link type="danger" @click="removeType(row)">
              删除
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无字典" />
        </template>
      </el-table>

      <el-pagination
        v-model:current-page="typeSearch.page"
        v-model:page-size="typeSearch.page_size"
        class="page-pagination"
        layout="total, sizes, prev, pager, next"
        :total="typeTotal"
        @change="loadTypes"
      />

      <div class="item-panel">
        <div class="panel-header">
          <div>
            <span class="panel-title">字典项</span>
            <span v-if="selectedType" class="panel-code">{{ selectedType.code }}</span>
          </div>
          <el-button v-if="canCreateItem" type="primary" @click="openCreateItem">
            新建字典项
          </el-button>
        </div>

        <el-alert v-if="itemError" class="page-alert" type="error" show-icon :closable="false" :title="itemError" />

        <el-form :inline="true" :model="itemSearch" class="search-form">
          <el-form-item label="关键字">
            <el-input v-model="itemSearch.keyword" clearable placeholder="名称/值" :disabled="!selectedType" />
          </el-form-item>
          <el-form-item label="状态">
            <el-select v-model="itemSearch.status" clearable placeholder="全部" :disabled="!selectedType" style="width: 120px;">
              <el-option label="启用" value="enabled" />
              <el-option label="停用" value="disabled" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" :disabled="!selectedType" @click="handleItemSearch">
              查询
            </el-button>
          </el-form-item>
          <el-form-item>
            <el-button :disabled="!selectedType" @click="handleItemReset">
              重置
            </el-button>
          </el-form-item>
        </el-form>

        <el-table v-loading="itemLoading" :data="itemRows" row-key="id">
          <el-table-column prop="label" label="显示名称" min-width="150" />
          <el-table-column prop="value" label="值" min-width="140" />
          <el-table-column prop="color" label="颜色" width="120">
            <template #default="{ row }">
              <span v-if="row.color" class="color-token">
                <span class="color-dot" :style="{ backgroundColor: row.color.startsWith('#') ? row.color : '' }" />
                {{ row.color }}
              </span>
            </template>
          </el-table-column>
          <el-table-column prop="sort_order" label="排序" width="90" />
          <el-table-column prop="status" label="状态" width="90">
            <template #default="{ row }">
              <el-tag :type="row.status === 'enabled' ? 'success' : 'info'">
                {{ statusLabel(row.status) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="is_default" label="默认" width="90">
            <template #default="{ row }">
              <el-tag :type="row.is_default ? 'success' : 'info'">
                {{ row.is_default ? '是' : '否' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="updated_at" label="更新时间" width="180" />
          <el-table-column label="操作" fixed="right" width="230">
            <template #default="{ row }">
              <el-button v-if="itemActions(row).canEditItem" link type="primary" @click="openEditItem(row)">
                编辑
              </el-button>
              <el-button v-if="itemActions(row).itemStatusAction" link type="primary" @click="changeItemStatus(row)">
                {{ row.status === 'enabled' ? '停用' : '启用' }}
              </el-button>
              <el-button v-if="itemActions(row).canDeleteItem" link type="danger" @click="removeItem(row)">
                删除
              </el-button>
            </template>
          </el-table-column>
          <template #empty>
            <el-empty :description="selectedType ? '暂无字典项' : '请选择字典'" />
          </template>
        </el-table>

        <el-pagination
          v-model:current-page="itemSearch.page"
          v-model:page-size="itemSearch.page_size"
          class="page-pagination"
          layout="total, sizes, prev, pager, next"
          :total="itemTotal"
          @change="loadItems"
        />
      </div>
    </el-card>

    <el-dialog v-model="typeDialogVisible" :title="typeDialogMode === 'create' ? '新建字典' : '编辑字典'" width="600px">
      <DictTypeForm
        :key="`${typeDialogMode}-${currentType?.id ?? 'new'}`"
        :mode="typeDialogMode"
        :data="currentType"
        :platform-context="isPlatformContext"
        @success="onTypeFormSuccess"
      />
    </el-dialog>

    <el-dialog v-model="itemDialogVisible" :title="itemDialogMode === 'create' ? '新建字典项' : '编辑字典项'" width="600px">
      <DictItemForm
        v-if="selectedType"
        :key="`${itemDialogMode}-${currentItem?.id ?? 'new'}-${selectedType.id}`"
        :mode="itemDialogMode"
        :dict-type-id="selectedType.id"
        :data="currentItem"
        @success="onItemFormSuccess"
      />
    </el-dialog>
  </div>
</template>

<style scoped lang="scss">
.education-foundation-page {
  .page-header,
  .panel-header {
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

  .item-panel {
    padding-top: 20px;
    margin-top: 20px;
    border-top: 1px solid var(--el-border-color-lighter);
  }

  .panel-header {
    margin-bottom: 12px;
  }

  .panel-title {
    font-weight: 600;
  }

  .panel-code {
    margin-left: 8px;
    color: var(--el-text-color-secondary);
  }

  .color-token {
    display: inline-flex;
    gap: 6px;
    align-items: center;
  }

  .color-dot {
    width: 10px;
    height: 10px;
    border: 1px solid var(--el-border-color);
    border-radius: 50%;
  }
}
</style>
