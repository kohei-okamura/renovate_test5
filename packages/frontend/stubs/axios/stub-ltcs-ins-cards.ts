/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createLtcsInsCardResponseStub } from '~~/stubs/create-ltcs-ins-card-response-stub'

/**
 * 介護保険被保険者証 API をスタブ化する.
 */
export const stubLtcsInsCards: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/users\/(\d+)\/ltcs-ins-cards\/(\d+)$/).reply(config => {
    const m = config.url!.match(/\/api\/users\/(\d+)\/ltcs-ins-cards\/(\d+)$/)
    const id = m && +m[2]
    const userId = m && +m[1]
    const stub = id && userId && createLtcsInsCardResponseStub(id)
    return stub ? [HttpStatusCode.OK, stub] : [HttpStatusCode.NotFound]
  })
  .onPost(/\/api\/users\/(\d+)\/ltcs-ins-cards$/).reply(HttpStatusCode.Created)
  .onPut(/\/api\/users\/(\d+)\/ltcs-ins-cards\/\d+$/).reply(HttpStatusCode.NoContent)
  .onDelete(/\/api\/users\/(\d+)\/ltcs-ins-cards\/\d+$/).reply(HttpStatusCode.NoContent)
