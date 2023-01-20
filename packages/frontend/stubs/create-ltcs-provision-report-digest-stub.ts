/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsProvisionReportStatus } from '@zinger/enums/lib/ltcs-provision-report-status'
import { range } from '@zinger/helpers'
import { LtcsProvisionReportDigest } from '~/models/ltcs-provision-report-digest'
import { createUserStub, USER_ID_MAX, USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createFaker } from '~~/stubs/fake'
import { ID_MAX, ID_MIN, SEEDS } from '~~/stubs/index'

export const LTCS_PROVISION_REPORT_DIGEST_ID_MAX = ID_MAX
export const LTCS_PROVISION_REPORT_DIGEST_ID_MIN = ID_MIN
export const LTCS_PROVISION_REPORT_DIGEST_STUB_COUNT = 10

export function createLtcsProvisionReportDigestStub (
  id: number = LTCS_PROVISION_REPORT_DIGEST_ID_MIN
): LtcsProvisionReportDigest {
  const faker = createFaker(SEEDS[id - 1])
  const user = createUserStub(faker.intBetween(USER_ID_MIN, USER_ID_MAX))
  return {
    /** 利用者 ID */
    userId: user.id,
    /** 利用者氏名 */
    name: user.name,
    /** 被保険者番号 */
    insNumber: faker.randomNumericString(10),
    /** 利用者の状態 */
    isEnabled: faker.randomBoolean(),
    /** 予実の状態 */
    status: faker.randomElement(LtcsProvisionReportStatus.values)
  }
}

export function createLtcsProvisionReportDigestStubs (
  n = LTCS_PROVISION_REPORT_DIGEST_STUB_COUNT,
  skip = 0
): LtcsProvisionReportDigest[] {
  const start = LTCS_PROVISION_REPORT_DIGEST_ID_MIN + skip
  const end = Math.min(start + n - 1, LTCS_PROVISION_REPORT_DIGEST_ID_MAX)
  return range(start, end).map(createLtcsProvisionReportDigestStub)
}
