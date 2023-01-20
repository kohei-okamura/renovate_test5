/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createDwsAreaGradeIndexResponseStub } from '~~/stubs/create-dws-area-grade-index-response-stub'

/**
 * 障害福祉サービス：地域区分 API をスタブ化する.
 */
export const stubDwsAreaGrades: StubFunction = mockAdapter => mockAdapter
  .onGet('/api/dws-area-grades').reply(HttpStatusCode.OK, createDwsAreaGradeIndexResponseStub())
