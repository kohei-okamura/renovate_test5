/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createLtcsProjectResponseStub } from '~~/stubs/create-ltcs-project-response-stub'

/**
 * 介護保険サービス：計画 API をスタブ化する.
 */
export const stubLtcsProjects: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/users\/(\d+)\/ltcs-projects\/(\d+)$/).reply(config => {
    const m = config.url!.match(/\/api\/users\/(\d+)\/ltcs-projects\/(\d+)$/)
    const id = m && +m[2]
    const userId = m && +m[1]
    const stub = id && userId && createLtcsProjectResponseStub(id)
    return stub ? [HttpStatusCode.OK, stub] : [HttpStatusCode.NotFound]
  })
  .onPost(/\/api\/users\/(\d+)\/ltcs-projects$/).reply(HttpStatusCode.Created)
  .onPut(/\/api\/users\/(\d+)\/ltcs-projects\/\d+$/).reply(HttpStatusCode.NoContent)
  .onDelete(/\/api\/users\/(\d+)\/ltcs-projects\/\d+$/).reply(HttpStatusCode.NoContent)
