/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, nextTick } from '@nuxtjs/composition-api'
import { MountOptions, Slots, Wrapper } from '@vue/test-utils'
import { LtcsProjectAmountCategory } from '@zinger/enums/lib/ltcs-project-amount-category'
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { debounce } from '@zinger/helpers/lib/debounce'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import deepmerge from 'deepmerge'
import Vue from 'vue'
import ZLtcsProvisionReportEntryFormDialog
  from '~/components/domain/provision-report/z-ltcs-provision-report-entry-form-dialog.vue'
import { ownExpenseProgramResolverStoreKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { useLtcsHomeVisitLongTermCareDictionary } from '~/composables/use-ltcs-home-visit-long-term-care-dictionary'
import { LtcsProvisionReportEntry } from '~/models/ltcs-provision-report-entry'
import { LtcsHomeVisitLongTermCareDictionaryApi } from '~/services/api/ltcs-home-visit-long-term-care-dictionary-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import {
  createLtcsHomeVisitLongTermCareDictionaryStubsForSuggestion
} from '~~/stubs/create-ltcs-home-visit-long-term-care-dictionary-entry-stub'
import { createOwnExpenseProgramResolverStoreStub } from '~~/stubs/create-own-expense-program-resolver-stub'
import { createOwnExpenseProgramStubs } from '~~/stubs/create-own-expense-program-stub'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { provides } from '~~/test/helpers/provides'
import { setProps } from '~~/test/helpers/set-props'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click, submit } from '~~/test/helpers/trigger'

jest.useFakeTimers()
jest.mock('@zinger/helpers/lib/debounce')
jest.mock('~/composables/use-ltcs-home-visit-long-term-care-dictionary')

