/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createUserDwsCalcSpecResponseStub } from '~~/stubs/create-user-dws-calc-spec-response-stub'

/**
 * 障害福祉サービス：利用者別算定情報 API をスタブ化する.
 */
export const stubUserDwsCalcSpecs: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/users\/(\d+)\/dws-calc-specs\/(\d+)$/).reply(config => {
    const m = config.url!.match(/\/api\/users\/(\d+)\/dws-calc-specs\/(\d+)$/)
    const id = m && +m[2]
    const userId = m && +m[1]
    return id && userId
      ? [HttpStatusCode.OK, createUserDwsCalcSpecResponseStub(id)]
      : [HttpStatusCode.NotFound]
  })
  .onPost(/\/api\/users\/(\d+)\/dws-calc-specs$/).reply(HttpStatusCode.Created)
  .onPut(/\/api\/users\/(\d+)\/dws-calc-specs\/\d+$/).reply(HttpStatusCode.OK, createUserDwsCalcSpecResponseStub())
