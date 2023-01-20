/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createOfficeGroupIndexResponseStub } from '~~/stubs/create-office-group-index-response-stub'
import { createOfficeGroupResponseStub } from '~~/stubs/create-office-group-response-stub'

/**
 * 事業所グループ API をスタブ化する.
 */
export const stubOfficeGroups: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/office-groups\/(\d+)$/).reply(config => {
    const m = config.url!.match(/\/(\d+)$/)
    const id = m && +m[1]
    return id ? [HttpStatusCode.OK, createOfficeGroupResponseStub(id)] : [HttpStatusCode.NotFound]
  })
  .onGet('/api/office-groups').reply(HttpStatusCode.OK, createOfficeGroupIndexResponseStub())
  .onPost('/api/office-groups').reply(HttpStatusCode.Created)
  .onPut('/api/office-groups').reply(HttpStatusCode.NoContent)
  .onPut(/\/api\/office-groups\/\d+$/).reply(HttpStatusCode.NoContent)
  .onDelete(/\/api\/office-groups\/\d+$/).reply(HttpStatusCode.NoContent)
