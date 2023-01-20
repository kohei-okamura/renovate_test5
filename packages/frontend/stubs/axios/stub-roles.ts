/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createRoleIndexResponseStub } from '~~/stubs/create-role-index-response-stub'
import { createRoleResponseStub } from '~~/stubs/create-role-response-stub'

/**
 * ロール API をスタブ化する.
 */
export const stubRoles: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/roles\/(\d+)$/).reply(config => {
    const m = config.url!.match(/\/(\d+)$/)
    const id = m && +m[1]
    return id ? [HttpStatusCode.OK, createRoleResponseStub(id)] : [HttpStatusCode.NotFound]
  })
  .onGet('/api/roles').reply(HttpStatusCode.OK, createRoleIndexResponseStub())
  .onPost('/api/roles').reply(HttpStatusCode.Created)
  .onPut(/\/api\/roles\/\d+$/).reply(HttpStatusCode.NoContent)
  .onDelete(/\/api\/roles\/\d+$/).reply(HttpStatusCode.NoContent)
