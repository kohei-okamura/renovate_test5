/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { OwnExpenseProgramsApi } from '~/services/api/own-expense-programs-api'
import { createIndexResponse } from '~~/stubs/create-index-response'
import { createOwnExpenseProgramStubs, OWN_EXPENSE_PROGRAM_STUB_COUNT } from '~~/stubs/create-own-expense-program-stub'

export function createOwnExpenseProgramIndexResponseStub (
  params: OwnExpenseProgramsApi.GetIndexParams = {}
): OwnExpenseProgramsApi.GetIndexResponse {
  return createIndexResponse(params, OWN_EXPENSE_PROGRAM_STUB_COUNT, createOwnExpenseProgramStubs)
}
