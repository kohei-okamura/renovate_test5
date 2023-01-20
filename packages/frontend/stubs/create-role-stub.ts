/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { RoleScope } from '@zinger/enums/lib/role-scope'
import { Set } from 'immutable'
import { Role, RoleId } from '~/models/role'
import { createPermissionGroupStubs } from '~~/stubs/create-permission-group-stub'
import { createFaker } from '~~/stubs/fake'
import { ID_MIN, STUB_DEFAULT_SEED } from '~~/stubs/index'

const permissionGroupStubs = createPermissionGroupStubs()
const faker = createFaker(STUB_DEFAULT_SEED)
const xs: Role[] = [
  {
    id: 1,
    name: 'システム管理者',
    isSystemAdmin: true,
    permissions: Set(permissionGroupStubs.flatMap(x => x.permissions)).toArray(),
    scope: faker.randomElement(RoleScope.values),
    sortOrder: 1,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  },
  {
    id: 2,
    name: 'テスト担当者',
    isSystemAdmin: false,
    permissions: [...permissionGroupStubs[0].permissions],
    scope: faker.randomElement(RoleScope.values),
    sortOrder: 2,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
]

export const ROLE_STUB_COUNT = xs.length
export const ROLE_ID_MAX = ID_MIN + ROLE_STUB_COUNT - 1
export const ROLE_ID_MIN = ID_MIN

export function createRoleStub (id: RoleId): Role {
  return xs.find(x => x.id === id)!
}

export function createRoleStubs (): Role[] {
  return xs
}
