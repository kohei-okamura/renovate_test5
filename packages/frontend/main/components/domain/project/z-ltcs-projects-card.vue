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
  footerLinkPermissions: [Permission.createLtcsProjects],
  itemLinkPermissions: [Permission.viewLtcsProjects],
  name: 'ZLtcsProjectsCard',
  path: 'ltcs-projects',
  permission: Permission.listLtcsProjects,
  title: '介護保険サービス計画'
})
</script>
