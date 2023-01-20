/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsProvisionReportsApi } from '~/services/api/dws-provision-reports-api'
import {
  createDwsProvisionReportDigestStubs,
  DWS_PROVISION_REPORT_DIGEST_STUB_COUNT
} from '~~/stubs/create-dws-provision-report-digest-stub'
import { createIndexResponse } from '~~/stubs/create-index-response'

export function createDwsProvisionReportIndexResponseStub (
  params: DwsProvisionReportsApi.GetIndexParams = {}
): DwsProvisionReportsApi.GetIndexResponse {
  return createIndexResponse(params, DWS_PROVISION_REPORT_DIGEST_STUB_COUNT, createDwsProvisionReportDigestStubs)
}
