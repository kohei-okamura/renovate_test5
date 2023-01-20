/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsProvisionReportStatus } from '@zinger/enums/lib/dws-provision-report-status'
import { DwsProvisionReport } from '~/models/dws-provision-report'
import { CONTRACT_ID_MAX, CONTRACT_ID_MIN } from '~~/stubs/create-contract-stub'
import { createDwsProvisionReportItemStubs } from '~~/stubs/create-dws-provision-report-item-stub'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { USER_ID_MAX, USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createFaker } from '~~/stubs/fake'
import { ID_MAX, ID_MIN, SEEDS } from '~~/stubs/index'

export const DWS_PROVISION_REPORT_ID_MAX = ID_MAX
export const DWS_PROVISION_REPORT_ID_MIN = ID_MIN

type Arguments = {
  id?: number
  providedIn?: string
}

export function createDwsProvisionReportStub (args?: Arguments): DwsProvisionReport {
  const id = args?.id ?? DWS_PROVISION_REPORT_ID_MIN
  const faker = createFaker(SEEDS[id - 1])
  const providedIn = faker.randomDateTimeString({ yearMonth: args?.providedIn ?? '2021-02' })
  const yearMonth = providedIn.substr(0, 7)
  const status = faker.randomElement(DwsProvisionReportStatus.values)
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
    /** 予定 */
    plans: createDwsProvisionReportItemStubs(yearMonth, faker.intBetween(10, 20)),
    /** 実績 */
    results: createDwsProvisionReportItemStubs(yearMonth, faker.intBetween(10, 20), 10),
    /** 状態 */
    status,
    /** 確定日時 */
    fixedAt: status === DwsProvisionReportStatus.fixed ? faker.randomDateString() : undefined,
    /** 登録日時 */
    createdAt: faker.randomDateString(),
    /** 更新日時 */
    updatedAt: faker.randomDateString()
  }
}
