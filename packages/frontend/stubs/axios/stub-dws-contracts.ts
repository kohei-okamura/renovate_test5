/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createContractResponseStub } from '~~/stubs/create-contract-response-stub'

/**
 * 障害福祉サービス：契約 API をスタブ化する.
 */
export const stubDwsContracts: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/users\/(\d+)\/dws-contracts\/(\d+)$/).reply(config => {
    const m = config.url!.match(/\/api\/users\/(\d+)\/dws-contracts\/(\d+)$/)
    const id = m && +m[2]
    const userId = m && +m[1]
    return id && userId
      ? [HttpStatusCode.OK, createContractResponseStub(id)]
      : [HttpStatusCode.NotFound]
  })
  .onPut(/\/api\/users\/(\d+)\/dws-contracts\/\d+$/).reply(config => {
    const m = config.url!.match(/\/api\/users\/(\d+)\/dws-contracts\/(\d+)$/)
    const id = m && +m[2]
    const userId = m && +m[1]
    return id && userId
      ? [HttpStatusCode.OK, createContractResponseStub(id + 1)]
      : [HttpStatusCode.NotFound]
  })
  .onPost(/\/api\/users\/(\d+)\/dws-contracts$/).reply(HttpStatusCode.Created)
  .onDelete(/\/api\/users\/(\d+)\/dws-contracts\/\d+$/).reply(HttpStatusCode.NoContent)
