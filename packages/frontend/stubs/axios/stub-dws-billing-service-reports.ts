/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createDwsBillingServiceReportResponseStub } from '~~/stubs/create-dws-billing-service-report-response-stub'

const urlPattern = '/api/dws-billings/\\d+/bundles/\\d+/reports/\\d+'

/**
 * 障害福祉サービス：請求：サービス提供実績記録票 API をスタブ化する.
 */
export const stubDwsDwsBillingServiceReports: StubFunction = mockAdapter => mockAdapter
  .onGet(new RegExp(urlPattern)).reply(config => {
    const m = config.url!.match(/\/(\d+)\/bundles/)
    const id = (m && +m[1]) ?? undefined
    return [HttpStatusCode.OK, createDwsBillingServiceReportResponseStub(id)]
  })
  .onPut(new RegExp(`${urlPattern}/status$`)).reply(config => {
    const m = config.url!.match(/\/(\d+)\/bundles/)
    const id = m && m.length >= 2 ? +m[1] : undefined
    return [HttpStatusCode.OK, createDwsBillingServiceReportResponseStub(id)]
  })
