/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createUserLtcsCalcSpecResponseStub } from '~~/stubs/create-user-ltcs-calc-spec-response-stub'

/**
 * 介護保険サービス：利用者別算定情報 API をスタブ化する.
 */
export const stubUserLtcsCalcSpecs: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/users\/(\d+)\/ltcs-calc-specs\/(\d+)$/).reply(config => {
    const m = config.url!.match(/\/api\/users\/(\d+)\/ltcs-calc-specs\/(\d+)$/)
    const id = m && +m[2]
    const userId = m && +m[1]
    return id && userId
      ? [HttpStatusCode.OK, createUserLtcsCalcSpecResponseStub(id)]
      : [HttpStatusCode.NotFound]
  })
  .onPost(/\/api\/users\/(\d+)\/ltcs-calc-specs$/).reply(HttpStatusCode.Created)
  .onPut(/\/api\/users\/(\d+)\/ltcs-calc-specs\/\d+$/).reply(HttpStatusCode.OK, createUserLtcsCalcSpecResponseStub())
