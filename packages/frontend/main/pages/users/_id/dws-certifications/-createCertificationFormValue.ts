/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsCertification } from '~/models/dws-certification'
import { DwsCertificationsApi } from '~/services/api/dws-certifications-api'
type Form = DeepPartial<DwsCertificationsApi.Form>

export const createCertificationFormValue = (x: DwsCertification): Form => ({
  child: {
    name: {
      familyName: x.child.name.familyName,
      givenName: x.child.name.givenName,
      phoneticFamilyName: x.child.name.phoneticFamilyName,
      phoneticGivenName: x.child.name.phoneticGivenName
    },
    birthday: x.child.birthday
  },
  effectivatedOn: x.effectivatedOn,
  status: x.status,
  dwsNumber: x.dwsNumber,
  dwsTypes: x.dwsTypes,
  issuedOn: x.issuedOn,
  cityName: x.cityName,
  cityCode: x.cityCode,
  dwsLevel: x.dwsLevel,
  isSubjectOfComprehensiveSupport: x.isSubjectOfComprehensiveSupport,
  activatedOn: x.activatedOn,
  deactivatedOn: x.deactivatedOn,
  grants: x.grants,
  copayLimit: x.copayLimit,
  copayActivatedOn: x.copayActivatedOn,
  copayDeactivatedOn: x.copayDeactivatedOn,
  copayCoordination: x.copayCoordination,
  agreements: x.agreements
})
