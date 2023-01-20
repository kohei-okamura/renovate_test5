<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-table data-z-contracts-card :items="items" :options="tableOptions">
    <template #item.office="{ item }">{{ resolveOfficeAbbr(item.officeId) }}</template>
    <template #item.status="{ item }">{{ resolveContractStatus(item.status) }}</template>
    <template #item.contractedOn="{ item }">
      <z-era-date short :value="item.contractedOn" />
    </template>
    <template #item.terminatedOn="{ item }">
      <z-era-date short :value="item.terminatedOn" />
    </template>
  </z-data-table>
</template>

<script lang="ts">
import { defineComponent, toRefs, useCssModule } from '@nuxtjs/composition-api'
import { resolveContractStatus } from '@zinger/enums/lib/contract-status'
import { Permission } from '@zinger/enums/lib/permission'
import { dataTableOptions } from '~/composables/data-table-options'
import { useOffices } from '~/composables/use-offices'
import { Contract } from '~/models/contract'
import { ZDataTableOptions } from '~/models/z-data-table-options'

type Props = Readonly<{
  items: Contract[]
  options: ZDataTableOptions<Contract>
  permission: Permission
}>

export default defineComponent<Props>({
  name: 'ZContractsCard',
  props: {
    items: { type: Array, required: true },
    options: { type: Object, required: true },
    permission: { type: String, required: true }
  },
  setup (props) {
    const propRefs = toRefs(props)
    const style = useCssModule()
    return {
      ...useOffices({ permission: propRefs.permission }),
      resolveContractStatus,
      tableOptions: dataTableOptions<Contract>({
        ...props.options,
        headers: [
          { text: '事業所', value: 'office', class: style.office, align: 'start', sortable: false },
          { text: '状態', value: 'status', class: style.status, align: 'start', sortable: false },
          { text: '契約日', value: 'contractedOn', class: style.contractedOn, align: 'start', sortable: false },
          { text: '解約日', value: 'terminatedOn', class: style.terminatedOn, align: 'start', sortable: false }
        ]
      })
    }
  }
})
</script>

<style lang="scss" module>
.office {
  width: auto;
}

.status {
  width: 10em;
}

.contractedOn,
.terminatedOn {
  width: 10em;
}
</style>
