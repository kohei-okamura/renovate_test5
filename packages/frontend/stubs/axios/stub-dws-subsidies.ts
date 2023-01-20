/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createDwsSubsidyResponseStub } from '~~/stubs/create-dws-subsidy-response-stub'

/**
 * 障害福祉サービス：自治体助成情報 API をスタブ化する.
 */
export const stubDwsSubsidies: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/users\/(\d+)\/dws-subsidies\/(\d+)$/).reply(config => {
    const m = config.url!.match(/\/api\/users\/(\d+)\/dws-subsidies\/(\d+)$/)
    const id = m && +m[2]
    const userId = m && +m[1]
    const stub = id && userId && createDwsSubsidyResponseStub(id)
    return stub ? [HttpStatusCode.OK, stub] : [HttpStatusCode.NotFound]
  })
  .onPost(/\/api\/users\/(\d+)\/dws-subsidies$/).reply(HttpStatusCode.Created)
  .onPut(/\/api\/users\/(\d+)\/dws-subsidies\/\d+$/).reply(HttpStatusCode.NoContent)
