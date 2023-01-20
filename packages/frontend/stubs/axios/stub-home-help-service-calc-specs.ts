/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createHomeHelpServiceCalcSpecResponseStub } from '~~/stubs/create-home-help-service-calc-spec-response-stub'

/**
 * 障害福祉サービス：居宅介護：算定情報 API をスタブ化する.
 */
export const stubHomeHelpServiceCalcSpecs: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/offices\/(\d+)\/home-help-service-calc-specs\/(\d+)$/).reply(config => {
    const m = config.url!.match(/\/api\/offices\/(\d+)\/home-help-service-calc-specs\/(\d+)$/)
    const id = m && +m[2]
    const officeId = m && +m[1]
    return id && officeId
      ? [HttpStatusCode.OK, createHomeHelpServiceCalcSpecResponseStub(id)]
      : [HttpStatusCode.NotFound]
  })
  .onPost(/\/api\/offices\/(\d+)\/home-help-service-calc-specs$/).reply(HttpStatusCode.Created)
  .onPut(/\/api\/offices\/(\d+)\/home-help-service-calc-specs\/\d+$/).reply(HttpStatusCode.NoContent)
  .onDelete(/\/api\/offices\/(\d+)\/home-help-service-calc-specs\/\d+$/).reply(HttpStatusCode.NoContent)
