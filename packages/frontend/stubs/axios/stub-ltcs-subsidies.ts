/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createLtcsSubsidyResponseStub } from '~~/stubs/create-ltcs-subsidy-response-stub'

/**
 * 利用者：公費情報 API をスタブ化する.
 */
export const stubLtcsSubsidies: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/users\/(\d+)\/ltcs-subsidies\/(\d+)$/).reply(config => {
    const m = config.url!.match(/\/api\/users\/(\d+)\/ltcs-subsidies\/(\d+)$/)
    const id = m && +m[2]
    const userId = m && +m[1]
    const stub = id && userId && createLtcsSubsidyResponseStub(id)
    return stub ? [HttpStatusCode.OK, stub] : [HttpStatusCode.NotFound]
  })
  .onPost(/\/api\/users\/(\d+)\/ltcs-subsidies$/).reply(HttpStatusCode.Created)
  .onPut(/\/api\/users\/(\d+)\/ltcs-subsidies\/\d+$/).reply(HttpStatusCode.NoContent)
  .onDelete(/\/api\/users\/(\d+)\/ltcs-subsidies\/\d+$/).reply(HttpStatusCode.NoContent)
