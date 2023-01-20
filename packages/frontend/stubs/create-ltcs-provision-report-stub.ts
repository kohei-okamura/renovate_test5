/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  HomeVisitLongTermCareSpecifiedOfficeAddition
} from '@zinger/enums/lib/home-visit-long-term-care-specified-office-addition'
import { LtcsBaseIncreaseSupportAddition } from '@zinger/enums/lib/ltcs-base-increase-support-addition'
import { LtcsOfficeLocationAddition } from '@zinger/enums/lib/ltcs-office-location-addition'
import { LtcsProvisionReportStatus } from '@zinger/enums/lib/ltcs-provision-report-status'
import {
  LtcsSpecifiedTreatmentImprovementAddition
} from '@zinger/enums/lib/ltcs-specified-treatment-improvement-addition'
import { LtcsTreatmentImprovementAddition } from '@zinger/enums/lib/ltcs-treatment-improvement-addition'
import { LtcsProvisionReport } from '~/models/ltcs-provision-report'
import { CONTRACT_ID_MAX, CONTRACT_ID_MIN } from '~~/stubs/create-contract-stub'
import { createLtcsProvisionReportEntryStubs } from '~~/stubs/create-ltcs-provision-report-entry-stub'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { USER_ID_MAX, USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createFaker } from '~~/stubs/fake'
import { ID_MAX, ID_MIN, SEEDS } from '~~/stubs/index'

export const LTCS_PROVISION_REPORT_ID_MAX = ID_MAX
export const LTCS_PROVISION_REPORT_ID_MIN = ID_MIN

type Arguments = {
  id?: number
  providedIn?: string
}

export function createLtcsProvisionReportStub (args: Arguments = {}): LtcsProvisionReport {
  const id = args.id ?? LTCS_PROVISION_REPORT_ID_MIN
  const faker = createFaker(SEEDS[id - 1])
  const providedIn = args?.providedIn ?? '2021-02'
  const status = faker.randomElement(LtcsProvisionReportStatus.values)
  return {
    /** 予実 ID */
    id,
    /** 利用者 ID */
    userId: faker.intBetween(USER_ID_MIN, USER_ID_MAX),
    /** 事業所 ID */
    officeId: faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX),
    /** 契約 ID */
    contractId: faker.intBetween(CONTRACT_ID_MIN, CONTRACT_ID_MAX),
    /** サービス提供年月 */
    providedIn,
    /** サービス情報 */
    entries: createLtcsProvisionReportEntryStubs(providedIn),
    /** 特定事業所加算 */
    specifiedOfficeAddition: faker.randomElement(HomeVisitLongTermCareSpecifiedOfficeAddition.values),
    /** 処遇改善加算 */
    treatmentImprovementAddition: faker.randomElement(LtcsTreatmentImprovementAddition.values),
    /** 特定処遇改善加算 */
    specifiedTreatmentImprovementAddition: faker.randomElement(LtcsSpecifiedTreatmentImprovementAddition.values),
    /** ベースアップ等支援加算 */
    baseIncreaseSupportAddition: faker.randomElement(LtcsBaseIncreaseSupportAddition.values),
    /** 地域加算 */
    locationAddition: faker.randomElement(LtcsOfficeLocationAddition.values),
    /** 超過単位（予定） */
    plan: {
      maxBenefitQuotaExcessScore: faker.intBetween(10, 99999),
      maxBenefitExcessScore: faker.intBetween(10, 99999)
    },
    /** 超過単位（実績） */
    result: {
      maxBenefitQuotaExcessScore: faker.intBetween(10, 99999),
      maxBenefitExcessScore: faker.intBetween(10, 99999)
    },
    /** 状態 */
    status,
    /** 確定日時 */
    fixedAt: status === LtcsProvisionReportStatus.fixed ? faker.randomDateString() : undefined,
    /** 登録日時 */
    createdAt: faker.randomDateString(),
    /** 更新日時 */
    updatedAt: faker.randomDateString()
  }
}
