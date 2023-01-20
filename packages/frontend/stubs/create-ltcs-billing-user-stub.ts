/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsBillingUser } from '~/models/ltcs-billing-user'
import { UserId } from '~/models/user'
import { createLtcsInsCardStub } from '~~/stubs/create-ltcs-ins-card-stub'
import { createUserStub, USER_ID_MIN } from '~~/stubs/create-user-stub'

export const createLtcsBillingUserStub = (userId: UserId = USER_ID_MIN): LtcsBillingUser => {
  const user = createUserStub(userId)
  const insCard = createLtcsInsCardStub(userId * 10)
  return {
    userId: user.id,
    ltcsInsCardId: insCard.id,
    insNumber: insCard.insNumber,
    name: user.name,
    sex: user.sex,
    birthday: user.birthday,
    ltcsLevel: insCard.ltcsLevel,
    activatedOn: insCard.activatedOn,
    deactivatedOn: insCard.deactivatedOn
  }
}
