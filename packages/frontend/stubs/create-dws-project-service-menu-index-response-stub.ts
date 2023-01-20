/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsProjectServiceMenusApi } from '~/services/api/dws-project-service-menus-api'
import {
  createDwsProjectServiceMenuStubs,
  DWS_PROJECT_SERVICE_MENU_ID_MAX
} from '~~/stubs/create-dws-project-service-menu-stub'
import { createIndexResponse } from '~~/stubs/create-index-response'

export function createDwsProjectServiceMenuIndexResponseStub (
  params: DwsProjectServiceMenusApi.GetIndexParams = {}
): DwsProjectServiceMenusApi.GetIndexResponse {
  return createIndexResponse(params, DWS_PROJECT_SERVICE_MENU_ID_MAX, createDwsProjectServiceMenuStubs)
}
