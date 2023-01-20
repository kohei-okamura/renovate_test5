<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-table :items="items" :options="tableOptions">
    <template #item.subsidyType="{ item }">{{ resolveUserDwsSubsidyType(item.subsidyType) }}</template>
    <template #item.period="{ item }">
      <z-era-date short :value="item.period.start" />
      <span>〜</span>
      <z-era-date short :value="item.period.end" />
    </template>
  </z-data-table>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { resolveUserDwsSubsidyType } from '@zinger/enums/lib/user-dws-subsidy-type'
import { User } from '~/models/user'
import { UserDwsSubsidy } from '~/models/user-dws-subsidy'
import { ZDataTableOptions } from '~/models/z-data-table-options'

type Props = Readonly<{
  items: UserDwsSubsidy[]
  user: User
}>

function createTableOptions (user: User): ZDataTableOptions<UserDwsSubsidy> {
  return {
    content: '障害福祉サービス自治体助成情報',
    footerLink: `/users/${user.id}/dws-subsidies/new`,
    footerLinkPermissions: [Permission.createUserDwsSubsidies],
    footerLinkText: '自治体助成情報を登録',
    headers: [
      { text: '給付方式', value: 'subsidyType', align: 'start', sortable: false },
      { text: '適用期間', value: 'period', align: 'start', sortable: false }
    ],
    itemLink: x => `/users/${user.id}/dws-subsidies/${x.id}`,
    itemLinkPermissions: [Permission.viewUserDwsSubsidies],
    title: '障害福祉サービス自治体助成情報'
  }
}

export default defineComponent<Props>({
  name: 'ZDwsSubsidiesCard',
  props: {
    items: { type: Array, required: true },
    user: { type: Object, required: true }
  },
  setup: props => ({
    resolveUserDwsSubsidyType,
    tableOptions: computed(() => createTableOptions(props.user))
  })
})
</script>
