/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createUserIndexResponseStub } from '~~/stubs/create-user-index-response-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStub } from '~~/stubs/create-user-stub'

const user = createUserStub()

/**
 * 利用者 API をスタブ化する.
 */
export const stubUsers: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/users\/(\d+)$/).reply(config => {
    const m = config.url!.match(/\/(\d+)$/)
    const id = m && +m[1]
    return id ? [HttpStatusCode.OK, createUserResponseStub(id)] : [HttpStatusCode.NotFound]
  })
  .onGet('/api/users').reply(config => [HttpStatusCode.OK, createUserIndexResponseStub(config.params)])
  .onPost('/api/users').reply(HttpStatusCode.Created, { user })
  .onPut(/\/api\/users\/\d+$/).reply(HttpStatusCode.NoContent)
  .onDelete(/\/api\/users\/\d+$/).reply(HttpStatusCode.NoContent)
  .onPut(/\/api\/users\/(\d+)\/bank-account$/).reply(HttpStatusCode.NoContent)
