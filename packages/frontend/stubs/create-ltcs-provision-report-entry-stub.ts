/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsProjectAmountCategory } from '@zinger/enums/lib/ltcs-project-amount-category'
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { ServiceOption } from '@zinger/enums/lib/service-option'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { range } from '@zinger/helpers'
import { LtcsProvisionReportEntry } from '~/models/ltcs-provision-report-entry'
import { $datetime } from '~/services/datetime-service'
import { getLtcsHomeVisitLongTermCareServiceCodeList } from '~~/stubs/create-ltcs-home-visit-long-term-care-dictionary-entry-stub'
import { createFaker } from '~~/stubs/fake'
import { ID_MAX, ID_MIN, SEEDS } from '~~/stubs/index'

export const LTCS_PROVISION_REPORT_ENTRY_ID_MAX = ID_MAX
export const LTCS_PROVISION_REPORT_ENTRY_ID_MIN = ID_MIN
export const LTCS_PROVISION_REPORT_ENTRY_STUB_COUNT = 10

export function createLtcsProvisionReportEntryStub (
  yearMonth: string,
  id: number = LTCS_PROVISION_REPORT_ENTRY_ID_MIN
): LtcsProvisionReportEntry {
  const serviceCodes = getLtcsHomeVisitLongTermCareServiceCodeList()
  const faker = createFaker(SEEDS[id - 1])
  const datetime = $datetime.parse(faker.randomDateTimeString())
  const randomDates = () => [...new Set(range(1, 10).map(_ => faker.randomDateString({ yearMonth })))]
  const category = faker.randomElement(LtcsProjectServiceCategory.values)
  const amounts = (c => {
    switch (c) {
      case LtcsProjectServiceCategory.physicalCare:
        return [{
          category: LtcsProjectAmountCategory.physicalCare,
          amount: faker.intBetween(1, 1440)
        }]
      case LtcsProjectServiceCategory.housework:
        return [{
          category: LtcsProjectAmountCategory.housework,
          amount: faker.intBetween(1, 1440)
        }]
      case LtcsProjectServiceCategory.physicalCareAndHousework:
        return [
          {
            category: LtcsProjectAmountCategory.physicalCare,
            amount: faker.intBetween(1, 1440)
          },
          {
            category: LtcsProjectAmountCategory.housework,
            amount: faker.intBetween(1, 1440)
          }
        ]
      case LtcsProjectServiceCategory.ownExpense:
      default:
        return []
    }
  })(category)
  const [ownExpenseProgramId, serviceCode] = ((c): [number | undefined, string] => {
    return c === LtcsProjectServiceCategory.ownExpense
      ? [faker.intBetween(1, 2), '']
      : [undefined, faker.randomElement(serviceCodes)]
  })(category)

  return {
    /** 時間帯 */
    slot: {
      start: datetime.toFormat('HH:mm'),
      end: datetime.plus({ hours: faker.intBetween(1, 3) }).toFormat('HH:mm')
    },
    /** 算定時間帯 */
    timeframe: faker.randomElement(Timeframe.values),
    /** サービス区分 */
    category,
    /** サービス提供量 */
    amounts,
    /** 提供人数 */
    headcount: faker.intBetween(1, 2),
    /** 自費サービス情報 ID */
    ownExpenseProgramId,
    /** サービスコード */
    serviceCode,
    /** サービスオプション */
    options: faker.randomElements(ServiceOption.values, 3),
    /** 備考 */
    note: '',
    /** 予定年月日 */
    plans: randomDates(),
    /** 実績年月日 */
    results: randomDates()
  }
}

export function createLtcsProvisionReportEntryStubs (
  yearMonth: string,
  n = LTCS_PROVISION_REPORT_ENTRY_STUB_COUNT,
  skip = 0
): LtcsProvisionReportEntry[] {
  const start = LTCS_PROVISION_REPORT_ENTRY_ID_MIN + skip
  const end = Math.min(start + n - 1, LTCS_PROVISION_REPORT_ENTRY_ID_MAX)
  return range(start, end).map(v => createLtcsProvisionReportEntryStub(yearMonth, v))
}
