/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsAreaGradesApi } from '~/services/api/dws-area-grades-api'
import { createDwsAreaGradeStubs, DWS_AREA_GRADE_STUB_COUNT } from '~~/stubs/create-dws-area-grade-stub'

export function createDwsAreaGradeIndexResponseStub (): DwsAreaGradesApi.GetIndexResponse {
  const list = createDwsAreaGradeStubs()
  return {
    list,
    pagination: {
      desc: false,
      page: 1,
      pages: 1,
      itemsPerPage: DWS_AREA_GRADE_STUB_COUNT,
      sortBy: 'sortOrder',
      count: DWS_AREA_GRADE_STUB_COUNT
    }
  }
}
