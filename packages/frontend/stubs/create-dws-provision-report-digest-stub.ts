/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsProvisionReportStatus } from '@zinger/enums/lib/dws-provision-report-status'
import { range } from '@zinger/helpers'
import { DwsProvisionReportDigest } from '~/models/dws-provision-report-digest'
import { createUserStub, USER_ID_MAX, USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createFaker } from '~~/stubs/fake'
import { ID_MAX, ID_MIN, SEEDS } from '~~/stubs/index'

export const DWS_PROVISION_REPORT_DIGEST_ID_MAX = ID_MAX
export const DWS_PROVISION_REPORT_DIGEST_ID_MIN = ID_MIN
export const DWS_PROVISION_REPORT_DIGEST_STUB_COUNT = 10

export function createDwsProvisionReportDigestStub (
  id: number = DWS_PROVISION_REPORT_DIGEST_ID_MIN
): DwsProvisionReportDigest {
  const faker = createFaker(SEEDS[id - 1])
  const user = createUserStub(faker.intBetween(USER_ID_MIN, USER_ID_MAX))
  return {
    /** 利用者 ID */
    userId: user.id,
    /** 利用者氏名 */
    name: user.name,
    /** 受給者証番号 */
    dwsNumber: faker.randomNumericString(10),
    /** 利用者の状態 */
    isEnabled: faker.randomBoolean(),
    /** 予実の状態 */
    status: faker.randomElement(DwsProvisionReportStatus.values)
  }
}

export function createDwsProvisionReportDigestStubs (
  n = DWS_PROVISION_REPORT_DIGEST_STUB_COUNT,
  skip = 0
): DwsProvisionReportDigest[] {
  const start = DWS_PROVISION_REPORT_DIGEST_ID_MIN + skip
  const end = Math.min(start + n - 1, DWS_PROVISION_REPORT_DIGEST_ID_MAX)
  return range(start, end).map(createDwsProvisionReportDigestStub)
}
