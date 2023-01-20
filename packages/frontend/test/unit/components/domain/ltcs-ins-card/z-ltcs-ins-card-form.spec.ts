/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { LtcsCarePlanAuthorType } from '@zinger/enums/lib/ltcs-care-plan-author-type'
import { LtcsInsCardServiceType } from '@zinger/enums/lib/ltcs-ins-card-service-type'
import { LtcsInsCardStatus } from '@zinger/enums/lib/ltcs-ins-card-status'
import { LtcsLevel } from '@zinger/enums/lib/ltcs-level'
import { OfficeQualification } from '@zinger/enums/lib/office-qualification'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZLtcsInsCardForm from '~/components/domain/ltcs-ins-card/z-ltcs-ins-card-form.vue'
import { useOffices } from '~/composables/use-offices'
import { LtcsInsCardsApi } from '~/services/api/ltcs-ins-cards-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click, submit } from '~~/test/helpers/trigger'

jest.mock('~/composables/use-offices')

describe('z-ltcs-ins-card-form.vue', () => {
  const { mount, shallowMount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const form: LtcsInsCardsApi.Form = {
    effectivatedOn: '2020-01-20',
    status: LtcsInsCardStatus.applied,
    insNumber: '2304316218',
    issuedOn: '2020-01-21',
    insurerNumber: '240094',
    insurerName: '邑楽郡明和町',
    ltcsLevel: LtcsLevel.careLevel3,
    certificatedOn: '2020-01-22',
    activatedOn: '2020-01-23',
    deactivatedOn: '2020-01-24',
    maxBenefitQuotas: [
      {
        ltcsInsCardServiceType: LtcsInsCardServiceType.serviceType2,
        maxBenefitQuota: 280600
      }
    ],
    careManagerName: '倉田 綾',
    carePlanAuthorType: LtcsCarePlanAuthorType.self,
    carePlanAuthorOfficeId: 2,
    copayRate: 3,
    copayActivatedOn: '2020-01-25',
    copayDeactivatedOn: '2020-01-26'
  }
  const propsData = {
    buttonText: '登録',
    errors: {},
    progress: false,
    user: createUserStub(),
    value: { ...form }
  }
  let wrapper: Wrapper<Vue & any>

  type MountComponentArguments = MountOptions<Vue> & {
    isShallow?: true
  }

  function mountComponent ({ isShallow, ...options }: MountComponentArguments = {}) {
    const fn = isShallow ? shallowMount : mount
    wrapper = fn(ZLtcsInsCardForm, {
      ...options,
      mocks: { ...mocks, ...options?.mocks },
      propsData: { ...propsData, ...options?.propsData }
    })
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

  afterEach(() => {
    mocked(useOffices).mockClear()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should call useOffices with correct qualifications', async () => {
    await mountComponent({ isShallow: true })
    expect(useOffices).toHaveBeenCalledTimes(2)
    expect(useOffices).toHaveBeenCalledWith({
      qualifications: [
        OfficeQualification.ltcsCareManagement
      ]
    })
    unmountComponent()
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: LtcsInsCardsApi.Form = { maxBenefitQuotas: [] }) {
      await setData(wrapper, {
        form: { ...form, ...values }
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
      await validate({
        maxBenefitQuotas: [
          {
            ltcsInsCardServiceType: LtcsInsCardServiceType.serviceType2,
            maxBenefitQuota: 280600
          }
        ]
      })
      expect(observer).toBePassed()
    })

    it('should fail when effectivatedOn is empty', async () => {
      await validate({
        effectivatedOn: '',
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-effectivated-on] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when status is empty', async () => {
      await validate({
        status: undefined,
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-ltcs-ins-card-status] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when insNumber is empty', async () => {
      await validate({
        insNumber: '',
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-ins-number] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when insNumber is other than 10 digits', async () => {
      await validate({
        insNumber: '2'.repeat(10),
        maxBenefitQuotas: [
          {
            ltcsInsCardServiceType: LtcsInsCardServiceType.serviceType2,
            maxBenefitQuota: 280600
          }
        ]
      })
      expect(observer).toBePassed()

      await validate({
        insNumber: '2'.repeat(11),
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-ins-number] .v-messages').text()).toBe('10桁の半角数字で入力してください。')
    })

    it('should fail when issuedOn is empty', async () => {
      await validate({
        issuedOn: '',
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-issued-on] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when insurerNumber is empty', async () => {
      await validate({
        insurerNumber: '',
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-insurer-number] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when insurerNumber is other than 6 digits', async () => {
      await validate({
        insurerNumber: '2'.repeat(6),
        maxBenefitQuotas: [
          {
            ltcsInsCardServiceType: LtcsInsCardServiceType.serviceType2,
            maxBenefitQuota: 280600
          }
        ]
      })
      expect(observer).toBePassed()

      await validate({
        insurerNumber: '2'.repeat(9),
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-insurer-number] .v-messages').text()).toBe('6桁の半角数字で入力してください。')
    })

    it('should fail when insurerName is empty', async () => {
      await validate({
        insurerName: '',
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-insurer-name] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when insurerName is longer than 100', async () => {
      await validate({
        insurerName: '三'.repeat(100),
        maxBenefitQuotas: [
          {
            ltcsInsCardServiceType: LtcsInsCardServiceType.serviceType2,
            maxBenefitQuota: 280600
          }
        ]
      })
      expect(observer).toBePassed()

      await validate({
        insurerName: '三'.repeat(101),
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-insurer-name] .v-messages').text()).toBe('100文字以内で入力してください。')
    })

    it('should fail when ltcsLevel is empty', async () => {
      await validate({
        ltcsLevel: undefined,
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-ltcs-level] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when certificatedOn is empty', async () => {
      await validate({
        certificatedOn: '',
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-certificated-on] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when activatedOn is empty', async () => {
      await validate({
        activatedOn: '',
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-activated-on] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when deactivatedOn is empty', async () => {
      await validate({
        deactivatedOn: '',
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-deactivated-on] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when carePlanAuthorType is empty', async () => {
      await validate({
        carePlanAuthorType: undefined,
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-care-plan-author-type] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when carePlanAuthorOfficeId is empty', async () => {
      await validate({
        carePlanAuthorType: LtcsCarePlanAuthorType.careManagerOffice,
        carePlanAuthorOfficeId: undefined,
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-care-plan-author-office-id] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when careManagerName is empty', async () => {
      await validate({
        carePlanAuthorType: LtcsCarePlanAuthorType.careManagerOffice,
        careManagerName: undefined,
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-care-manager-name] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when ltcsInsCardServiceType is empty', async () => {
      await validate({
        maxBenefitQuotas: [
          {
            ltcsInsCardServiceType: undefined
          }
        ]
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-ltcs-ins-card-service-type] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when maxBenefitQuota is empty', async () => {
      await validate({
        maxBenefitQuotas: [
          {
            maxBenefitQuota: undefined
          }
        ]
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-ltcs-ins-card-service-type] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when non-numeric maxBenefitQuota given', async () => {
      await validate({
        maxBenefitQuotas: [
          {
            maxBenefitQuota: 'abc' as any
          }
        ]
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-max-benefit-quota] .v-messages').text()).toBe('半角数字のみで入力してください。')
    })

    it('should fail when copayRate is empty', async () => {
      await validate({
        copayRate: undefined,
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-copay-rate] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when non-numeric copayRate given', async () => {
      await validate({
        copayRate: 'abc' as any,
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-copay-rate] .v-messages').text()).toBe('半角数字のみで入力してください。')
    })

    it('should pass when maxBenefitQuotas is empty', async () => {
      await validate({
        maxBenefitQuotas: []
      })
      expect(observer).toBePassed()
    })

    it('should fail when copayActivatedOn is empty', async () => {
      await validate({
        copayActivatedOn: '',
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-copay-activated-on] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when copayDeactivatedOn is empty', async () => {
      await validate({
        copayDeactivatedOn: '',
        maxBenefitQuotas: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-copay-deactivated-on] .v-messages').text()).toBe('入力してください。')
    })
  })

  describe('methods', () => {
    beforeEach(() => {
      mountComponent({
        stubs: ['z-validate-error-messages', 'z-select', 'z-text-field', 'z-keyword-filter-autocomplete', 'v-radio-group', 'z-date-field']
      })
    })

    afterEach(() => {
      unmountComponent()
    })

    it('should add a quota when click add-quota', async () => {
      const before = wrapper.vm.form.maxBenefitQuotas.length
      await click(() => wrapper.find('[data-add-quota]'))
      expect(wrapper.vm.form.maxBenefitQuotas.length).toBe(before + 1)
    })

    it('should delete an quota when click delete-quota', async () => {
      const before = wrapper.vm.form.maxBenefitQuotas.length
      await click(() => wrapper.find('[data-delete-quota]'))
      expect(wrapper.vm.form.maxBenefitQuotas.length).toBe(before - 1)
    })

    it('should emit submit when submit form', async () => {
      const expected = {
        ...form,
        carePlanAuthorOfficeId: undefined,
        careManagerName: undefined,
        copayRate: (form.copayRate ?? 0) * 10
      }
      await submit(() => wrapper.find('[data-form]'))
      const emitted = wrapper.emitted('submit')
      expect(emitted).toBeTruthy()
      expect(emitted!.length).toBe(1)
      expect(emitted![0][0]).toMatchObject(expected)
    })
  })
})
