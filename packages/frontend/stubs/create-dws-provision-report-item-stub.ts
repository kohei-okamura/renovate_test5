/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsProjectServiceCategory } from '@zinger/enums/lib/dws-project-service-category'
import { ServiceOption } from '@zinger/enums/lib/service-option'
import { range } from '@zinger/helpers'
import { ISO_DATETIME_FORMAT } from '~/models/date'
import { DwsProvisionReportItem } from '~/models/dws-provision-report-item'
import { $datetime } from '~/services/datetime-service'
import { createFaker } from '~~/stubs/fake'
import { ID_MAX, ID_MIN, SEEDS } from '~~/stubs/index'

export const DWS_PROVISION_REPORT_ITEM_ID_MAX = ID_MAX
export const DWS_PROVISION_REPORT_ITEM_ID_MIN = ID_MIN
export const DWS_PROVISION_REPORT_ITEM_STUB_COUNT = 10

export function createDwsProvisionReportItemStub (
  yearMonth: string,
  id: number = DWS_PROVISION_REPORT_ITEM_ID_MIN
): DwsProvisionReportItem {
  const faker = createFaker(SEEDS[id - 1])
  const date = faker.randomDateString({ yearMonth })
  const datetime = $datetime.parse(date)
  const start = datetime.plus({ hours: faker.intBetween(19, 22) })
  const end = start.plus({
    hours: faker.intBetween(4, 7),
    minutes: faker.randomElement([0, 10, 20, 30, 40, 50])
  })
  const category = faker.randomElement(DwsProjectServiceCategory.values)
  return {
    /** スケジュール */
    schedule: {
      date,
      start: start.toFormat(ISO_DATETIME_FORMAT),
      end: end.toFormat(ISO_DATETIME_FORMAT)
    },
    /** サービス区分 */
    category,
    /** 提供人数 */
    headcount: faker.intBetween(1, 2),
    /** 移動介護時間数 */
    movingDurationMinutes: faker.intBetween(10, 300),
    /** 自費サービス情報 ID */
    ownExpenseProgramId: category === DwsProjectServiceCategory.ownExpense ? faker.intBetween(1, 2) : undefined,
    /** サービスオプション */
    options: faker.randomElements(ServiceOption.values, 3),
    /** 備考 */
    note: faker.randomElement(['備考です。'.repeat(faker.intBetween(1, 50)), ''])
  }
}

export function createDwsProvisionReportItemStubs (
  yearMonth: string,
  n = DWS_PROVISION_REPORT_ITEM_STUB_COUNT,
  skip = 0
): DwsProvisionReportItem[] {
  const start = DWS_PROVISION_REPORT_ITEM_ID_MIN + skip
  const end = Math.min(start + n - 1, DWS_PROVISION_REPORT_ITEM_ID_MAX)
  return range(start, end).map(v => createDwsProvisionReportItemStub(yearMonth, v))
}
