/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createStaffIndexResponseStub } from '~~/stubs/create-staff-index-response-stub'
import { createStaffResponseStub } from '~~/stubs/create-staff-response-stub'
import { createStaffStub } from '~~/stubs/create-staff-stub'

const randomInt = (max: number) => Math.floor(Math.random() * Math.floor(max))
const staff = createStaffStub()

/**
 * スタッフ API をスタブ化する.
 */
export const stubStaffs: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/staffs\/(\d+)$/).reply(config => {
    if (randomInt(10) % 3 !== 0) {
      const m = config.url!.match(/\/(\d+)$/)
      const id = m && +m[1]
      return id ? [HttpStatusCode.OK, createStaffResponseStub(id)] : [HttpStatusCode.NotFound]
    } else {
      return [HttpStatusCode.ServiceUnavailable]
    }
  })
  .onGet('/api/staffs').reply(config => [HttpStatusCode.OK, createStaffIndexResponseStub(config.params)])
  .onPost('/api/staffs').reply(HttpStatusCode.Created, { staff })
  .onPut(/\/api\/staffs\/\d+$/).reply(HttpStatusCode.NoContent)
  .onDelete(/\/api\/staffs\/\d+$/).reply(HttpStatusCode.NoContent)
  .onPut(/\/api\/staffs\/(\d+)\/bank-account$/).reply(HttpStatusCode.NoContent)
