/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { DwsProjectServiceCategory } from '@zinger/enums/lib/dws-project-service-category'
import Vue from 'vue'
import ZDwsProvisionReportItemCopyDialog
  from '~/components/domain/provision-report/z-dws-provision-report-item-copy-dialog.vue'
import { ownExpenseProgramResolverStoreKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { DateLike } from '~/models/date'
import { DwsProvisionReportItem } from '~/models/dws-provision-report-item'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createOwnExpenseProgramResolverStoreStub } from '~~/stubs/create-own-expense-program-resolver-stub'
import { createOwnExpenseProgramStubs } from '~~/stubs/create-own-expense-program-stub'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click, submit } from '~~/test/helpers/trigger'

describe('z-dws-provision-report-item-copy-dialog.vue', () => {
  type Form = { dates: DateLike[] }
  const { mount } = setupComponentTest()
  const form: Form = {
    dates: ['2020-05-01', '2020-05-02', '2020-05-03']
  }
  const item: Partial<DwsProvisionReportItem> = {
    schedule: {
      date: '2021-05-04',
      start: '2021-05-04T09:00:00+0900',
      end: '2021-05-04T10:00:00+0900'
    },
    movingDurationMinutes: 30,
    category: DwsProjectServiceCategory.ownExpense,
    ownExpenseProgramId: 1,
    headcount: 1,
    options: [],
    note: 'これは備考です。'
  }
  const ownExpenseProgramResolverStore = createOwnExpenseProgramResolverStoreStub({
    ownExpensePrograms: createOwnExpenseProgramStubs()
  })

  let wrapper: Wrapper<Vue & any>

  function mountComponent (options?: MountOptions<Vue>) {
    wrapper = mount(ZDwsProvisionReportItemCopyDialog, {
      ...options,
      ...provides(
        [ownExpenseProgramResolverStoreKey, ownExpenseProgramResolverStore]
      ),
      propsData: {
        copyableDates: ['2020-05-01', '2020-05-02', '2020-05-03'],
        show: true,
        target: '予定',
        value: item,
        width: '50%'
      }
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  afterEach(() => {
    jest.clearAllMocks()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (cb: () => void, values?: Form) {
      await setData(wrapper, {
        form: { ...form, ...(values ?? {}) }
      })
      await observer.validate()
      jest.runOnlyPendingTimers()
      cb()
      observer.reset()
    }

    beforeAll(() => {
      mountComponent()
      observer = getValidationObserver(wrapper)
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should pass when input correctly', async () => {
      await validate(
        () => {
          expect(observer).toBePassed()
        }
      )
    })

    it('should fail if dates is empty', async () => {
      await validate(
        () => {
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-dates-length] .v-messages').text()).toBe('コピー先の日付を1つ以上選択してください。')
        },
        {
          dates: []
        }
      )
    })
  })

  describe('event', () => {
    beforeEach(() => {
      mountComponent()
    })

    afterEach(() => {
      unmountComponent()
    })

    it('should emit click:cancel when cancel button clicked', async () => {
      const button = wrapper.find('[data-cancel]')
      const eventName = 'click:cancel'
      await click(() => button)
      expect(wrapper.emitted(eventName)).toBeTruthy()
      expect(wrapper.emitted(eventName)?.length).toBe(1)
    })

    it('should emit click:save when ok button clicked', async () => {
      await setData(wrapper, { form })
      const button = wrapper.find('[data-ok]')
      const eventName = 'click:save'
      await submit(() => button)
      expect(wrapper.emitted(eventName)).toBeTruthy()
      expect(wrapper.emitted(eventName)!.length).toBe(1)
      expect(wrapper.emitted(eventName)![0][0]).toStrictEqual(form.dates)
    })
  })
})
