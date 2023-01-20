/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsAreaGradesApi } from '~/services/api/ltcs-area-grades-api'
import { createLtcsAreaGradeStubs, LTCS_AREA_GRADE_STUB_COUNT } from '~~/stubs/create-ltcs-area-grade-stub'

export function createLtcsAreaGradeIndexResponseStub (): LtcsAreaGradesApi.GetIndexResponse {
  const list = createLtcsAreaGradeStubs()
  return {
    list,
    pagination: {
      desc: false,
      page: 1,
      pages: 1,
      itemsPerPage: LTCS_AREA_GRADE_STUB_COUNT,
      sortBy: 'sortOrder',
      count: LTCS_AREA_GRADE_STUB_COUNT
    }
  }
}
