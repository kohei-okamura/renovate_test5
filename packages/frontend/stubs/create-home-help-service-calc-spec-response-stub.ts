/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HomeHelpServiceCalcSpecId } from '~/models/home-help-service-calc-spec'
import { HomeHelpServiceCalcSpecsApi } from '~/services/api/home-help-service-calc-specs-api'
import {
  createHomeHelpServiceCalcSpecStub,
  HOME_HELP_SERVICE_CALC_SPEC_ID_MIN
} from '~~/stubs/create-home-help-service-calc-spec-stub'

export function createHomeHelpServiceCalcSpecResponseStub (
  id: HomeHelpServiceCalcSpecId = HOME_HELP_SERVICE_CALC_SPEC_ID_MIN
): HomeHelpServiceCalcSpecsApi.GetResponse {
  return {
    homeHelpServiceCalcSpec: createHomeHelpServiceCalcSpecStub(id)
  }
}
