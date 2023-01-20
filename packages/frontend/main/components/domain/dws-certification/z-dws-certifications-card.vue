<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-table data-z-dws-certifications-card :items="items" :options="tableOptions">
    <template #item.dwsLevel="{ item }">
      {{ resolveDwsCertificationStatus(item.status) }}：{{ resolveDwsLevel(item.dwsLevel) }}
    </template>
    <template #item.effectivatedOn="{ item }">
      <z-era-date short :value="item.effectivatedOn" />
    </template>
  </z-data-table>
</template>

<script lang="ts">
import { computed, defineComponent, useCssModule } from '@nuxtjs/composition-api'
import { resolveDwsCertificationStatus } from '@zinger/enums/lib/dws-certification-status'
import { resolveDwsLevel } from '@zinger/enums/lib/dws-level'
import { Permission } from '@zinger/enums/lib/permission'
import { dataTableOptions } from '~/composables/data-table-options'
import { DwsCertification } from '~/models/dws-certification'
import { User } from '~/models/user'

type Props = Readonly<{
  items: DwsCertification[]
  user: User
}>

export default defineComponent<Props>({
  name: 'ZDwsCertificationsCard',
  props: {
    items: { type: Array, required: true },
    user: { type: Object, required: true }
  },
  setup: props => {
    const style = useCssModule()
    return {
      resolveDwsLevel,
      resolveDwsCertificationStatus,
      tableOptions: computed(() => dataTableOptions<DwsCertification>({
        content: '障害福祉サービス受給者証',
        footerLink: `/users/${props.user.id}/dws-certifications/new`,
        footerLinkPermissions: [Permission.createDwsCertifications],
        footerLinkText: '受給者証を登録',
        headers: [
          { text: '市町村名', value: 'cityName', class: style.cityName, align: 'start', sortable: false },
          { text: '障害支援区分', value: 'dwsLevel', class: style.dwsLevel, align: 'start', sortable: false },
          { text: '受給者証番号', value: 'dwsNumber', class: style.dwsNumber, align: 'start', sortable: false },
          { text: '適用日', value: 'effectivatedOn', class: style.effectivatedOn, align: 'start', sortable: false }
        ],
        itemLink: x => `/users/${props.user.id}/dws-certifications/${x.id}`,
        itemLinkPermissions: [Permission.viewDwsCertifications],
        title: '障害福祉サービス受給者証'
      }))
    }
  }
})
</script>

<style lang="scss" module>
.cityName {
  width: auto;
}

.dwsNumber {
  width: 10em;
}

.dwsLevel {
  width: 15em;
}

.effectivatedOn {
  width: 10em;
}
</style>
