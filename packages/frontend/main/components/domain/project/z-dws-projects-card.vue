<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-projects-card :contracts="contracts" :items="items" :options="options" :permission="permission" :user="user" />
</template>

<script lang="ts">
import { Permission } from '@zinger/enums/lib/permission'
import { createProjectsCardWrapper } from '~/components/domain/project/z-projects-card-utils'

export default createProjectsCardWrapper({
  footerLinkPermissions: [Permission.createDwsProjects],
  itemLinkPermissions: [Permission.viewDwsProjects],
  name: 'ZDwsProjectsCard',
  path: 'dws-projects',
  permission: Permission.listDwsProjects,
  title: '障害福祉サービス計画'
})
</script>
