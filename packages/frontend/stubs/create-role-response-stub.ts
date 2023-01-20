/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { RoleId } from '~/models/role'
import { RolesApi } from '~/services/api/roles-api'
import { createRoleStub, ROLE_ID_MIN } from '~~/stubs/create-role-stub'

export const createRoleResponseStub = (id: RoleId = ROLE_ID_MIN): RolesApi.GetResponse => ({
  role: createRoleStub(id)
})
