/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createDwsBillingCopayCoordinationResponseStub } from '~~/stubs/create-dws-billing-copay-coordination-response-stub'

const baseUrlPattern = '/api/dws-billings/\\d+/bundles/\\d+/copay-coordinations'

/**
 * 障害福祉サービス：請求：利用者負担上限額管理結果票 API をスタブ化する.
 */
export const stubDwsBillingCopayCoordinations: StubFunction = mockAdapter => mockAdapter
  .onPost(new RegExp(baseUrlPattern)).reply(HttpStatusCode.Created)
  .onGet(new RegExp(`${baseUrlPattern}/\\d+$`))
  .reply(HttpStatusCode.OK, createDwsBillingCopayCoordinationResponseStub())
  .onPut(new RegExp(`${baseUrlPattern}/\\d+$`))
  .reply(HttpStatusCode.OK, createDwsBillingCopayCoordinationResponseStub())
  .onPut(new RegExp(`${baseUrlPattern}/\\d+/status$`))
  .reply(HttpStatusCode.OK, createDwsBillingCopayCoordinationResponseStub())
