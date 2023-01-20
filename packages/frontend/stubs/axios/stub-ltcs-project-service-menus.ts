/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createLtcsProjectServiceMenuIndexResponseStub } from '~~/stubs/create-ltcs-project-service-menu-index-response-stub'

/**
 * 介護保険サービス：計画：サービス項目 API をスタブ化する.
 */
export const stubLtcsProjectServiceMenus: StubFunction = mockAdapter => mockAdapter
  .onGet('/api/ltcs-project-service-menus')
  .reply(config => [HttpStatusCode.OK, createLtcsProjectServiceMenuIndexResponseStub(config.params)])
