/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { DwsProjectServiceCategory } from '@zinger/enums/lib/dws-project-service-category'
import { ServiceOption } from '@zinger/enums/lib/service-option'
import Vue from 'vue'
import ZDwsProvisionReportItemBrowsingDialog
  from '~/components/domain/provision-report/z-dws-provision-report-item-browsing-dialog.vue'
import { ownExpenseProgramResolverStoreKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { createOwnExpenseProgramResolverStoreStub } from '~~/stubs/create-own-expense-program-resolver-stub'
import { createOwnExpenseProgramStubs } from '~~/stubs/create-own-expense-program-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-dws-provision-report-item-browsing-dialog.vue', () => {
  const { mount } = setupComponentTest()
  const propsData = {
    show: true,
    target: '予定',
    value: {
      schedule: {
        date: '2021-07-09',
        start: '2021-07-09T09:00:00+0900',
        end: '2021-07-09T10:00:00+0900'
      },
      movingDurationMinutes: 30,
      category: DwsProjectServiceCategory.ownExpense,
      ownExpenseProgramId: 1,
      headcount: 1,
      options: [ServiceOption.notificationEnabled, ServiceOption.firstTime, ServiceOption.providedByBeginner],
      note: ''
    },
    width: '50%'
  }
  const ownExpenseProgramResolverStore = createOwnExpenseProgramResolverStoreStub({
    ownExpensePrograms: createOwnExpenseProgramStubs()
  })

  let wrapper: Wrapper<Vue>

  function mountComponent (options: MountOptions<Vue>) {
    wrapper = mount(ZDwsProvisionReportItemBrowsingDialog, {
      ...options,
      ...provides(
        [ownExpenseProgramResolverStoreKey, ownExpenseProgramResolverStore]
      )
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })
})
