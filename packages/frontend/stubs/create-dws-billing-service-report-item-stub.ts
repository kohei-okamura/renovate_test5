/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingServiceReportProviderType } from '@zinger/enums/lib/dws-billing-service-report-provider-type'
import { DwsBillingServiceReportSituation } from '@zinger/enums/lib/dws-billing-service-report-situation'
import { DwsGrantedServiceCode } from '@zinger/enums/lib/dws-granted-service-code'
import { range } from '@zinger/helpers/index'
import { DateTime } from 'luxon'
import { DateString, ISO_DATETIME_FORMAT } from '~/models/date'
import { DwsBillingBundle } from '~/models/dws-billing-bundle'
import { DwsBillingServiceReportId } from '~/models/dws-billing-service-report'
import { DwsBillingServiceReportItem } from '~/models/dws-billing-service-report-item'
import { $datetime } from '~/services/datetime-service'
import {
  DWS_BILLING_SEEDS,
  DWS_BILLING_SERVICE_REPORT_ITEM_STUB_COUNT_PER_REPORT
} from '~~/stubs/create-dws-billing-stub-settings'
import { createFaker } from '~~/stubs/fake'
import ramenIpsum from '~~/stubs/fake/ramen-ipsum'

type CreateDwsBillingServiceReportItemStubParams = {
  id: DwsBillingServiceReportId
  providedOn: DateString
  serialNumber: number
}

type CreateDwsBillingServiceReportItemStub = {
  (params: CreateDwsBillingServiceReportItemStubParams): DwsBillingServiceReportItem
}

export const createDwsBillingServiceReportItemStub: CreateDwsBillingServiceReportItemStub = params => {
  const { id, providedOn, serialNumber } = params
  const seed = DWS_BILLING_SEEDS[(id - 1) * DWS_BILLING_SERVICE_REPORT_ITEM_STUB_COUNT_PER_REPORT + serialNumber]
  const faker = createFaker(seed)
  const ramen = ramenIpsum.factory(seed)
  const hasPlan = faker.intBetween(1, 10) < 8
  const hasResult = faker.intBetween(1, 10) < 8 || !hasPlan
  const datetime = $datetime.parse(providedOn)
  const start = datetime.plus({ hours: faker.intBetween(19, 22) })
  const end = start.plus({
    hours: faker.intBetween(4, 7),
    minutes: faker.randomElement([0, 10, 20, 30, 40, 50])
  })
  const period = {
    start: start.toFormat(ISO_DATETIME_FORMAT),
    end: end.toFormat(ISO_DATETIME_FORMAT)
  }
  const movingDurationHours = faker.randomElement([0, 5, 10, 15, 20, 25, 30, 35, 40]) * 10000
  const serviceDurationHours = faker.intBetween(0, 48) * 50000
  return {
    serialNumber,
    providedOn,
    serviceType: faker.randomElement(DwsGrantedServiceCode.values),
    providerType: faker.randomElement(DwsBillingServiceReportProviderType.values),
    situation: faker.randomElement(DwsBillingServiceReportSituation.values),
    plan: hasPlan ? { period, movingDurationHours, serviceDurationHours } : undefined,
    result: hasResult ? { period, serviceDurationHours, movingDurationHours } : undefined,
    serviceCount: faker.intBetween(1, 20),
    headcount: faker.intBetween(1, 2),
    isCoaching: faker.intBetween(1, 100) < 5,
    isFirstTime: faker.intBetween(1, 100) < 5,
    isEmergency: faker.intBetween(1, 100) < 5,
    isWelfareSpecialistCooperation: faker.intBetween(1, 100) < 5,
    isBehavioralDisorderSupportCooperation: faker.intBetween(1, 100) < 5,
    isMovingCareSupport: faker.intBetween(1, 100) < 5,
    isDriving: false,
    isPreviousMonth: faker.randomBoolean(),
    note: ramen.ipsum(100)
  }
}

type CreateDwsBillingServiceReportItemStubsParams = {
  bundle: DwsBillingBundle
  id: DwsBillingServiceReportId
  numberOfItems?: number
}

type CreateDwsBillingServiceReportItemStubs = {
  (params: CreateDwsBillingServiceReportItemStubsParams): DwsBillingServiceReportItem[]
}

export const createDwsBillingServiceReportItemStubs: CreateDwsBillingServiceReportItemStubs = (
  {
    bundle,
    id,
    numberOfItems
  }
) => {
  const faker = createFaker(DWS_BILLING_SEEDS[id - 1])
  const date = DateTime.fromISO(`${bundle.providedIn}-01`)
  const dateRange = {
    min: date.toSeconds(),
    max: date.endOf('month').toSeconds()
  }
  return range(1, numberOfItems ?? DWS_BILLING_SERVICE_REPORT_ITEM_STUB_COUNT_PER_REPORT)
    .map(() => faker.randomDateString({ range: dateRange }))
    .sort()
    .map((providedOn, i) => createDwsBillingServiceReportItemStub({ id, providedOn, serialNumber: i + 1 }))
}
