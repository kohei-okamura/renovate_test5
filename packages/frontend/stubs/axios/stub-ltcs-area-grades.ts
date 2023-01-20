/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createLtcsAreaGradeIndexResponseStub } from '~~/stubs/create-ltcs-area-grade-index-response-stub'

/**
 * 介護保険サービス：地域区分 API をスタブ化する.
 */
export const stubLtcsAreaGrades: StubFunction = mockAdapter => mockAdapter
  .onGet('/api/ltcs-area-grades').reply(HttpStatusCode.OK, createLtcsAreaGradeIndexResponseStub())
