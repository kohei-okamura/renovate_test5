/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createOfficeGroupStubs } from '~~/stubs/create-office-group-stub'
import { createOfficeStubs } from '~~/stubs/create-office-stub'
import { createRoleStubs } from '~~/stubs/create-role-stub'
import { createStaffStubs } from '~~/stubs/create-staff-stub'
import { createUserStubs } from '~~/stubs/create-user-stub'

/**
 * 選択肢 API をスタブ化する.
 */
export const stubOptions: StubFunction = mockAdapter => {
  mockAdapter.onGet('/api/options/office-groups').reply(() => [
    HttpStatusCode.OK,
    createOfficeGroupStubs().map(x => ({ value: x.id, text: x.name }))
  ])
  mockAdapter.onGet('/api/options/offices').reply(() => [
    HttpStatusCode.OK,
    createOfficeStubs().map(x => ({ value: x.id, text: x.abbr, keyword: x.name }))
  ])
  mockAdapter.onGet('/api/options/roles').reply(() => [
    HttpStatusCode.OK,
    createRoleStubs().map(x => ({ value: x.id, text: x.name }))
  ])
  mockAdapter.onGet('/api/options/staffs').reply(() => [
    HttpStatusCode.OK,
    createStaffStubs().map(x => ({ value: x.id, text: x.name.displayName }))
  ])
  mockAdapter.onGet('/api/options/users').reply(() => [
    HttpStatusCode.OK,
    createUserStubs().map(x => ({ value: x.id, text: x.name.displayName }))
  ])
}
