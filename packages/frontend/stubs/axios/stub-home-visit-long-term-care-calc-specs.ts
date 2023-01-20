/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import {
  createHomeVisitLongTermCareCalcSpecPostOrPutResponseStub,
  createHomeVisitLongTermCareCalcSpecResponseStub
} from '~~/stubs/create-home-visit-long-term-care-calc-spec-response-stub'

const urlPattern = '/api/offices/(\\d+)/home-visit-long-term-care-calc-specs'
const noIdPattern = new RegExp(`${urlPattern}$`)
const idPattern = new RegExp(`${urlPattern}/(\\d+)$`)

/**
 * 介護保険サービス：訪問介護：算定情報 API をスタブ化する.
 */
export const stubHomeVisitLongTermCareCalcSpecs: StubFunction = mockAdapter => mockAdapter
  .onGet(idPattern).reply(config => {
    const m = config.url!.match(idPattern)
    const id = m && +m[2]
    const officeId = m && +m[1]
    return id && officeId
      ? [HttpStatusCode.OK, createHomeVisitLongTermCareCalcSpecResponseStub(id)]
      : [HttpStatusCode.NotFound]
  })
  .onGet(noIdPattern).reply(HttpStatusCode.OK, createHomeVisitLongTermCareCalcSpecResponseStub())
  .onPost(noIdPattern).reply(HttpStatusCode.Created, createHomeVisitLongTermCareCalcSpecPostOrPutResponseStub())
  .onPut(idPattern).reply(HttpStatusCode.OK, createHomeVisitLongTermCareCalcSpecPostOrPutResponseStub())
  .onDelete(idPattern).reply(HttpStatusCode.NoContent)
