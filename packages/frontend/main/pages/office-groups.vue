<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page data-page-office-groups :breadcrumbs="breadcrumbs">
    <v-card class="pb-4 pt-2">
      <div :class="$style.header">
        <div :class="[$style.headerItem, $style.headerName]">事業所グループ名</div>
        <div
          v-if="hasOfficeGroupsUpdatePermission"
          :class="[$style.headerItem, $style.headerActions]"
        >
          <span>操作</span>
          <v-tooltip bottom>
            <template #activator="{ on }">
              <v-btn icon text v-on="on">
                <v-icon>{{ $icons.help }}</v-icon>
              </v-btn>
            </template>
            <span>ドラッグ＆ドロップで並び替えができます。</span>
          </v-tooltip>
        </div>
      </div>
      <v-divider />
      <z-sortable-tree
        data-sortable-tree
        parent-key="parentOfficeGroupId"
        :is-sortable="hasOfficeGroupsUpdatePermission"
        :value="officeGroupsTree"
        @update="onUpdate"
      >
        <template #prepend>
          <v-icon>{{ $icons.office }}</v-icon>
        </template>
        <template #label="{ item }">{{ item.name }}</template>
        <template #append="{ item }">
          <!-- Vue 2.16.13 以降：この span 要素がないとこのブロックが表示されない場合がある -->
          <span>&nbsp;</span>
          <v-btn
            v-if="hasOfficeGroupsUpdatePermission"
            data-edit-btn
            icon
            nuxt
            ripple
            small
            :to="`/office-groups/${item.id}/edit`"
          >
            <v-icon>{{ $icons.edit }}</v-icon>
          </v-btn>
          <z-menu-button
            v-if="isAuthorized([permissions.createOfficeGroups, permissions.deleteOfficeGroups])"
            data-menu-button
          >
            <z-menu-button-item
              v-if="isAuthorized([permissions.createOfficeGroups]) && !item.parentOfficeGroupId"
              :icon="$icons.add"
              :to="`/office-groups/new?parentOfficeGroupId=${item.id}`"
            >
              <span>サブグループを追加</span>
            </z-menu-button-item>
            <z-menu-button-item
              v-if="isAuthorized([permissions.deleteOfficeGroups])"
              :icon="$icons.delete"
              @click.prevent="deleteOfficeGroup(item)"
            >
              <span>事業所グループを削除</span>
            </z-menu-button-item>
          </z-menu-button>
        </template>
      </z-sortable-tree>
      <z-fab
        v-if="isAuthorized([permissions.createOfficeGroups])"
        bottom
        data-fab
        fixed
        nuxt
        right
        to="/office-groups/new"
        :icon="$icons.add"
      />
    </v-card>
    <nuxt-child />
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent, provide } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { officeGroupsStoreKey, useOfficeGroupsStore } from '~/composables/stores/use-office-groups-store'
import { useAuth } from '~/composables/use-auth'
import { useBackgroundLoader } from '~/composables/use-background-loader'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { OfficeGroup } from '~/models/office-group'
import { Tree } from '~/models/tree'

export default defineComponent({
  name: 'OfficeGroupsPage',
  middleware: [auth(Permission.listOfficeGroups)],
  setup () {
    const { isAuthorized, permissions } = useAuth()
    const { $alert, $api, $confirm, $snackbar } = usePlugins()

    const officeGroupsStore = useOfficeGroupsStore()
    const { officeGroupsTree } = officeGroupsStore.state
    useBackgroundLoader(() => officeGroupsStore.getIndex())
    provide(officeGroupsStoreKey, officeGroupsStore)

    const deleteOfficeGroup = async (officeGroup: OfficeGroup) => {
      const confirmed = await $confirm.show({
        message: `事業所グループ「${officeGroup.name}」を削除します。\n\n本当によろしいですか？`,
        positive: '削除'
      })
      if (confirmed) {
        const id = officeGroup.id
        try {
          await $api.officeGroups.delete({ id })
          await catchErrorStack(() => officeGroupsStore.getIndex())
          $snackbar.success('事業所グループを削除しました。')
        } catch (error) {
          if (error instanceof Error) {
            $alert.error('事業所グループの削除に失敗しました。', error.stack)
          }
        }
      }
    }
    const onUpdate = async (args: { item: OfficeGroup, children: Tree<OfficeGroup> }) => {
      let i = 0
      const parentOfficeGroupId = args.item?.id
      const list = args.children.map(x => ({
        ...x.item,
        parentOfficeGroupId,
        sortOrder: ++i
      }))
      await $api.officeGroups.sort({ list })
    }
    const hasOfficeGroupsUpdatePermission = computed(() => isAuthorized.value([Permission.updateOfficeGroups]))
    return {
      ...useBreadcrumbs('officeGroups'),
      deleteOfficeGroup,
      isAuthorized,
      hasOfficeGroupsUpdatePermission,
      officeGroupsTree,
      onUpdate,
      permissions
    }
  },
  head: () => ({
    title: '事業所グループ'
  })
})
</script>

<style lang="scss" module>
.header {
  align-items: center;
  display: flex;
  height: 48px;
  padding: 0 8px 0 32px;
}

.headerItem {
  font-size: 13px;
}

.headerName {
  flex-grow: 1;
}

.headerActions {
  align-items: center;
  display: flex;
  justify-content: flex-end;
  text-align: right;
  width: 84px;
}
</style>
