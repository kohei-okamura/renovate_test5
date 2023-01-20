/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createVisitingCareForPwsdCalcSpecResponseStub } from '~~/stubs/create-visiting-care-for-pwsd-calc-spec-response-stub'

/**
 * スタッフ API をスタブ化する.
 */
export const stubVisitingCareForPwsdCalcSpecs: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/offices\/(\d+)\/visiting-care-for-pwsd-calc-specs\/(\d+)$/).reply(config => {
    const m = config.url!.match(/\/api\/offices\/(\d+)\/visiting-care-for-pwsd-calc-specs\/(\d+)$/)
    const id = m && +m[2]
    const officeId = m && +m[1]
    return id && officeId
      ? [HttpStatusCode.OK, createVisitingCareForPwsdCalcSpecResponseStub(id)]
      : [HttpStatusCode.NotFound]
  })
  .onPost(/\/api\/offices\/(\d+)\/visiting-care-for-pwsd-calc-specs$/).reply(HttpStatusCode.Created)
  .onPut(/\/api\/offices\/(\d+)\/visiting-care-for-pwsd-calc-specs\/\d+$/).reply(HttpStatusCode.NoContent)
  .onDelete(/\/api\/offices\/(\d+)\/visiting-care-for-pwsd-calc-specs\/\d+$/).reply(HttpStatusCode.NoContent)
