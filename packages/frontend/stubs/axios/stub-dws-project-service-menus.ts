/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createDwsProjectServiceMenuIndexResponseStub } from '~~/stubs/create-dws-project-service-menu-index-response-stub'

/**
 * 障害福祉サービス：計画：サービス項目 API をスタブ化する.
 */
export const stubDwsProjectServiceMenus: StubFunction = mockAdapter => mockAdapter
  .onGet('/api/dws-project-service-menus')
  .reply(config => [HttpStatusCode.OK, createDwsProjectServiceMenuIndexResponseStub(config.params)])
