/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import {
  createLtcsHomeVisitLongTermCareDictionaryStubByServiceCode,
  createLtcsHomeVisitLongTermCareDictionaryStubsForSuggestion
} from '~~/stubs/create-ltcs-home-visit-long-term-care-dictionary-entry-stub'
import { createLtcsHomeVisitLongTermCareDictionaryEntryResponseStub } from '~~/stubs/ltcs-home-visit-long-term-care-dictionary-entry-response-stub'

/**
 * 介護保険サービス：訪問介護：サービスコード辞書エントリ API をスタブ化する.
 */
export const stubLtcsHomeVisitLongTermCareDictionary: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/ltcs-home-visit-long-term-care-dictionary-entries\/(\d{6})$/).reply(config => {
    const m = config.url!.match(/\/(\d{6})$/)
    const serviceCode = m && m[1]
    return serviceCode
      ? [HttpStatusCode.OK, createLtcsHomeVisitLongTermCareDictionaryEntryResponseStub(serviceCode)]
      : [HttpStatusCode.NotFound]
  })
  .onGet('/api/ltcs-home-visit-long-term-care-dictionary').reply(config => {
    if (config.params.q && config.params.q.length === 6) {
      const x = createLtcsHomeVisitLongTermCareDictionaryStubByServiceCode(config.params.q)
      const list = x === undefined ? [] : [x]
      const pagination = {
        count: list.length,
        desc: false,
        itemsPerPage: list.length,
        page: 1,
        pages: 1,
        sortBy: ''
      }
      return [HttpStatusCode.OK, { list, pagination }]
    } else {
      const list = createLtcsHomeVisitLongTermCareDictionaryStubsForSuggestion(config.params)
      const pagination = {
        count: list.length,
        desc: false,
        itemsPerPage: list.length,
        page: 1,
        pages: 1,
        sortBy: ''
      }
      return [HttpStatusCode.OK, { list, pagination }]
    }
  })
