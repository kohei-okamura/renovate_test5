/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsProvisionReportsApi } from '~/services/api/ltcs-provision-reports-api'
import { createLtcsProvisionReportStub } from '~~/stubs/create-ltcs-provision-report-stub'

type Arguments = {
  id?: number
  providedIn?: string
}

export function createLtcsProvisionReportResponseStub (args: Arguments = {}): LtcsProvisionReportsApi.GetResponse {
  return {
    ltcsProvisionReport: createLtcsProvisionReportStub(args)
  }
}
