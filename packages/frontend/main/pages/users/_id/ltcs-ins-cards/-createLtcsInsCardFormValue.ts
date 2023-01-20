/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsInsCard } from '~/models/ltcs-ins-card'
import { LtcsInsCardsApi } from '~/services/api/ltcs-ins-cards-api'

type Form = Partial<LtcsInsCardsApi.Form>

export const createLtcsInsCardFormValue = (x: LtcsInsCard): Form => ({
  effectivatedOn: x.effectivatedOn,
  status: x.status,
  insNumber: x.insNumber,
  issuedOn: x.issuedOn,
  insurerNumber: x.insurerNumber,
  insurerName: x.insurerName,
  ltcsLevel: x.ltcsLevel,
  certificatedOn: x.certificatedOn,
  activatedOn: x.activatedOn,
  deactivatedOn: x.deactivatedOn,
  maxBenefitQuotas: x.maxBenefitQuotas,
  careManagerName: x.careManagerName,
  carePlanAuthorType: x.carePlanAuthorType,
  communityGeneralSupportCenterId: x.communityGeneralSupportCenterId,
  carePlanAuthorOfficeId: x.carePlanAuthorOfficeId,
  copayRate: x.copayRate / 10, // 利用者負担割合を十分率にする
  copayActivatedOn: x.copayActivatedOn,
  copayDeactivatedOn: x.copayDeactivatedOn
})
