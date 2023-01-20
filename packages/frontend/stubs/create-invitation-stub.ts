/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { range } from '@zinger/helpers/index'
import { Invitation, InvitationId } from '~/models/invitation'
import { OFFICE_GROUP_ID_MAX, OFFICE_GROUP_ID_MIN } from '~~/stubs/create-office-group-stub'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { ROLE_ID_MAX, ROLE_ID_MIN } from '~~/stubs/create-role-stub'
import { createFaker } from '~~/stubs/fake'
import { STUB_DEFAULT_SEED } from '~~/stubs/index'

const officeIds = range(OFFICE_ID_MIN, OFFICE_ID_MAX)
const officeGroupIds = range(OFFICE_GROUP_ID_MIN, OFFICE_GROUP_ID_MAX)
const roleIds = range(ROLE_ID_MIN, ROLE_ID_MAX)

export function createInvitationStub (id: InvitationId, token: Invitation['token']): Invitation {
  const seed = `${STUB_DEFAULT_SEED}:${token}`
  const faker = createFaker(seed)
  const fake = faker.createFake()
  return {
    id,
    token,
    email: fake.email,
    officeIds: officeIds.filter(() => faker.randomBoolean()),
    officeGroupIds: officeGroupIds.filter(() => faker.randomBoolean()),
    roleIds: roleIds.filter(() => faker.randomBoolean()),
    expiredAt: faker.randomDateTimeString(),
    createdAt: faker.randomDateTimeString()
  }
}
