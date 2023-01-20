/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createOwnExpenseProgramIndexResponseStub } from '~~/stubs/create-own-expense-program-index-response-stub'
import { createOwnExpenseProgramResponseStub } from '~~/stubs/create-own-expense-program-response-stub'

/**
 * 自費サービス API をスタブ化する.
 */
export const stubOwnExpensePrograms: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/own-expense-programs\/(\d+)$/).reply(config => {
    const m = config.url!.match(/\/(\d+)$/)
    const id = m && +m[1]
    return id ? [HttpStatusCode.OK, createOwnExpenseProgramResponseStub(id)] : [HttpStatusCode.NotFound]
  })
  .onGet('/api/own-expense-programs')
  .reply(config => [HttpStatusCode.OK, createOwnExpenseProgramIndexResponseStub(config.params)])
  .onPost('/api/own-expense-programs').reply(HttpStatusCode.Created)
  .onPut(/\/api\/own-expense-programs\/\d+$/).reply(HttpStatusCode.NoContent)
