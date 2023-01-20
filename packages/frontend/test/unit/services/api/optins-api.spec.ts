/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { OfficeQualification } from '@zinger/enums/lib/office-qualification'
import { Permission } from '@zinger/enums/lib/permission'
import { Purpose } from '@zinger/enums/lib/purpose'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { VSelectOption } from '~/models/vuetify'
import { OptionsApi } from '~/services/api/options-api'
import { createOfficeGroupStubs } from '~~/stubs/create-office-group-stub'
import { createOfficeStubs } from '~~/stubs/create-office-stub'
import { createRoleStubs } from '~~/stubs/create-role-stub'
import { createStaffStubs } from '~~/stubs/create-staff-stub'
import { createUserStubs } from '~~/stubs/create-user-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/options-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let options: OptionsApi.Definition

  beforeEach(() => {
    options = OptionsApi.create(axios)
  })

  describe.each<[keyof OptionsApi.Definition, string, VSelectOption[]]>([
    ['officeGroups', 'office-groups', createOfficeGroupStubs().map(x => ({ value: x.id, text: x.name }))],
    ['offices', 'offices', createOfficeStubs().map(x => ({ value: x.id, text: x.abbr, keyword: x.name }))],
    ['roles', 'roles', createRoleStubs().map(x => ({ value: x.id, text: x.name }))],
    ['staffs', 'staffs', createStaffStubs().map(x => ({ value: x.id, text: x.name.displayName }))],
    ['users', 'users', createUserStubs().map(x => ({ value: x.id, text: x.name.displayName }))]
  ])('%s', (api, name, stubs) => {
    it(`should get /api/options/${name}`, async () => {
      const params = { permission: Permission.createBillings }
      const url = `/api/options/${name}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, stubs)
      })

      await options[api](params)

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'get', params, url })
    })

    it('should return response of the api', async () => {
      const expected = stubs
      adapter.setup(x => {
        x.onGet(`/api/options/${name}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const actual = await options[api]({ permission: Permission.listStaffs })

      expect(actual).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet(`/api/options/${name}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = options[api]({ permission: Permission.viewUsers })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('offices', () => {
    const stubs = createOfficeStubs().map(x => ({ value: x.id, text: x.abbr, keyword: x.name }))
    it.each<string, Purpose | undefined>([
      ['without purpose', undefined],
      ['with purpose', Purpose.internal]
    ])('should access to /api/options/offices %s', async (_, purpose) => {
      const params = {
        permission: Permission.createBillings,
        qualifications: [OfficeQualification.ltcsCareManagement],
        ...purpose ? { purpose } : undefined
      }
      const url = '/api/options/offices'
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, stubs)
      })

      await options.offices(params)

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'get', params, url })
    })
  })
})