describe('z-ltcs-provision-report-entry-form-dialog.vue', () => {
  const { mount } = setupComponentTest()
  const index = 31
  const form: LtcsProvisionReportEntry = {
    slot: { start: '09:00', end: '10:00' },
    timeframe: Timeframe.daytime,
    category: LtcsProjectServiceCategory.physicalCare,
    amounts: [
      { category: LtcsProjectAmountCategory.physicalCare, amount: 30 }
    ],
    headcount: 1,
    ownExpenseProgramId: undefined,
    serviceCode: '111111',
    options: [],
    note: '',
    plans: [],
    results: []
  }
  const stubs = ['z-select', 'z-text-field', 'z-autocomplete', 'v-checkbox', 'z-textarea']
  type SearchParams = Partial<LtcsHomeVisitLongTermCareDictionaryApi.GetIndexParams>
  const mockedDictionary = createMock<ReturnType<typeof useLtcsHomeVisitLongTermCareDictionary>>({
    searchLtcsHomeVisitLongTermCareDictionary: computed(() => (params: SearchParams) => {
      const x = createLtcsHomeVisitLongTermCareDictionaryStubsForSuggestion({
        isEffectiveOn: '2021-02-01',
        officeId: 517,
        ...params
      })
      return Promise.resolve(x)
    })
  })
  const ownExpenseProgramResolverStore = createOwnExpenseProgramResolverStoreStub({
    ownExpensePrograms: createOwnExpenseProgramStubs()
  })

  let wrapper: Wrapper<Vue & any>

  type MountComponentParams = MountOptions<Vue> & {
    entry?: DeepPartial<LtcsProvisionReportEntry>
    readonly?: true
  }

  function mountComponent ({ entry, readonly, ...options }: MountComponentParams = {}) {
    wrapper = mount(ZLtcsProvisionReportEntryFormDialog, {
      ...options,
      ...provides(
        [ownExpenseProgramResolverStoreKey, ownExpenseProgramResolverStore]
      ),
      propsData: {
        isEffectiveOn: '2021-02',
        officeId: 517,
        show: true,
        readonly: readonly ?? false,
        value: {
          index,
          entry: deepmerge(form, entry ?? {})
        },
        width: '50%'
      }
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useLtcsHomeVisitLongTermCareDictionary).mockReturnValue(mockedDictionary)
    mocked(debounce).mockImplementation((_, f) => f)
  })

  afterAll(() => {
    mocked(debounce).mockReset()
    mocked(useLtcsHomeVisitLongTermCareDictionary).mockReset()
  })

  describe('initial view', () => {
    it('should be rendered correctly with slots', async () => {
      const slots: Slots = {
        'positive-label': '追加',
        title: 'サービス情報を追加'
      }
      await mountComponent({ stubs, slots })
      expect(wrapper.find('[data-positive-label]').text()).toBe('追加')
      expect(wrapper.find('[data-title]').text()).toBe('サービス情報を追加')
      expect(wrapper).toMatchSnapshot()
      unmountComponent()
    })
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: DeepPartial<LtcsProvisionReportEntry>, f: () => void) {
      const entry = { ...form, ...values }
      const value = { index, entry }
      await setProps(wrapper, { value })
      jest.runOnlyPendingTimers()
      await nextTick()
      await nextTick()
      await observer.validate()
      f()
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
      await validate({}, () => {
        expect(observer).toBePassed()
      })
    })

    it('should fail when slot start is empty', async () => {
      const values = {
        slot: { start: '', end: '10:00' }
      }
      await validate(values, () => {
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-slot-start] .v-messages').text()).toBe('入力してください。')
      })
    })

    it('should fail when slot end is empty', async () => {
      const values = {
        slot: { start: '09:00', end: '' }
      }
      await validate(values, () => {
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-slot-end] .v-messages').text()).toBe('入力してください。')
      })
    })

    it('should fail when timeframe is empty', async () => {
      const values = {
        timeframe: undefined
      }
      await validate(values, () => {
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-timeframe] .v-messages').text()).toBe('入力してください。')
      })
    })

    it('should fail when category is empty', async () => {
      const values = {
        category: undefined
      }
      await validate(values, () => {
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-category] .v-messages').text()).toBe('入力してください。')
      })
    })

    it.each([
      ['physicalCare', LtcsProjectServiceCategory.physicalCare, LtcsProjectAmountCategory.physicalCare],
      ['housework', LtcsProjectServiceCategory.housework, LtcsProjectAmountCategory.housework]
    ])('should fail when amounts is empty if category is %s', async (_, category, amountCategory) => {
      const values = {
        category,
        amounts: [{ category: amountCategory, amount: undefined }]
      }
      await validate(values, () => {
        expect(observer).not.toBePassed()
        expect(wrapper.findAll('[data-amount] .v-messages').at(0).text()).toBe('入力してください。')
      })
    })

    it.each([
      [
        'amount(physicalCare) is empty',
        [
          { category: LtcsProjectAmountCategory.physicalCare, amount: undefined },
          { category: LtcsProjectAmountCategory.housework, amount: 120 }
        ],
        ['入力してください。', '']
      ],
      [
        'amount(housework) is empty',
        [
          { category: LtcsProjectAmountCategory.physicalCare, amount: 120 },
          { category: LtcsProjectAmountCategory.housework, amount: undefined }
        ],
        ['', '入力してください。']
      ],
      [
        'both of amount(physicalCare) and amount(housework) are empty',
        [
          { category: LtcsProjectAmountCategory.physicalCare, amount: undefined },
          { category: LtcsProjectAmountCategory.housework, amount: undefined }
        ],
        ['入力してください。', '入力してください。']
      ]
    ])('should fail when %s if category is physicalCareAndHousework', async (_, amounts, messages) => {
      const values = {
        category: LtcsProjectServiceCategory.physicalCareAndHousework,
        amounts
      }
      await validate(values, () => {
        const elements = wrapper.findAll('[data-amount] .v-messages')
        expect(observer).not.toBePassed()
        expect(elements.at(0).text()).toBe(messages[0])
        expect(elements.at(1).text()).toBe(messages[1])
      })
    })

    it('should fail when ownExpenseProgramId is empty if category is ownExpense', async () => {
      const values = {
        category: LtcsProjectServiceCategory.ownExpense,
        ownExpenseProgramId: undefined
      }
      await validate(values, () => {
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-own-expense-program-id] .v-messages').text()).toBe('入力してください。')
      })
    })

    it.each([
      ['physicalCare', LtcsProjectServiceCategory.physicalCare],
      ['housework', LtcsProjectServiceCategory.housework],
      ['physicalCareAndHousework', LtcsProjectServiceCategory.physicalCareAndHousework]
    ])('should not fail if ownExpenseProgramId is empty when category is %s', async (_, category) => {
      const amounts = (cat => {
        switch (cat) {
          case LtcsProjectServiceCategory.physicalCare:
            return [
              { category: LtcsProjectAmountCategory.physicalCare, amount: 30 }
            ]
          case LtcsProjectServiceCategory.housework:
            return [
              { category: LtcsProjectAmountCategory.housework, amount: 30 }
            ]
          case LtcsProjectServiceCategory.physicalCareAndHousework:
            return [
              { category: LtcsProjectAmountCategory.physicalCare, amount: 30 },
              { category: LtcsProjectAmountCategory.housework, amount: 30 }
            ]
          default:
            return []
        }
      })(category)
      const values = {
        category,
        amounts,
        ownExpenseProgramId: undefined
      }
      await validate(values, () => {
        expect(observer).toBePassed()
      })
    })

    it('should fail when amount is less than 1', async () => {
      const min = 1
      const message = `${min}以上、1440以下の半角数字で入力してください。`
      const values = {
        category: LtcsProjectServiceCategory.physicalCareAndHousework,
        amounts: [
          { category: LtcsProjectAmountCategory.physicalCare, amount: min - 1 },
          { category: LtcsProjectAmountCategory.housework, amount: min - 1 }
        ]
      }
      await validate(values, () => {
        const elements = wrapper.findAll('[data-amount] .v-messages')
        expect(observer).not.toBePassed()
        expect(elements.at(0).text()).toBe(message)
        expect(elements.at(1).text()).toBe(message)
      })
    })

    it('should fail if amount is greater than 1440', async () => {
      const max = 1440
      const message = `1以上、${max}以下の半角数字で入力してください。`
      const values = {
        category: LtcsProjectServiceCategory.physicalCareAndHousework,
        amounts: [
          { category: LtcsProjectAmountCategory.physicalCare, amount: max + 1 },
          { category: LtcsProjectAmountCategory.housework, amount: max + 1 }
        ]
      }
      await validate(values, () => {
        const elements = wrapper.findAll('[data-amount] .v-messages')
        expect(observer).not.toBePassed()
        expect(elements.at(0).text()).toBe(message)
        expect(elements.at(1).text()).toBe(message)
      })
    })

    it('should fail when headcount is empty', async () => {
      const values = {
        serviceCode: undefined, // 自動補完されるのを防ぐためサービスコードも指定しない
        headcount: undefined
      }
      await validate(values, () => {
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-headcount] .v-messages').text()).toBe('入力してください。')
      })
    })

    it('should fail when serviceCode is empty', async () => {
      const values = {
        serviceCode: undefined
      }
      await validate(values, () => {
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-service-code] .v-messages').text()).toBe('入力してください。')
      })
    })

    it('should fail if note is over 255 characters', async () => {
      const max = 255
      const values = {
        note: 'a'.repeat(max + 1)
      }
      await validate(values, () => {
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-note] .v-messages').text()).toBe(`${max}文字以内で入力してください。`)
      })
    })
  })

  describe('event', () => {
    beforeAll(() => {
      mountComponent({ stubs })
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should emit click:cancel when cancel button clicked', async () => {
      await click(() => wrapper.find('[data-cancel]'))
      const actual = wrapper.emitted('click:cancel')

      expect(actual).toHaveLength(1)
    })

    it('should emit click:save when ok button clicked', async () => {
      await jest.runAllTimers()
      const observer = getValidationObserver(wrapper)
      // TODO: serviceCode に値が設定できないため、validate を Spy で通過させる
      jest.spyOn(observer, 'validate').mockResolvedValue(true)

      await submit(() => wrapper.find('[data-form]'))

      const actual = wrapper.emitted('click:save')
      expect(actual).toHaveLength(1)
      expect(actual![0][0]).toEqual({ index, entry: deleteUndefinedProperties(form) })
    })
  })
})
