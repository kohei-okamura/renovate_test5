/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsProjectServiceMenusApi } from '~/services/api/ltcs-project-service-menus-api'
import { createIndexResponse } from '~~/stubs/create-index-response'
import {
  createLtcsProjectServiceMenuStubs,
  LTCS_PROJECT_SERVICE_MENU_ID_MAX
} from '~~/stubs/create-ltcs-project-service-menu-stub'

export function createLtcsProjectServiceMenuIndexResponseStub (
  params: LtcsProjectServiceMenusApi.GetIndexParams = {}
): LtcsProjectServiceMenusApi.GetIndexResponse {
  return createIndexResponse(params, LTCS_PROJECT_SERVICE_MENU_ID_MAX, createLtcsProjectServiceMenuStubs)
}
