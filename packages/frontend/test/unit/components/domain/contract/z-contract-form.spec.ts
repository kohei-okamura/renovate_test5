/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { ContractStatus } from '@zinger/enums/lib/contract-status'
import { DwsServiceDivisionCode } from '@zinger/enums/lib/dws-service-division-code'
import { LtcsExpiredReason } from '@zinger/enums/lib/ltcs-expired-reason'
import { Permission } from '@zinger/enums/lib/permission'
import { ServiceSegment } from '@zinger/enums/lib/service-segment'
import { clone } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZContractForm from '~/components/domain/contract/z-contract-form.vue'
import { useOffices } from '~/composables/use-offices'
import { DwsContractsApi } from '~/services/api/dws-contracts-api'
import { LtcsContractsApi } from '~/services/api/ltcs-contracts-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createDwsCertificationStub } from '~~/stubs/create-dws-certification-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('z-contract-form.vue', () => {
  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const stub = createDwsCertificationStub()
  const user = createUserStub(stub.userId)
  const basePropsData = {
    errors: {},
    progress: false,
    user
  }

  let wrapper: Wrapper<Vue>
  let observer: ValidationObserverInstance

  function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(ZContractForm, { ...options, mocks })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  type Form =
    DwsContractsApi.CreateForm |
    DwsContractsApi.UpdateForm |
    LtcsContractsApi.CreateForm |
    LtcsContractsApi.UpdateForm

  function createValidateFunction<T extends Form> (form: T) {
    return async (values: DeepPartial<T> = {}) => {
      await setData(wrapper, {
        form: { ...clone(form), ...values }
      })
      await observer.validate()
      jest.runOnlyPendingTimers()
    }
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
  })

  afterAll(() => {
    mocked(useOffices).mockReset()
  })

  describe.each([
    ['dws', Permission.createDwsContracts, ServiceSegment.disabilitiesWelfare],
    ['ltcs', Permission.createLtcsContracts, ServiceSegment.longTermCare]
  ])('for create %s contract', (_, permission, serviceSegment) => {
    const form: DwsContractsApi.CreateForm & LtcsContractsApi.CreateForm = {
      officeId: 9,
      note: 'だるまさんがころんだ'
    }
    const propsData = {
      ...basePropsData,
      buttonText: '登録',
      permission,
      serviceSegment,
      value: clone(form)
    }

    beforeAll(() => {
      mountComponent({ propsData })
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should be rendered correctly', () => {
      expect(wrapper).toMatchSnapshot()
    })

    describe('validation', () => {
      const validate = createValidateFunction(form)

      beforeAll(() => {
        observer = getValidationObserver(wrapper)
      })

      it('should pass when input correctly', async () => {
        await validate()
        expect(observer).toBePassed()
      })

      it('should fail when the officeId is empty', async () => {
        await validate({
          officeId: undefined
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-office-id] .v-messages').text()).toBe('入力してください。')
      })

      it('should fail when the officeId is not numeric', async () => {
        await validate({
          officeId: 'abc' as any
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-office-id] .v-messages').text()).toBe('半角数字のみで入力してください。')
      })

      it('should pass even if the note is empty', async () => {
        await validate({
          note: ''
        })
        expect(observer).toBePassed()
      })
    })
  })

  describe('for update dws contract', () => {
    const serviceSegment = ServiceSegment.disabilitiesWelfare
    const permission = Permission.updateDwsContracts

    describe('when status is formal', () => {
      const status = ContractStatus.formal
      const form: DwsContractsApi.UpdateForm = {
        officeId: 9,
        status,
        contractedOn: '2008-05-17',
        terminatedOn: undefined,
        dwsPeriods: {
          [DwsServiceDivisionCode.homeHelpService]: Object.freeze({
            start: '2008-06-01',
            end: undefined
          }),
          [DwsServiceDivisionCode.visitingCareForPwsd]: Object.freeze({
            start: '2012-10-01',
            end: undefined
          })
        },
        note: 'だるまさんがころんだ'
      }
      const propsData = {
        ...basePropsData,
        buttonText: '保存',
        contractStatus: status,
        permission,
        serviceSegment,
        value: clone(form)
      }

      beforeAll(() => {
        mountComponent({ propsData })
      })

      afterAll(() => {
        unmountComponent()
      })

      it('should be rendered correctly', () => {
        expect(wrapper).toMatchSnapshot()
      })

      describe('validation', () => {
        const validate = createValidateFunction(form)

        beforeAll(() => {
          observer = getValidationObserver(wrapper)
        })

        it('should pass when input correctly', async () => {
          await validate()
          expect(observer).toBePassed()
        })

        it('should fail when the contractedOn is empty', async () => {
          await validate({
            contractedOn: undefined
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-contracted-on] .v-messages').text()).toBe('入力してください。')
        })

        it.each([
          ...DwsServiceDivisionCode.values.map(x => [x])
        ])('should fail when all of dwsPeriods.*.start are empty', async code => {
          await validate({
            dwsPeriods: {
              [DwsServiceDivisionCode.homeHelpService]: { start: undefined, end: undefined },
              [DwsServiceDivisionCode.visitingCareForPwsd]: { start: undefined, end: undefined }
            }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find(`[data-dws-periods-start="${code}"] .v-messages`).text()).toBe('入力してください。')
        })

        it.each([
          ...DwsServiceDivisionCode.values.map(x => [x])
        ])('should pass even if the dwsPeriods.%s.start is empty when a opposite is not empty', async code => {
          await validate({
            dwsPeriods: {
              [DwsServiceDivisionCode.homeHelpService]: { start: '2008-06-01', end: undefined },
              [DwsServiceDivisionCode.visitingCareForPwsd]: { start: '2012-10-01', end: undefined },
              ...({ [code]: { start: undefined, end: undefined } })
            }
          })
          expect(observer).toBePassed()
        })

        it.each([
          ...DwsServiceDivisionCode.values.map(x => [x])
        ])('should fail if the dwsPeriods.%s.start before the contractedOn', async code => {
          await validate({
            contractedOn: '2008-05-17',
            dwsPeriods: {
              [DwsServiceDivisionCode.homeHelpService]: { start: '2008-06-01', end: undefined },
              [DwsServiceDivisionCode.visitingCareForPwsd]: { start: '2012-10-01', end: undefined },
              ...({ [code]: { start: '2008-05-16', end: undefined } })
            }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find(`[data-dws-periods-start="${code}"] .v-messages`).text()).toBe(
            '契約日以降の日付を入力してください。'
          )
        })

        it.each([
          ...DwsServiceDivisionCode.values.flatMap(x => [
            ['equals', x, '2008-05-17', '2008-05-17'],
            ['is after', x, '2008-05-17', '2008-05-18']
          ])
        ])(
          'should pass even if the dwsPeriods.%s.start equals the contractedOn',
          async (_, code, contractedOn, start) => {
            await validate({
              contractedOn,
              dwsPeriods: {
                [DwsServiceDivisionCode.homeHelpService]: { start: '2008-06-01', end: undefined },
                [DwsServiceDivisionCode.visitingCareForPwsd]: { start: '2012-10-01', end: undefined },
                ...({ [code]: { start, end: undefined } })
              }
            })
            expect(observer).toBePassed()
          }
        )

        it('should pass even if note is empty', async () => {
          await validate({
            note: ''
          })
          expect(observer).toBePassed()
        })

        it('should pass when note is less than 256 characters long', async () => {
          await validate({
            note: 'あ'.repeat(255)
          })
          expect(observer).toBePassed()
        })

        it('should fail when note is more than 255 characters long', async () => {
          await validate({
            note: 'あ'.repeat(256)
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-note] .v-messages').text()).toBe('255文字以内で入力してください。')
        })
      })
    })

    describe('when status is terminated', () => {
      const status = ContractStatus.terminated
      const form: DwsContractsApi.UpdateForm = {
        officeId: 9,
        status,
        contractedOn: '2008-05-17',
        terminatedOn: '2021-04-30',
        dwsPeriods: {
          [DwsServiceDivisionCode.homeHelpService]: Object.freeze({
            start: '2008-06-01',
            end: '2012-09-30'
          }),
          [DwsServiceDivisionCode.visitingCareForPwsd]: Object.freeze({
            start: '2012-10-01',
            end: '2021-04-15'
          })
        },
        note: 'だるまさんがころんだ'
      }
      const propsData = {
        ...basePropsData,
        buttonText: '保存',
        contractStatus: status,
        permission,
        serviceSegment,
        value: clone(form)
      }

      beforeAll(() => {
        mountComponent({ propsData })
      })

      afterAll(() => {
        unmountComponent()
      })

      it('should be rendered correctly', () => {
        expect(wrapper).toMatchSnapshot()
      })

      describe('validation', () => {
        const validate = createValidateFunction(form)
        const dwsPeriods = {
          [DwsServiceDivisionCode.homeHelpService]: { start: '2008-06-01', end: '2012-09-30' },
          [DwsServiceDivisionCode.visitingCareForPwsd]: { start: '2012-10-01', end: '2021-04-15' }
        }

        beforeAll(() => {
          observer = getValidationObserver(wrapper)
        })

        it('should pass when input correctly', async () => {
          await validate()
          expect(observer).toBePassed()
        })

        it('should fail when the terminatedOn is empty', async () => {
          await validate({
            terminatedOn: undefined
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-terminated-on] .v-messages').text()).toBe('入力してください。')
        })

        it.each([
          ['is before', '2008-05-17', '2008-05-16'],
          ['equals', '2008-05-17', '2008-05-17']
        ])('should fail when the terminatedOn %s the contractedOn', async (_, contractedOn, terminatedOn) => {
          await validate({
            contractedOn,
            terminatedOn
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-terminated-on] .v-messages').text()).toBe('契約日以降の日付を入力してください。')
        })

        it('should pass when the terminatedOn is after the contractedOn', async () => {
          await validate({
            contractedOn: '2008-05-17',
            terminatedOn: '2008-05-18'
          })
          expect(observer).toBePassed()
        })

        it.each([
          ...DwsServiceDivisionCode.values.map(x => [x])
        ])('should fail when all of dwsPeriods.*.start are empty', async code => {
          await validate({
            dwsPeriods: {
              [DwsServiceDivisionCode.homeHelpService]: { start: undefined, end: undefined },
              [DwsServiceDivisionCode.visitingCareForPwsd]: { start: undefined, end: undefined }
            }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find(`[data-dws-periods-start="${code}"] .v-messages`).text()).toBe('入力してください。')
        })

        it.each([
          ...DwsServiceDivisionCode.values.map(x => [x])
        ])('should pass even if the dwsPeriods.%s.start is empty when a opposite is not empty', async code => {
          await validate({
            dwsPeriods: {
              ...dwsPeriods,
              ...({ [code]: { start: undefined, end: undefined } })
            }
          })
          expect(observer).toBePassed()
        })

        it.each([
          ...DwsServiceDivisionCode.values.map(x => [x])
        ])('should fail if the dwsPeriods.%s.start before the contractedOn', async code => {
          await validate({
            contractedOn: '2008-05-17',
            dwsPeriods: {
              ...dwsPeriods,
              ...({ [code]: { start: '2008-05-16', end: '2021-04-15' } })
            }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find(`[data-dws-periods-start="${code}"] .v-messages`).text()).toBe(
            '契約日以降の日付を入力してください。'
          )
        })

        it.each([
          ...DwsServiceDivisionCode.values.flatMap(x => [
            ['equals', x, '2008-05-17', '2008-05-17'],
            ['is after', x, '2008-05-17', '2008-05-18']
          ])
        ])(
          'should pass even if the dwsPeriods.%s.start equals the contractedOn',
          async (_, code, contractedOn, start) => {
            await validate({
              contractedOn,
              dwsPeriods: {
                ...dwsPeriods,
                ...({ [code]: { start, end: '2021-04-15' } })
              }
            })
            expect(observer).toBePassed()
          }
        )

        it.each([
          ...DwsServiceDivisionCode.values.map(x => [x, x])
        ])(
          'should fail when the dwsPeriods.%s.end is empty if the dwsPeriods.%s.start is not empty',
          async (_, code) => {
            await validate({
              dwsPeriods: {
                ...dwsPeriods,
                ...({ [code]: { start: '2008-06-01', end: undefined } })
              }
            })
            expect(observer).not.toBePassed()
            expect(wrapper.find(`[data-dws-periods-end="${code}"] .v-messages`).text()).toBe('入力してください。')
          }
        )

        it.each([
          ...DwsServiceDivisionCode.values.map(x => [x, x])
        ])(
          'should pass even if the dwsPeriods.%s.end is empty when the dwsPeriods.%s.start is empty',
          async (_, code) => {
            await validate({
              dwsPeriods: {
                ...dwsPeriods,
                ...({ [code]: { start: undefined, end: undefined } })
              }
            })
            expect(observer).toBePassed()
          }
        )

        it.each([
          ...DwsServiceDivisionCode.values.flatMap(x => [
            [x, 'is before', x, '2012-10-01', '2012-10-01', '2012-09-30'],
            [x, 'equals', x, '2012-10-01', '2012-10-01', '2012-10-01']
          ])
        ])(
          'should fail when the dwsPeriods.%s.end %s the dwsPeriods.%s.start',
          async (code, _, _code, contractedOn, start, end) => {
            await validate({
              contractedOn,
              dwsPeriods: {
                ...dwsPeriods,
                ...({ [code]: { start, end } })
              }
            })
            expect(observer).not.toBePassed()
            expect(wrapper.find(`[data-dws-periods-end="${code}"] .v-messages`).text()).toBe(
              '初回サービス提供日以降の日付を入力してください。'
            )
          }
        )

        it.each([
          ...DwsServiceDivisionCode.values.map(x => [x, x])
        ])(
          'should pass when the dwsPeriods.%s.end is after the dwsPeriods.%s.start',
          async (_, code) => {
            await validate({
              dwsPeriods: {
                ...dwsPeriods,
                ...({ [code]: { start: '2012-10-01', end: '2012-10-02' } })
              }
            })
            expect(observer).toBePassed()
          }
        )

        it('should pass even if note is empty', async () => {
          await validate({
            note: ''
          })
          expect(observer).toBePassed()
        })

        it('should pass when note is less than 256 characters long', async () => {
          await validate({
            note: 'あ'.repeat(255)
          })
          expect(observer).toBePassed()
        })

        it('should fail when note is more than 255 characters long', async () => {
          await validate({
            note: 'あ'.repeat(256)
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-note] .v-messages').text()).toBe('255文字以内で入力してください。')
        })
      })
    })
  })

  describe('for update ltcs contract', () => {
    const serviceSegment = ServiceSegment.longTermCare
    const permission = Permission.updateLtcsContracts

    describe('when status is formal', () => {
      const status = ContractStatus.formal
      const form: LtcsContractsApi.UpdateForm = {
        officeId: 9,
        status,
        contractedOn: '2008-05-17',
        terminatedOn: undefined,
        ltcsPeriod: {
          start: '2008-06-01',
          end: undefined
        },
        expiredReason: LtcsExpiredReason.unspecified,
        note: 'だるまさんがころんだ'
      }
      const propsData = {
        ...basePropsData,
        buttonText: '保存',
        contractStatus: status,
        permission,
        serviceSegment,
        value: clone(form)
      }

      beforeAll(() => {
        mountComponent({ propsData })
      })

      afterAll(() => {
        unmountComponent()
      })

      it('should be rendered correctly', () => {
        expect(wrapper).toMatchSnapshot()
      })

      describe('validation', () => {
        const validate = createValidateFunction(form)

        beforeAll(() => {
          observer = getValidationObserver(wrapper)
        })

        it('should pass when input correctly', async () => {
          await validate()
          expect(observer).toBePassed()
        })

        it('should fail when the contractedOn is empty', async () => {
          await validate({
            contractedOn: undefined
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-contracted-on] .v-messages').text()).toBe('入力してください。')
        })

        it('should fail when the ltcsPeriod.start is empty', async () => {
          await validate({
            ltcsPeriod: {
              start: undefined,
              end: undefined
            }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-ltcs-period-start] .v-messages').text()).toBe('入力してください。')
        })

        it('should fail if the ltcsPeriod.start before the contractedOn', async () => {
          await validate({
            contractedOn: '2008-05-17',
            ltcsPeriod: {
              start: '2008-05-16',
              end: undefined
            }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-ltcs-period-start] .v-messages').text()).toBe('契約日以降の日付を入力してください。')
        })

        it.each([
          ['equals', '2008-05-17', '2008-05-17'],
          ['is after', '2008-05-17', '2008-05-18']
        ])('should pass even if the ltcsPeriod.start equals the contractedOn', async (_, contractedOn, start) => {
          await validate({
            contractedOn,
            ltcsPeriod: { start, end: undefined }
          })
          expect(observer).toBePassed()
        })

        it('should pass even if note is empty', async () => {
          await validate({
            note: ''
          })
          expect(observer).toBePassed()
        })

        it('should pass when note is less than 256 characters long', async () => {
          await validate({
            note: 'あ'.repeat(255)
          })
          expect(observer).toBePassed()
        })

        it('should fail when note is more than 255 characters long', async () => {
          await validate({
            note: 'あ'.repeat(256)
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-note] .v-messages').text()).toBe('255文字以内で入力してください。')
        })
      })
    })

    describe('when status is terminated', () => {
      const status = ContractStatus.terminated
      const form: LtcsContractsApi.UpdateForm = {
        officeId: 9,
        status,
        contractedOn: '2008-05-17',
        terminatedOn: '2021-04-30',
        ltcsPeriod: {
          start: '2012-10-01',
          end: '2021-04-15'
        },
        expiredReason: LtcsExpiredReason.hospitalized,
        note: 'だるまさんがころんだ'
      }
      const propsData = {
        ...basePropsData,
        buttonText: '保存',
        contractStatus: status,
        permission,
        serviceSegment,
        value: clone(form)
      }

      beforeAll(() => {
        mountComponent({ propsData })
      })

      afterAll(() => {
        unmountComponent()
      })

      it('should be rendered correctly', () => {
        expect(wrapper).toMatchSnapshot()
      })

      describe('validation', () => {
        const validate = createValidateFunction(form)

        beforeAll(() => {
          observer = getValidationObserver(wrapper)
        })

        it('should pass when input correctly', async () => {
          await validate()
          expect(observer).toBePassed()
        })

        it('should fail when the terminatedOn is empty', async () => {
          await validate({
            terminatedOn: undefined
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-terminated-on] .v-messages').text()).toBe('入力してください。')
        })

        it.each([
          ['is before', '2008-05-17', '2008-05-16'],
          ['equals', '2008-05-17', '2008-05-17']
        ])('should fail when the terminatedOn %s the contractedOn', async (_, contractedOn, terminatedOn) => {
          await validate({
            contractedOn,
            terminatedOn
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-terminated-on] .v-messages').text()).toBe('契約日以降の日付を入力してください。')
        })

        it('should pass when the terminatedOn is after the contractedOn', async () => {
          await validate({
            contractedOn: '2008-05-17',
            terminatedOn: '2008-05-18'
          })
          expect(observer).toBePassed()
        })

        it('should fail when all of ltcsPeriod.start are empty', async () => {
          await validate({
            ltcsPeriod: { start: undefined, end: undefined }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-ltcs-period-start] .v-messages').text()).toBe('入力してください。')
        })

        it('should fail if the ltcsPeriod.start before the contractedOn', async () => {
          await validate({
            contractedOn: '2008-05-17',
            ltcsPeriod: { start: '2008-05-16', end: '2021-04-15' }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-ltcs-period-start] .v-messages').text()).toBe(
            '契約日以降の日付を入力してください。'
          )
        })

        it.each([
          ['equals', '2008-05-17', '2008-05-17'],
          ['is after', '2008-05-17', '2008-05-18']
        ])(
          'should pass even if the ltcsPeriod.start equals the contractedOn',
          async (_, contractedOn, start) => {
            await validate({
              contractedOn,
              ltcsPeriod: { start, end: '2021-04-15' }
            })
            expect(observer).toBePassed()
          }
        )

        it(
          'should fail when the ltcsPeriod.end is empty if the ltcsPeriod.start is not empty',
          async () => {
            await validate({
              ltcsPeriod: { start: '2008-06-01', end: undefined }
            })
            expect(observer).not.toBePassed()
            expect(wrapper.find('[data-ltcs-period-end] .v-messages').text()).toBe('入力してください。')
          }
        )

        it.each([
          ['is before', '2012-10-01', '2012-10-01', '2012-09-30'],
          ['equals', '2012-10-01', '2012-10-01', '2012-10-01']
        ])('should fail when the ltcsPeriod.end %s the ltcsPeriod.start', async (_, contractedOn, start, end) => {
          await validate({
            contractedOn,
            ltcsPeriod: { start, end }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-ltcs-period-end] .v-messages').text()).toBe(
            '初回サービス提供日以降の日付を入力してください。'
          )
        })

        it('should pass when the dwsPeriods.%s.end is after the ltcsPeriod.start', async () => {
          await validate({
            ltcsPeriod: { start: '2012-10-01', end: '2012-10-02' }
          })
          expect(observer).toBePassed()
        })

        it('should pass even if note is empty', async () => {
          await validate({
            note: ''
          })
          expect(observer).toBePassed()
        })

        it('should pass when note is less than 256 characters long', async () => {
          await validate({
            note: 'あ'.repeat(255)
          })
          expect(observer).toBePassed()
        })

        it('should fail when note is more than 255 characters long', async () => {
          await validate({
            note: 'あ'.repeat(256)
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-note] .v-messages').text()).toBe('255文字以内で入力してください。')
        })
      })
    })
  })
})
