/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createCallingIndexResponseStub } from '~~/stubs/create-callings-index-response-stub'

/**
 * 出勤確認 API をスタブ化する.
 */
export const stubCallings: StubFunction = mockAdapter => mockAdapter
  .onPost(/\/api\/callings\/[a-zA-Z0-9]{60}\/acknowledges/).reply(HttpStatusCode.Created)
  .onGet(/\/api\/callings\/[a-zA-Z0-9]{60}\/shifts/).reply(config => {
    return [HttpStatusCode.OK, createCallingIndexResponseStub(config.params)]
  })
