/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { UsersApi } from '~/services/api/users-api'
import { createIndexResponse } from '~~/stubs/create-index-response'
import { createUserStubs, USER_STUB_COUNT } from '~~/stubs/create-user-stub'

export function createUserIndexResponseStub (params: UsersApi.GetIndexParams = {}): UsersApi.GetIndexResponse {
  return createIndexResponse(params, USER_STUB_COUNT, createUserStubs)
}
