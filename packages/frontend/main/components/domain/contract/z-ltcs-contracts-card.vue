<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-contracts-card :items="items" :options="options" :permission="permission" />
</template>

<script lang="ts">
import { Permission } from '@zinger/enums/lib/permission'
import { ServiceSegment } from '@zinger/enums/lib/service-segment'
import { createContractsCardWrapper } from '~/components/domain/contract/z-contracts-card-utils'

export default createContractsCardWrapper({
  footerLinkPermissions: [Permission.createLtcsContracts],
  itemLinkPermissions: [Permission.viewLtcsContracts],
  name: 'ZLtcsContractsCard',
  path: 'ltcs-contracts',
  permission: Permission.listLtcsContracts,
  segment: ServiceSegment.longTermCare,
  title: '介護保険サービス契約'
})
</script>
