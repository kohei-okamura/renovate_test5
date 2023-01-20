/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { DateRangeType } from '@zinger/enums/lib/date-range-type'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZShiftDownloadForm from '~/components/domain/shift/z-shift-download-form.vue'
import { useOffices } from '~/composables/use-offices'
import { ShiftsApi } from '~/services/api/shifts-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { submit } from '~~/test/helpers/trigger'

jest.mock('~/composables/use-offices')

describe('z-shift-download-form.vue', () => {
  type Form = DeepPartial<ShiftsApi.CreateTemplateForm>

  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const formValues: ShiftsApi.CreateTemplateForm = {
    officeId: OFFICE_ID_MIN,
    isCopy: false,
    range: {
      start: '2021-02-21',
      end: '2021-02-28'
    }
  }
  const propsData = {
    errors: {},
    progress: false,
    value: formValues
  }

  let wrapper: Wrapper<Vue>

  function mountComponent () {
    wrapper = mount(ZShiftDownloadForm, { propsData, mocks })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
  })

  afterAll(() => {
    mocked(useOffices).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (form: Form = {}, data: Record<string, any> = {}): Promise<void> {
      await setData(wrapper, {
        ...data,
        form: { ...formValues, ...form }
      })
      await observer.validate()
      jest.runOnlyPendingTimers()
    }

    beforeAll(() => {
      mountComponent()
      observer = getValidationObserver(wrapper)
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should pass when input correctly', async () => {
      await validate()

      expect(observer).toBePassed()
    })

    it('should fail when officeId is empty', async () => {
      const officeId = undefined

      await validate({ officeId })

      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-office-id] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when range.start is empty if rangeType is "specify"', async () => {
      const range = {
        start: undefined,
        end: '2021-02-24'
      }
      const rangeType = DateRangeType.specify

      await validate({ range }, { rangeType })

      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-range-start] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when range.end is empty if rangeType is "specify"', async () => {
      const range = {
        start: '2021-02-24',
        end: undefined
      }
      const rangeType = DateRangeType.specify

      await validate({ range }, { rangeType })

      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-range-end] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when range.end is less than range.start if rangeType is "specify"', async () => {
      const form = {
        range: {
          start: '2021-02-24',
          end: '2021-02-23'
        }
      }
      const data = {
        rangeType: DateRangeType.specify
      }

      await validate(form, data)

      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-range-end] .v-messages').text()).toBe('開始日以降の日付を入力してください。')
    })

    it(
      'should not fail when rangeType is not "specify" even if both of range.start and range.end are empty',
      async () => {
        const form = {
          range: {
            start: undefined,
            end: undefined
          }
        }
        const data = {
          rangeType: DateRangeType.nextMonth
        }

        await validate(form, data)

        expect(observer).toBePassed()
      }
    )
  })

  describe('submit', () => {
    async function submitWith (form: Form = {}, data: Record<string, any> = {}): Promise<void> {
      await setData(wrapper, {
        ...data,
        form: { ...formValues, ...form }
      })
      await submit(() => wrapper.find('[data-form]'))
      await wrapper.vm.$nextTick()
    }

    beforeEach(() => {
      mountComponent()
    })

    afterEach(() => {
      unmountComponent()
    })

    describe.each([
      ['when rangeType is nextWeek', DateRangeType.nextWeek, { start: undefined, end: undefined }],
      ['when rangeType is nextMonth', DateRangeType.nextMonth, { start: undefined, end: undefined }],
      ['when rangeType is specify', DateRangeType.specify, { start: '2021-02-22', end: '2021-02-28' }]
    ])('dispatch event "submit" %s', (_, rangeType, range) => {
      it.each([
        ['when sourceType is NO_COPY', -1],
        ['when sourceType is lastWeek', DateRangeType.lastWeek],
        ['when sourceType is thisWeek', DateRangeType.thisWeek]
      ])('%s', async (_, sourceType) => {
        await submitWith({ range }, { rangeType, sourceType })
        const actual = wrapper.emitted('submit')

        expect(actual).toHaveLength(1)
        expect(actual![0][0]).toMatchSnapshot()
      })
    })
  })
})
