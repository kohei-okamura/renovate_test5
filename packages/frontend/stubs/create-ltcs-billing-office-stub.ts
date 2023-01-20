/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsBillingOffice } from '~/models/ltcs-billing-office'
import { OfficeId } from '~/models/office'
import { createOfficeStub, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'

export const createLtcsBillingOfficeStub = (officeId: OfficeId = OFFICE_ID_MIN): LtcsBillingOffice => {
  const office = createOfficeStub(officeId)
  return {
    officeId: office.id,
    code: office.ltcsHomeVisitLongTermCareService?.code ?? '1234567890',
    name: office.name,
    abbr: office.abbr,
    addr: office.addr,
    tel: office.tel
  }
}
