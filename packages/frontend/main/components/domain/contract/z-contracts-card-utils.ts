/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { ServiceSegment } from '@zinger/enums/lib/service-segment'
import { Contract } from '~/models/contract'
import { User } from '~/models/user'
import { ZDataTableOptions } from '~/models/z-data-table-options'

type ContractsCardWrapperProps = Readonly<{
  items: Contract[]
  user: User
}>

type ContractsCardWrapperOptions = {
  footerLinkPermissions?: Permission[]
  itemLinkPermissions?: Permission[]
  name: string
  path: 'dws-contracts' | 'ltcs-contracts'
  permission: Permission
  segment: ServiceSegment
  title: string
}

export function createContractsCardWrapper (options: ContractsCardWrapperOptions) {
  const { footerLinkPermissions, itemLinkPermissions, name, path, permission, segment, title } = options
  return defineComponent<ContractsCardWrapperProps>({
    name,
    props: {
      items: { type: Array, required: true },
      user: { type: Object, required: true }
    },
    setup (props) {
      const options = computed<Partial<ZDataTableOptions<Contract>>>(() => {
        const user = props.user
        return {
          content: '契約',
          footerLink: `/users/${user.id}/${path}/new?segment=${segment}`,
          footerLinkPermissions,
          footerLinkText: '契約を登録',
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
