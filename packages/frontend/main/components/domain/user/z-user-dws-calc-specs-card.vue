<!--
  - Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-table :items="items" :options="tableOptions">
    <template #item.locationAddition="{ item }">{{ resolveDwsUserLocationAddition(item.locationAddition) }}</template>
    <template #item.effectivatedOn="{ item }">
      <z-era-date short :value="item.effectivatedOn" />
    </template>
  </z-data-table>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { resolveDwsUserLocationAddition } from '@zinger/enums/lib/dws-user-location-addition'
import { Permission } from '@zinger/enums/lib/permission'
import { User } from '~/models/user'
import { UserDwsCalcSpec } from '~/models/user-dws-calc-spec'
import { ZDataTableOptions } from '~/models/z-data-table-options'

type Props = Readonly<{
  items: UserDwsCalcSpec[]
  user: User
}>

function createTableOptions (user: User): ZDataTableOptions<UserDwsCalcSpec> {
  return {
    content: '障害福祉サービス利用者別算定情報',
    footerLink: `/users/${user.id}/dws-calc-specs/new`,
    footerLinkPermissions: [Permission.createUserDwsCalcSpecs],
    footerLinkText: '利用者別算定情報を登録',
    headers: [
      { text: '地域加算', value: 'locationAddition', align: 'start', sortable: false },
      { text: '適用日', value: 'effectivatedOn', align: 'start', sortable: false }
    ],
    itemLink: x => `/users/${user.id}/dws-calc-specs/${x.id}/edit`,
    itemLinkPermissions: [Permission.updateUserDwsCalcSpecs],
    title: '障害福祉サービス利用者別算定情報'
  }
}

export default defineComponent<Props>({
  name: 'ZUserDwsCalcSpecsCard',
  props: {
    items: { type: Array, required: true },
    user: { type: Object, required: true }
  },
  setup: props => ({
    resolveDwsUserLocationAddition,
    tableOptions: computed(() => createTableOptions(props.user))
  })
})
</script>
