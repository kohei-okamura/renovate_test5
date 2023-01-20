/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { LtcsProjectAmountCategory } from '@zinger/enums/lib/ltcs-project-amount-category'
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { ServiceOption } from '@zinger/enums/lib/service-option'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZLtcsProvisionReportEntryBrowsingDialog
  from '~/components/domain/provision-report/z-ltcs-provision-report-entry-browsing-dialog.vue'
import { ownExpenseProgramResolverStoreKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import {
  createLtcsHomeVisitLongTermCareDictionaryStubByServiceCode
} from '~~/stubs/create-ltcs-home-visit-long-term-care-dictionary-entry-stub'
import { createOwnExpenseProgramResolverStoreStub } from '~~/stubs/create-own-expense-program-resolver-stub'
import { createOwnExpenseProgramStubs } from '~~/stubs/create-own-expense-program-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-ltcs-provision-report-entry-browsing-dialog.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('ltcsHomeVisitLongTermCareDictionary')
  const propsData = {
    isEffectiveOn: '2021-02-01',
    officeId: 517,
    show: true,
    value: {
      slot: { start: '09:00', end: '10:00' },
      timeframe: Timeframe.daytime,
      category: LtcsProjectServiceCategory.physicalCare,
      amounts: [
        { category: LtcsProjectAmountCategory.physicalCare, amount: 30 }
      ],
      headcount: 1,
      ownExpenseProgramId: undefined,
      serviceCode: '111111',
      options: [ServiceOption.notificationEnabled, ServiceOption.firstTime, ServiceOption.providedByBeginner],
      note: '備考',
      plans: [],
      results: []
    },
    width: '50%'
  }
  const ownExpenseProgramResolverStore = createOwnExpenseProgramResolverStoreStub({
    ownExpensePrograms: createOwnExpenseProgramStubs()
  })

  let wrapper: Wrapper<Vue>

  function mountComponent (options: MountOptions<Vue>) {
    wrapper = mount(ZLtcsProvisionReportEntryBrowsingDialog, {
      ...options,
      ...provides(
        [ownExpenseProgramResolverStoreKey, ownExpenseProgramResolverStore]
      ),
      mocks: {
        $api
      }
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    jest.spyOn($api.ltcsHomeVisitLongTermCareDictionary, 'getIndex').mockImplementation(params => {
      const x = createLtcsHomeVisitLongTermCareDictionaryStubByServiceCode(params?.q ?? '')
      const list = x === undefined ? [] : [x]
      const pagination = {
        count: list.length,
        desc: false,
        itemsPerPage: list.length,
        page: 1,
        pages: 1,
        sortBy: ''
      }
      return Promise.resolve({ list, pagination })
    })
  })

  afterEach(() => {
    mocked($api.ltcsHomeVisitLongTermCareDictionary.getIndex).mockRestore()
  })

  it('should be rendered correctly', () => {
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })
})
