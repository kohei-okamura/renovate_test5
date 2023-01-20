/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsProvisionReportsApi } from '~/services/api/dws-provision-reports-api'
import { createDwsProvisionReportStub } from '~~/stubs/create-dws-provision-report-stub'

type Arguments = {
  id?: number
  providedIn?: string
}

export function createDwsProvisionReportResponseStub (args: Arguments = {}): DwsProvisionReportsApi.GetResponse {
  return {
    dwsProvisionReport: createDwsProvisionReportStub(args)
  }
}
