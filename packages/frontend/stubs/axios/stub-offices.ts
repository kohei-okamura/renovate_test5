/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createOfficeIndexResponseStub } from '~~/stubs/create-office-index-response-stub'
import { createOfficeResponseStub } from '~~/stubs/create-office-response-stub'

/**
 * 事業所 API をスタブ化する.
 */
export const stubOffices: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/offices\/(\d+)$/).reply(config => {
    const m = config.url!.match(/\/(\d+)$/)
    const id = m && +m[1]
    return id ? [HttpStatusCode.OK, createOfficeResponseStub(id)] : [HttpStatusCode.NotFound]
  })
  .onGet('/api/offices').reply(config => [HttpStatusCode.OK, createOfficeIndexResponseStub(config.params)])
  .onPost('/api/offices').reply(HttpStatusCode.Created)
  .onPut(/\/api\/offices\/\d+$/).reply(HttpStatusCode.NoContent)
  .onDelete(/\/api\/offices\/\d+$/).reply(HttpStatusCode.NoContent)
