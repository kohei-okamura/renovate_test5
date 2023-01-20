/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsHomeVisitLongTermCareDictionaryApi } from '~/services/api/ltcs-home-visit-long-term-care-dictionary-api'
import { createLtcsHomeVisitLongTermCareDictionaryStubByServiceCode } from '~~/stubs/create-ltcs-home-visit-long-term-care-dictionary-entry-stub'

export function createLtcsHomeVisitLongTermCareDictionaryEntryResponseStub (
  serviceCode: string = '111111'
): LtcsHomeVisitLongTermCareDictionaryApi.GetResponse {
  const dictionaryEntry = createLtcsHomeVisitLongTermCareDictionaryStubByServiceCode(serviceCode)
  return {
    dictionaryEntry: dictionaryEntry!
  }
}
