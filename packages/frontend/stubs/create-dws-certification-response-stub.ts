/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsCertificationId } from '~/models/dws-certification'
import { DwsCertificationsApi } from '~/services/api/dws-certifications-api'
import { createDwsCertificationStub, DWS_CERTIFICATION_ID_MIN } from '~~/stubs/create-dws-certification-stub'

export function createDwsCertificationResponseStub (
  id: DwsCertificationId = DWS_CERTIFICATION_ID_MIN
): DwsCertificationsApi.GetResponse {
  return {
    dwsCertification: createDwsCertificationStub(id)
  }
}
