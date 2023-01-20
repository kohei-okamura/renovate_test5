/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Auth } from '~/models/auth'
import { createSessionStoreStub } from '~~/stubs/create-session-store-stub'
import { createStaffStub } from '~~/stubs/create-staff-stub'

export const createAuthStub = (auth: Partial<Auth> = {}) => createSessionStoreStub({
  auth: {
    isSystemAdmin: false,
    permissions: [],
    staff: createStaffStub(),
    ...auth
  }
})
