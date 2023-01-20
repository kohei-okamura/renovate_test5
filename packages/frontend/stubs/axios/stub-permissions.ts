/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createPermissionIndexResponseStub } from '~~/stubs/create-permission-index-response-stub'

/**
 * 権限 API をスタブ化する.
 */
export const stubPermissions: StubFunction = mockAdapter => mockAdapter
  .onGet('/api/permissions').reply(HttpStatusCode.OK, createPermissionIndexResponseStub())
