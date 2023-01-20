<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-table data-z-ltcs-ins-cards-card :items="items" :options="tableOptions">
    <template #item.ltcsLevel="{ item }">
      {{ resolveLtcsInsCardStatus(item.status) }}：{{ resolveLtcsLevel(item.ltcsLevel) }}
    </template>
    <template #item.effectivatedOn="{ item }">
      <z-era-date short :value="item.effectivatedOn" />
    </template>
  </z-data-table>
</template>

<script lang="ts">
import { computed, defineComponent, useCssModule } from '@nuxtjs/composition-api'
import { resolveLtcsInsCardStatus } from '@zinger/enums/lib/ltcs-ins-card-status'
import { resolveLtcsLevel } from '@zinger/enums/lib/ltcs-level'
import { Permission } from '@zinger/enums/lib/permission'
import { LtcsInsCard } from '~/models/ltcs-ins-card'
import { User } from '~/models/user'
import { ZDataTableOptions } from '~/models/z-data-table-options'

type Props = Readonly<{
  items: LtcsInsCard[]
  user: User
}>

function createTableOptions (user: User): ZDataTableOptions<LtcsInsCard> {
  const style = useCssModule()
  return {
    content: '介護保険被保険者証',
    footerLink: `/users/${user.id}/ltcs-ins-cards/new`,
    footerLinkPermissions: [Permission.createLtcsInsCards],
    footerLinkText: '被保険者証を登録',
    headers: [
      { text: '保険者名', value: 'insurerName', class: style.insurerName, align: 'start', sortable: false },
      { text: '要介護度', value: 'ltcsLevel', class: style.ltcsLevel, align: 'start', sortable: false },
      { text: '被保険者証番号', value: 'insNumber', class: style.insNumber, align: 'start', sortable: false },
      { text: '適用日', value: 'effectivatedOn', class: style.effectivatedOn, align: 'start', sortable: false }
    ],
    itemLink: x => `/users/${user.id}/ltcs-ins-cards/${x.id}`,
    itemLinkPermissions: [Permission.viewLtcsInsCards],
    title: '介護保険被保険者証'
  }
}

export default defineComponent<Props>({
  name: 'ZLtcsInsInsCardsCard',
  props: {
    items: { type: Array, required: true },
    user: { type: Object, required: true }
  },
  setup: props => ({
    resolveLtcsLevel,
    resolveLtcsInsCardStatus,
    tableOptions: computed(() => createTableOptions(props.user))
  })
})
</script>

<style lang="scss" module>
.insurerName {
  width: auto;
}

.ltcsLevel {
  width: 15em;
}

.insNumber {
  width: 10em;
}

.effectivatedOn {
  width: 10em;
}
</style>
