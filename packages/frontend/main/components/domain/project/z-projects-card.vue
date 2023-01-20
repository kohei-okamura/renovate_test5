<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-table data-z-projects-card :items="items" :options="tableOptions">
    <template #item.office="{ item }">{{ resolveOfficeAbbr(item.officeId) }}</template>
    <template #item.staff="{ item }">{{ resolveStaffName(item.staffId) }}</template>
    <template #item.writtenOn="{ item }">
      <z-era-date short :value="item.writtenOn" />
    </template>
    <template #item.effectivatedOn="{ item }">
      <z-era-date short :value="item.effectivatedOn" />
    </template>
  </z-data-table>
</template>

<script lang="ts">
import { computed, defineComponent, toRefs, useCssModule } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { dataTableOptions } from '~/composables/data-table-options'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { Contract } from '~/models/contract'
import { Project } from '~/models/project'
import { User } from '~/models/user'
import { ZDataTableOptions } from '~/models/z-data-table-options'

type Props = Readonly<{
  items: Project[]
  contracts: Contract[]
  options: ZDataTableOptions<Project>
  permission: Permission
  user: User
}>

export default defineComponent<Props>({
  name: 'ZProjectsCard',
  props: {
    items: { type: Array, required: true },
    contracts: { type: Array, required: true },
    options: { type: Object, required: true },
    permission: { type: String, required: true },
    user: { type: Object, required: true }
  },
  setup (props: Props) {
    const propRefs = toRefs(props)
    const style = useCssModule()
    return {
      ...useOffices({ permission: propRefs.permission }),
      ...useStaffs({ permission: propRefs.permission }),
      tableOptions: computed(() => dataTableOptions<Project>({
        ...props.options,
        headers: [
          { text: '事業所', value: 'office', class: style.office, align: 'start', sortable: false },
          { text: '作成者', value: 'staff', class: style.staff, align: 'start', sortable: false },
          { text: '作成日', value: 'writtenOn', class: style.writtenOn, align: 'start', sortable: false },
          { text: '適用日', value: 'effectivatedOn', class: style.effectivatedOn, align: 'start', sortable: false }
        ]
      }))
    }
  }
})
</script>

<style lang="scss" module>
.office {
  width: auto;
}

.staff {
  width: 10em;
}

.writtenOn,
.effectivatedOn {
  width: 10em;
}
</style>
