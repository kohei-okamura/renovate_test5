/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { TaxType } from '@zinger/enums/lib/tax-type'
import { OwnExpenseProgramId } from '~/models/own-expense-program'
import { OwnExpenseProgramsApi } from '~/services/api/own-expense-programs-api'
import { createOwnExpenseProgramStub } from '~~/stubs/create-own-expense-program-stub'

export function createOwnExpenseProgramResponseStub (
  id: OwnExpenseProgramId = 1,
  taxType?: TaxType
): OwnExpenseProgramsApi.GetResponse {
  const ownExpenseProgram = createOwnExpenseProgramStub(id, taxType)
  return {
    ownExpenseProgram
  }
}
