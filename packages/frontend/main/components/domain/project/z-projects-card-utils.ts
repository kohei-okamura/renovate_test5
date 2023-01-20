/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { Project } from '~/models/project'
import { User } from '~/models/user'
import { ZDataTableOptions } from '~/models/z-data-table-options'

type ProjectsCardWrapperProps = Readonly<{
  items: Project[]
  user: User
}>

type ProjectsCardWrapperOptions = {
  footerLinkPermissions?: Permission[]
  itemLinkPermissions?: Permission[]
  name: string
  path: string
  permission: Permission
  title: string
}

export function createProjectsCardWrapper (options: ProjectsCardWrapperOptions) {
  const { footerLinkPermissions, itemLinkPermissions, name, path, permission, title } = options
  return defineComponent<ProjectsCardWrapperProps>({
    name,
    props: {
      items: { type: Array, required: true },
      contracts: { type: Array, required: true },
      user: { type: Object, required: true }
    },
    setup (props) {
      const options = computed<Partial<ZDataTableOptions<Project>>>(() => {
        const user = props.user
        return {
          content: '計画',
          footerLink: `/users/${user.id}/${path}/new`,
          footerLinkPermissions,
          footerLinkText: '計画を登録',
          itemLink: x => `/users/${user.id}/${path}/${x.id}`,
          itemLinkPermissions,
          title
        }
      })
      return {
        options,
        permission
      }
    }
  })
}
