/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsHomeVisitLongTermCareDictionaryApi } from '~/services/api/ltcs-home-visit-long-term-care-dictionary-api'
import { createLtcsHomeVisitLongTermCareDictionaryStubs } from '~~/stubs/create-ltcs-home-visit-long-term-care-dictionary-entry-stub'

type GetIndexResponse = LtcsHomeVisitLongTermCareDictionaryApi.GetIndexResponse

export function createLtcsHomeVisitLongTermCareDictionaryIndexResponseStub (n: number = 10): GetIndexResponse {
  const list = createLtcsHomeVisitLongTermCareDictionaryStubs(n)
  return {
    list,
    pagination: {
      desc: false,
      page: 1,
      pages: 1,
      itemsPerPage: 10,
      sortBy: 'id',
      count: 10
    }
  }
}
