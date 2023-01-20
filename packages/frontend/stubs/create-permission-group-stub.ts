/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Permission } from '@zinger/enums/lib/permission'
import { DateLike } from '~/models/date'
import { PermissionGroup, PermissionGroupId } from '~/models/permission-group'

const now: DateLike = '2020-09-28T12:34:56.789+0900'
const xs: PermissionGroup[] = [
  {
    id: 1,
    code: 'offices',
    name: '事業所',
    displayName: '事業所',
    permissions: [
      Permission.listInternalOffices,
      Permission.viewInternalOffices,
      Permission.createInternalOffices,
      Permission.updateInternalOffices,
      Permission.deleteInternalOffices
    ],
    sortOrder: 1,
    createdAt: now
  },
  {
    id: 2,
    code: 'office-groups',
    name: '事業所グループ',
    displayName: '事業所グループ',
    permissions: [
      Permission.listOfficeGroups,
      Permission.createOfficeGroups,
      Permission.updateOfficeGroups,
      Permission.deleteOfficeGroups
    ],
    sortOrder: 2,
    createdAt: now
  },
  {
    id: 3,
    code: 'roles',
    name: 'ロール',
    displayName: 'ロール',
    permissions: [
      Permission.listRoles,
      Permission.viewRoles,
      Permission.createRoles,
      Permission.updateRoles,
      Permission.deleteRoles
    ],
    sortOrder: 3,
    createdAt: now
  },
  {
    id: 4,
    code: 'staffs',
    name: 'スタッフ',
    displayName: 'スタッフ',
    permissions: [
      Permission.listStaffs,
      Permission.viewStaffs,
      Permission.createStaffs,
      Permission.updateStaffs,
      Permission.deleteStaffs
    ],
    sortOrder: 4,
    createdAt: now
  },
  {
    id: 5,
    code: 'users',
    name: '利用者',
    displayName: '利用者',
    permissions: [
      Permission.listUsers,
      Permission.viewUsers,
      Permission.createUsers,
      Permission.updateUsers,
      Permission.deleteUsers
    ],
    sortOrder: 5,
    createdAt: now
  }
]
export const PERMISSION_GROUP_STUB_COUNT = xs.length
export const PERMISSION_GROUP_IDS = xs.map(x => x.id)
export const PERMISSION_GROUP_ID_MAX = Math.max(...PERMISSION_GROUP_IDS)
export const PERMISSION_GROUP_ID_MIN = Math.min(...PERMISSION_GROUP_IDS)

export function createPermissionGroupStub (id: PermissionGroupId | undefined): PermissionGroup | undefined {
  return xs.find(x => x.id === id)
}

export function createPermissionGroupStubs (): PermissionGroup[] {
  return xs
}
