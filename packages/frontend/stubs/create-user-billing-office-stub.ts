/*
* Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
* UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
*/
import { OfficeId } from '~/models/office'
import { UserBillingOffice } from '~/models/user-billing-office'
import { createOfficeStub, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'

export function createUserBillingOfficeStub (id: OfficeId = OFFICE_ID_MIN): UserBillingOffice {
  const office = createOfficeStub(id)
  return {
    name: office.name,
    corporationName: office.corporationName,
    addr: office.addr,
    tel: office.tel
  }
}
