/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { DwsProjectServiceCategory } from '@zinger/enums/lib/dws-project-service-category'
import deepmerge from 'deepmerge'
import Vue from 'vue'
import ZDwsProvisionReportItemFormDialog
  from '~/components/domain/provision-report/z-dws-provision-report-item-form-dialog.vue'
import { ownExpenseProgramResolverStoreKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { DwsProvisionReportItem } from '~/models/dws-provision-report-item'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createOfficeStub } from '~~/stubs/create-office-stub'
import { createOwnExpenseProgramResolverStoreStub } from '~~/stubs/create-own-expense-program-resolver-stub'
import { createOwnExpenseProgramStubs } from '~~/stubs/create-own-expense-program-stub'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click, submit } from '~~/test/helpers/trigger'

describe('z-dws-provision-report-item-form-dialog.vue', () => {
  const { mount } = setupComponentTest()
  const key = 'specific key'
  const form: Partial<DwsProvisionReportItem> = {
    schedule: {
      date: '2021-02-28',
      start: '2021-02-28T09:00:00+0900',
      end: '2021-02-28T10:00:00+0900'
    },
    movingDurationMinutes: 30,
    category: DwsProjectServiceCategory.ownExpense,
    ownExpenseProgramId: 2,
    headcount: 1,
    options: undefined,
    note: ''
  }
  const ownExpenseProgramResolverStore = createOwnExpenseProgramResolverStoreStub({
    ownExpensePrograms: createOwnExpenseProgramStubs()
  })
  const officeId = createOfficeStub().id

  let wrapper: Wrapper<Vue & any>

  function mountComponent ({ options, item }: {
    options?: MountOptions<Vue>
    item?: DeepPartial<DwsProvisionReportItem>
  } = {}) {
    wrapper = mount(ZDwsProvisionReportItemFormDialog, {
      ...options,
      ...provides(
        [ownExpenseProgramResolverStoreKey, ownExpenseProgramResolverStore]
      ),
      propsData: {
        officeId,
        providedIn: '2021-02',
        show: true,
        target: '予定',
        width: '50%',
        value: {
          key,
          item: deepmerge(form, item ?? {})
        }
      }
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  afterEach(() => {
    jest.clearAllMocks()
  })

  describe('initial view', () => {
    afterEach(() => {
      unmountComponent()
    })

    it('should be rendered correctly', () => {
      mountComponent()
      expect(wrapper).toMatchSnapshot()
    })

    it('should be rendered "追加" in ok button\'s label if schedule date is not set', () => {
      mountComponent({
        item: {
          schedule: {
            date: undefined,
            start: undefined,
            end: undefined
          }
        }
      })
      expect(wrapper.find('[data-ok]').text()).toBe('追加')
    })

    it('should be rendered "編集" in ok button\'s label if schedule date is set', () => {
      mountComponent()
      expect(wrapper.find('[data-ok]').text()).toBe('編集')
    })
  })

  describe('correct show by category', () => {
    afterEach(() => {
      unmountComponent()
    })

    it.each([
      ['physicalCare', DwsProjectServiceCategory.physicalCare],
      ['housework', DwsProjectServiceCategory.housework],
      ['accompanyWithPhysicalCare', DwsProjectServiceCategory.accompanyWithPhysicalCare],
      ['accompany', DwsProjectServiceCategory.accompany],
      ['ownExpense', DwsProjectServiceCategory.ownExpense]
    ])('should not show movingDurationMinutes form when category is %s', (_, category: DwsProjectServiceCategory) => {
      mountComponent({
        item: {
          category
        }
      })
      expect(wrapper.find('[data-moving-duration-minutes]')).not.toExist()
    })

    it('should show movingDurationMinutes form when category is visitingCareForPwsd', () => {
      mountComponent({
        item: {
          category: DwsProjectServiceCategory.visitingCareForPwsd
        }
      })
      expect(wrapper.find('[data-moving-duration-minutes]')).toExist()
    })
  })

  describe('validation', () => {
    type Values = DeepPartial<Omit<DwsProvisionReportItem, 'movingDurationMinutes'> & { movingDurationMinutes: string }>
    let observer: ValidationObserverInstance

    async function validate (cb: () => void, values?: Values) {
      await setData(wrapper, {
        form: deepmerge(form, values ?? {})
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

    it('should fail when schedule start is empty', async () => {
      await validate(
        () => {
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-schedule-start] .v-messages').text()).toBe('入力してください。')
        },
        {
          schedule: { start: '', end: '10:00' }
        }
      )
    })

    it('should fail when schedule end is empty', async () => {
      await validate(
        () => {
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-schedule-end] .v-messages').text()).toBe('入力してください。')
        },
        {
          schedule: { start: '09:00', end: '' }
        })
    })

    it('should fail when movingDurationMinutes is not number', async () => {
      await validate(
        () => {
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-moving-duration-minutes] .v-messages').text()).toBe('半角数字のみで入力してください。')
        },
        {
          category: DwsProjectServiceCategory.visitingCareForPwsd,
          movingDurationMinutes: 'abc'
        })
    })

    it('should fail when category is empty', async () => {
      await validate(
        () => {
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-category] .v-messages').text()).toBe('入力してください。')
        },
        {
          category: undefined
        }
      )
    })

    it('should fail when ownExpenseProgramId is empty', async () => {
      await validate(
        () => {
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-own-expense-program-id] .v-messages').text()).toBe('入力してください。')
        },
        {
          ownExpenseProgramId: undefined
        }
      )
    })

    it.each([
      ['physicalCare', DwsProjectServiceCategory.physicalCare],
      ['housework', DwsProjectServiceCategory.housework],
      ['accompanyWithPhysicalCare', DwsProjectServiceCategory.accompanyWithPhysicalCare],
      ['accompany', DwsProjectServiceCategory.accompany],
      ['visitingCareForPwsd', DwsProjectServiceCategory.visitingCareForPwsd]
    ])('should not fail if ownExpenseProgramId is empty when category is %s', async (_, category) => {
      await validate(
        () => {
          expect(observer).toBePassed()
        },
        {
          category,
          ownExpenseProgramId: undefined
        }
      )
    })

    it('should fail when headcount is empty', async () => {
      await validate(
        () => {
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-headcount] .v-messages').text()).toBe('入力してください。')
        },
        {
          headcount: undefined
        }
      )
    })

    it('should fail if note is over 255 characters', async () => {
      const max = 255
      await validate(
        () => {
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-note] .v-messages').text()).toBe(`${max}文字以内で入力してください。`)
        },
        {
          note: 'a'.repeat(max + 1)
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
      const item = { ...form, movingDurationMinutes: 0, options: [] }
      const button = wrapper.find('[data-ok]')
      const eventName = 'click:save'
      await submit(() => button)
      expect(wrapper.emitted(eventName)).toBeTruthy()
      expect(wrapper.emitted(eventName)!.length).toBe(1)
      expect(wrapper.emitted(eventName)![0][0]).toStrictEqual({ key, item })
    })
  })
})
