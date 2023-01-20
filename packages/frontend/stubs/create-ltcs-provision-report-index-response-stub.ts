/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsProvisionReportsApi } from '~/services/api/ltcs-provision-reports-api'
import { createIndexResponse } from '~~/stubs/create-index-response'
import {
  createLtcsProvisionReportDigestStubs,
  LTCS_PROVISION_REPORT_DIGEST_STUB_COUNT
} from '~~/stubs/create-ltcs-provision-report-digest-stub'

export function createLtcsProvisionReportIndexResponseStub (
  params: LtcsProvisionReportsApi.GetIndexParams = {}
): LtcsProvisionReportsApi.GetIndexResponse {
  return createIndexResponse(params, LTCS_PROVISION_REPORT_DIGEST_STUB_COUNT, createLtcsProvisionReportDigestStubs)
}
