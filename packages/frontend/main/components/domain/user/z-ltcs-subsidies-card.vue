<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-table :items="items" :options="tableOptions">
    <template #item.defrayerCategory="{ item }">{{ resolveDefrayerCategory(item.defrayerCategory) }}</template>
    <template #item.period="{ item }">
      <z-era-date short :value="item.period.start" />
      <span>〜</span>
      <z-era-date short :value="item.period.end" />
    </template>
  </z-data-table>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { resolveDefrayerCategory } from '@zinger/enums/lib/defrayer-category'
import { Permission } from '@zinger/enums/lib/permission'
import { User } from '~/models/user'
import { UserLtcsSubsidy } from '~/models/user-ltcs-subsidy'
import { ZDataTableOptions } from '~/models/z-data-table-options'

type Props = Readonly<{
  items: UserLtcsSubsidy[]
  user: User
}>

function createTableOptions (user: User): ZDataTableOptions<UserLtcsSubsidy> {
  return {
    content: '介護保険サービス公費情報',
    footerLink: `/users/${user.id}/ltcs-subsidies/new`,
    footerLinkPermissions: [Permission.createUserLtcsSubsidies],
    footerLinkText: '公費情報を登録',
    headers: [
      { text: '公費制度（法別番号）', value: 'defrayerCategory', align: 'start', sortable: false },
      { text: '適用期間', value: 'period', align: 'start', sortable: false }
    ],
    itemLink: x => `/users/${user.id}/ltcs-subsidies/${x.id}`,
    itemLinkPermissions: [Permission.viewUserLtcsSubsidies],
    title: '介護保険サービス公費情報'
  }
}

export default defineComponent<Props>({
  name: 'ZLtcsSubsidiesCard',
  props: {
    items: { type: Array, required: true },
    user: { type: Object, required: true }
  },
  setup: props => ({
    resolveDefrayerCategory,
    tableOptions: computed(() => createTableOptions(props.user))
  })
})
</script>
