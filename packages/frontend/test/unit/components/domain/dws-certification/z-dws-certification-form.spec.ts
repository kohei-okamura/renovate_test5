/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, ref } from '@nuxtjs/composition-api'
import { MountOptions, Wrapper } from '@vue/test-utils'
import { DwsCertificationAgreementType } from '@zinger/enums/lib/dws-certification-agreement-type'
import { DwsCertificationServiceType } from '@zinger/enums/lib/dws-certification-service-type'
import { DwsCertificationStatus } from '@zinger/enums/lib/dws-certification-status'
import { DwsLevel } from '@zinger/enums/lib/dws-level'
import { DwsType } from '@zinger/enums/lib/dws-type'
import { OfficeQualification } from '@zinger/enums/lib/office-qualification'
import { Permission } from '@zinger/enums/lib/permission'
import { Purpose } from '@zinger/enums/lib/purpose'
import { camelToKebab } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZDwsCertificationForm from '~/components/domain/dws-certification/z-dws-certification-form.vue'
import { useOffices } from '~/composables/use-offices'
import { DwsCertificationsApi } from '~/services/api/dws-certifications-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createDwsCertificationStub } from '~~/stubs/create-dws-certification-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click, submit } from '~~/test/helpers/trigger'

jest.mock('~/composables/use-offices')

describe('z-dws-certification-form.vue', () => {
  type Form = DeepPartial<DwsCertificationsApi.Form>

  const { mount, shallowMount } = setupComponentTest()
  const $form = createMockedFormService()
  const createBaseChild = () => ({
    name: {
      familyName: '倉田',
      givenName: '綾',
      phoneticFamilyName: 'クラタ',
      phoneticGivenName: 'アヤ'
    },
    birthday: '1988-08-23'
  })
  const form: Form = {
    child: createBaseChild(),
    effectivatedOn: '1995-01-20',
    status: DwsCertificationStatus.applied,
    dwsNumber: '0123456789',
    dwsTypes: [DwsType.physical],
    issuedOn: '1995-01-20',
    cityName: '東伯郡琴浦町',
    cityCode: '340331',
    dwsLevel: DwsLevel.level2,
    isSubjectOfComprehensiveSupport: true,
    activatedOn: '1995-01-20',
    deactivatedOn: '1995-01-20',
    grants: [
      {
        dwsCertificationServiceType: DwsCertificationServiceType.physicalCare,
        grantedAmount: 'amount',
        activatedOn: '1995-01-20',
        deactivatedOn: '1995-01-20'
      }
    ],
    copayLimit: 6894,
    copayActivatedOn: '1995-01-20',
    copayDeactivatedOn: '1995-01-20',
    copayCoordination: {
      copayCoordinationType: 3,
      officeId: 2
    },
    agreements: [
      {
        indexNumber: 2,
        officeId: 3,
        dwsCertificationAgreementType: DwsCertificationAgreementType.accompany,
        paymentAmount: 44520,
        agreedOn: '1995-01-20',
        expiredOn: '1995-01-20'
      }
    ]
  }
  const formArrays = {
    grants: [
      {
        dwsCertificationServiceType: DwsCertificationServiceType.physicalCare,
        grantedAmount: 'amount',
        activatedOn: '1995-01-20',
        deactivatedOn: '1995-01-20'
      }
    ],
    agreements: [
      {
        indexNumber: 2,
        officeId: 3,
        dwsCertificationAgreementType: DwsCertificationAgreementType.accompany,
        paymentAmount: 44520,
        agreedOn: '1995-01-20',
        expiredOn: '1995-01-20'
      }
    ]
  }
  const stub = createDwsCertificationStub()
  const propsData = {
    buttonText: '登録',
    errors: {},
    permission: Permission.createDwsCertifications,
    progress: false,
    user: createUserStub(stub.userId),
    value: { ...form }
  }
  const mocks = {
    $form
  }

  let wrapper: Wrapper<Vue & any>

  function mountComponent ({ options, isShallow }: { options?: MountOptions<Vue>, isShallow?: true } = {}) {
    const fn = isShallow ? shallowMount : mount
    wrapper = fn(ZDwsCertificationForm, {
      ...options,
      mocks,
      propsData
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
  })

  afterAll(() => {
    mocked(useOffices).mockRestore()
  })

  afterEach(() => {
    jest.clearAllMocks()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should call useOffices with correct qualifications', async () => {
    const permission = ref(propsData.permission)
    await mountComponent({ isShallow: true })
    expect(useOffices).toHaveBeenCalledTimes(2)
    expect(useOffices).toHaveBeenNthCalledWith(
      1,
      {
        purpose: computed(() => Purpose.external),
        qualifications: [
          OfficeQualification.dwsHomeHelpService,
          OfficeQualification.dwsVisitingCareForPwsd,
          OfficeQualification.dwsCommAccompany,
          OfficeQualification.dwsOthers
        ]
      }
    )
    expect(useOffices).toHaveBeenNthCalledWith(2, { permission, internal: true })
    unmountComponent()
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: Form = { grants: [], agreements: [] }) {
      await setData(wrapper, {
        form: { ...form, ...values }
      })
      await observer.validate()
      jest.runOnlyPendingTimers()
    }

    beforeAll(() => {
      mountComponent({
        options: {
          stubs: { 'v-input': true, 'v-messages': false }
        }
      })
      observer = getValidationObserver(wrapper)
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should pass when input correctly', async () => {
      await validate(form)
      expect(observer).toBePassed()
    })

    describe('value is empty', () => {
      it.each([
        ['familyName'],
        ['givenName'],
        ['phoneticFamilyName'],
        ['phoneticGivenName']
      ])('should fail when child %s is empty', async name => {
        const child = createBaseChild()
        await validate({
          child: {
            ...child,
            name: {
              ...child.name,
              [name]: ''
            }
          },
          grants: [],
          agreements: []
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find(`[data-${camelToKebab(name)}] .v-messages`).text()).toBe('入力してください。')
      })

      it('should fail when child birthday is empty', async () => {
        await validate({
          child: {
            ...createBaseChild(),
            birthday: ''
          },
          grants: [],
          agreements: []
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-birthday] .v-messages').text()).toBe('入力してください。')
      })

      it('should success when child is all empty', async () => {
        await validate({
          child: {
            name: {
              familyName: '',
              givenName: '',
              phoneticFamilyName: '',
              phoneticGivenName: ''
            },
            birthday: ''
          },
          ...formArrays
        })
        expect(observer).toBePassed()
      })

      it.each([
        ['effectivatedOn'],
        ['status', undefined],
        ['dwsNumber'],
        ['dwsTypes', []],
        ['issuedOn'],
        ['cityName'],
        ['cityCode'],
        ['dwsLevel', undefined],
        ['activatedOn'],
        ['deactivatedOn'],
        ['copayLimit', undefined],
        ['copayActivatedOn'],
        ['copayDeactivatedOn']
      ])('should fail when %s is empty', async (name, value: unknown = '') => {
        await validate({
          [name]: value,
          grants: [],
          agreements: []
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find(`[data-${camelToKebab(name)}] .v-messages`).text()).toBe('入力してください。')
      })

      it.each([
        ['dwsCertificationServiceType', undefined],
        ['grantedAmount', undefined],
        ['activatedOn'],
        ['deactivatedOn']
      ])('should fail when grant\'s %s is empty', async (name, value: unknown = '') => {
        await validate({
          grants: [
            { [name]: value }
          ],
          agreements: []
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find(`[data-grant-${camelToKebab(name)}] .v-messages`).text()).toBe('入力してください。')
      })

      it.each([
        ['indexNumber'],
        ['officeId'],
        ['dwsCertificationAgreementType'],
        ['agreedOn'],
        ['paymentAmount']
      ])('should fail when agreement\'s %s is empty', async name => {
        await validate({
          grants: [],
          agreements: [
            { [name]: undefined }
          ]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find(`[data-agreement-${camelToKebab(name)}] .v-messages`).text()).toBe('入力してください。')
      })

      it('should fail when grants is empty', async () => {
        await validate({
          ...formArrays,
          grants: []
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-grants] .v-alert__content').text()).toBe('介護給付費の支給内容を1つ以上追加してください。')
      })

      it('should fail when agreements is empty', async () => {
        await validate({
          ...formArrays,
          agreements: []
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-agreements] .v-alert__content').text()).toBe('訪問系サービス事業者記入欄を1つ以上追加してください。')
      })
    })

    describe.each([
      ['dwsNumber', 10],
      ['cityCode', 6]
    ])('%s\'s digits', (name, digits) => {
      it(`should fail if digits is less than ${digits}`, async () => {
        await validate({
          ...formArrays,
          [name]: '2'.repeat(digits - 1)
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find(`[data-${camelToKebab(name)}] .v-messages`).text()).toBe(`${digits}桁の半角数字で入力してください。`)
      })

      it(`should fail if digits is greater than ${digits}`, async () => {
        await validate({
          ...formArrays,
          [name]: '2'.repeat(digits + 1)
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find(`[data-${camelToKebab(name)}] .v-messages`).text()).toBe(`${digits}桁の半角数字で入力してください。`)
      })

      it(`should not fail if digits is ${digits}`, async () => {
        await validate({
          ...formArrays,
          [name]: '2'.repeat(digits)
        })
        expect(observer).toBePassed()
      })
    })

    describe('indexNumber\'s digits', () => {
      it('should fail if indexNumber is less than 1', async () => {
        await validate({
          ...formArrays,
          agreements: [{
            ...formArrays.agreements[0],
            indexNumber: 0
          }]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-agreement-index-number] .v-messages').text()).toBe('1以上、99以下の半角数字で入力してください。')
      })

      it('should fail if indexNumber is greater than 99', async () => {
        await validate({
          ...formArrays,
          agreements: [{
            ...formArrays.agreements[0],
            indexNumber: 100
          }]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-agreement-index-number] .v-messages').text()).toBe('1以上、99以下の半角数字で入力してください。')
      })

      it('should not fail if indexNumber is between 1 and 99', async () => {
        await validate({
          ...formArrays,
          agreements: [{
            ...formArrays.agreements[0],
            indexNumber: 99
          }]
        })
        expect(observer).toBePassed()
      })
    })

    describe('value has invalid character', () => {
      it.each([
        ['copayLimit']
      ])('should fail when non-numeric given to %s', async name => {
        await validate({
          [name]: 'abc' as any,
          grants: [],
          agreements: []
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find(`[data-${camelToKebab(name)}] .v-messages`).text()).toBe('半角数字のみで入力してください。')
      })

      it.each([
        ['indexNumber'],
        ['officeId'],
        ['dwsCertificationAgreementType']
      ])('should fail when non-numeric given to agreement\'s %s', async name => {
        await validate({
          grants: [],
          agreements: [
            { [name]: 'abc' as any }
          ]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find(`[data-agreement-${camelToKebab(name)}] .v-messages`).text()).toBe('半角数字のみで入力してください。')
      })
    })

    it('should fail when cityName is longer than 200', async () => {
      const maxLength = '三'.repeat(200)
      await validate({
        ...formArrays,
        cityName: maxLength
      })
      expect(observer).toBePassed()

      await validate({
        cityName: `三${maxLength}`,
        grants: [],
        agreements: []

      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-city-name] .v-messages').text()).toBe('200文字以内で入力してください。')
    })

    describe('dwsCertificationAgreementType combine of dwsLevel', () => {
      it('should pass when effectivatedOn over expiredOn', async () => {
        await validate({
          dwsLevel: DwsLevel.level2,
          isSubjectOfComprehensiveSupport: true,
          effectivatedOn: '2020-01-02',
          agreements: [{
            ...formArrays.agreements[0],
            expiredOn: '2020-01-01',
            dwsCertificationAgreementType: DwsCertificationAgreementType.visitingCareForPwsd1
          }]
        })
        expect(observer).toBePassed()
      })
      test.each([
        ['サービス内容が visitingCareForPwsd1 かつ 障害支援区分 が level6 ではない',
          {
            dwsLevel: DwsLevel.level2,
            isSubjectOfComprehensiveSupport: true,
            dwsCertificationAgreementType: DwsCertificationAgreementType.visitingCareForPwsd1
          }
        ],
        ['サービス内容が visitingCareForPwsd1 かつ isSubjectOfComprehensiveSupport が false',
          {
            dwsLevel: DwsLevel.level6,
            isSubjectOfComprehensiveSupport: false,
            dwsCertificationAgreementType: DwsCertificationAgreementType.visitingCareForPwsd1
          }
        ],
        ['サービス内容が visitingCareForPwsd2 かつ 障害支援区分 が level6 ではない',
          {
            dwsLevel: DwsLevel.level4,
            isSubjectOfComprehensiveSupport: false,
            dwsCertificationAgreementType: DwsCertificationAgreementType.visitingCareForPwsd2
          }
        ],
        ['サービス内容が visitingCareForPwsd2 かつ isSubjectOfComprehensiveSupport が true',
          {
            dwsLevel: DwsLevel.level6,
            isSubjectOfComprehensiveSupport: true,
            dwsCertificationAgreementType: DwsCertificationAgreementType.visitingCareForPwsd2
          }
        ],
        ['サービス内容が visitingCareForPwsd3 かつ 障害支援区分が level3 〜 level6 ではない',
          {
            dwsLevel: DwsLevel.level2,
            isSubjectOfComprehensiveSupport: false,
            dwsCertificationAgreementType: DwsCertificationAgreementType.visitingCareForPwsd3
          }
        ],
        ['サービス内容が outingSupportForPwsd かつ 障害支援区分が level3 〜 level6 ではない',
          {
            dwsLevel: DwsLevel.level2,
            isSubjectOfComprehensiveSupport: true,
            dwsCertificationAgreementType: DwsCertificationAgreementType.outingSupportForPwsd
          }
        ]
      ])('should fail when %s', async (_, x) => {
        await validate({
          dwsLevel: x.dwsLevel,
          isSubjectOfComprehensiveSupport: x.isSubjectOfComprehensiveSupport,
          agreements: [{
            ...formArrays.agreements[0],
            dwsCertificationAgreementType: x.dwsCertificationAgreementType
          }]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-agreement-dws-certification-agreement-type] .v-messages').text())
          .toBe('障害支援区分とサービス内容の組み合わせが正しくありません。間違いがないかご確認ください。')
      })
    })
    describe('介護給付費の支給決定内容の重度訪問介護の重複チェック', () => {
      test('介護給付費の支給決定内容が一つしかない場合に通過する', async () => {
        await validate({
          dwsLevel: DwsLevel.level6,
          grants: [{
            dwsCertificationServiceType: DwsCertificationServiceType.visitingCareForPwsd2,
            grantedAmount: 'amount',
            activatedOn: '1995-01-01',
            deactivatedOn: '1995-01-20'
          }]
        })
        expect(observer).toBePassed()
      })
      test('介護給付費の支給決定内容が2つ存在するが重度訪問介護以外の場合に通過する', async () => {
        await validate({
          dwsLevel: DwsLevel.level6,
          grants: [
            {
              dwsCertificationServiceType: DwsCertificationServiceType.visitingCareForPwsd2,
              grantedAmount: 'amount',
              activatedOn: '1995-01-01',
              deactivatedOn: '1995-01-20'
            },
            {
              dwsCertificationServiceType: DwsCertificationServiceType.physicalCare,
              grantedAmount: 'amount',
              activatedOn: '1995-01-01',
              deactivatedOn: '1995-01-20'
            }
          ]
        })
        expect(observer).toBePassed()
      })
      test('介護給付費の支給決定内容が2つ存在するが重度訪問介護で期間が重複していない場合に通過する', async () => {
        await validate({
          dwsLevel: DwsLevel.level6,
          grants: [
            {
              dwsCertificationServiceType: DwsCertificationServiceType.visitingCareForPwsd2,
              grantedAmount: 'amount',
              activatedOn: '1995-01-01',
              deactivatedOn: '1995-01-20'
            },
            {
              dwsCertificationServiceType: DwsCertificationServiceType.visitingCareForPwsd2,
              grantedAmount: 'amount',
              activatedOn: '1995-01-21',
              deactivatedOn: '1995-01-25'
            }
          ]
        })
        expect(observer).toBePassed()
      })
      test('サービス種別が複数で重度訪問介護（その他）と重度訪問介護（障害支援区分6該当者）が重複している場合に失敗する', async () => {
        await validate({
          dwsLevel: DwsLevel.level6,
          grants: [
            {
              dwsCertificationServiceType: DwsCertificationServiceType.visitingCareForPwsd2,
              grantedAmount: 'amount',
              activatedOn: '1995-01-01',
              deactivatedOn: '1995-01-20'
            },
            {
              dwsCertificationServiceType: DwsCertificationServiceType.visitingCareForPwsd3,
              grantedAmount: 'amount',
              activatedOn: '1995-01-01',
              deactivatedOn: '1995-01-20'
            }
          ]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-grant-dws-certification-service-type] .v-messages').text())
          .toBe('支給決定期間が重複する重度訪問介護の支給決定内容が他に存在します。')
      })
    })
    describe('介護給付費の支給決定内容「サービス種別」の矛盾チェック', () => {
      describe('障害支援区分が「区分6」かつ重度障害者等包括支援対象の場合', () => {
        test.each([
          ['居宅介護：居宅における身体介護中心', DwsCertificationServiceType.physicalCare],
          ['居宅介護：家事援助中心', DwsCertificationServiceType.housework],
          ['居宅介護：通院等介助（身体介護を伴う場合）中心', DwsCertificationServiceType.accompanyWithPhysicalCare],
          ['居宅介護：通院等介助（身体介護を伴わない場合）中心', DwsCertificationServiceType.accompany],
          ['重度訪問介護（重度障害者等包括支援対象者）', DwsCertificationServiceType.visitingCareForPwsd1],
          ['重度訪問介護（障害支援区分6該当者）', DwsCertificationServiceType.visitingCareForPwsd2],
          ['重度訪問介護（その他）', DwsCertificationServiceType.visitingCareForPwsd3]
        ])('サービス種別が「%s」の場合に通過する', async () => {
          await validate({
            effectivatedOn: '2022-04-01',
            dwsLevel: DwsLevel.level6,
            isSubjectOfComprehensiveSupport: true,
            grants: [{
              dwsCertificationServiceType: DwsCertificationServiceType.visitingCareForPwsd2,
              grantedAmount: 'amount',
              activatedOn: '2022-09-01',
              deactivatedOn: '2023-12-31'
            }]
          })
          expect(observer).toBePassed()
        })
      })
      describe('障害支援区分が「区分6」の場合', () => {
        test.each([
          ['居宅介護：居宅における身体介護中心', DwsCertificationServiceType.physicalCare],
          ['居宅介護：家事援助中心', DwsCertificationServiceType.housework],
          ['居宅介護：通院等介助（身体介護を伴う場合）中心', DwsCertificationServiceType.accompanyWithPhysicalCare],
          ['居宅介護：通院等介助（身体介護を伴わない場合）中心', DwsCertificationServiceType.accompany],
          ['重度訪問介護（障害支援区分6該当者）', DwsCertificationServiceType.visitingCareForPwsd2],
          ['重度訪問介護（その他）', DwsCertificationServiceType.visitingCareForPwsd3]
        ])('サービス種別が「%s」の場合に通過する', async (_, type) => {
          await validate({
            effectivatedOn: '2022-04-01',
            dwsLevel: DwsLevel.level6,
            isSubjectOfComprehensiveSupport: false,
            grants: [{
              dwsCertificationServiceType: type,
              grantedAmount: 'amount',
              activatedOn: '2022-09-01',
              deactivatedOn: '2023-12-31'
            }]
          })
          expect(observer).toBePassed()
        })
        test.each([
          ['重度訪問介護（重度障害者等包括支援対象者）', DwsCertificationServiceType.visitingCareForPwsd1]
        ])('サービス種別が「%s」の場合にエラーとなる', async (_, type) => {
          await validate({
            effectivatedOn: '2022-04-01',
            dwsLevel: DwsLevel.level6,
            isSubjectOfComprehensiveSupport: false,
            grants: [{
              dwsCertificationServiceType: type,
              grantedAmount: 'amount',
              activatedOn: '2022-09-01',
              deactivatedOn: '2023-12-31'
            }]
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-grant-dws-certification-service-type] .v-messages').text()).toBe(
            '障害支援区分と矛盾するサービス種別です。間違いがないかご確認ください。'
          )
        })
      })
      describe.each([
        ['障害支援区分5', DwsLevel.level5],
        ['障害支援区分4', DwsLevel.level4],
        ['障害支援区分3', DwsLevel.level3]
      ])('障害支援区分が「%s」の場合', (_, level) => {
        test.each([
          ['居宅介護：居宅における身体介護中心', DwsCertificationServiceType.physicalCare],
          ['居宅介護：家事援助中心', DwsCertificationServiceType.housework],
          ['居宅介護：通院等介助（身体介護を伴う場合）中心', DwsCertificationServiceType.accompanyWithPhysicalCare],
          ['居宅介護：通院等介助（身体介護を伴わない場合）中心', DwsCertificationServiceType.accompany],
          ['重度訪問介護（その他）', DwsCertificationServiceType.visitingCareForPwsd3]
        ])('サービス種別が「%s」の場合に通過する', async (_, type) => {
          await validate({
            effectivatedOn: '2022-04-01',
            dwsLevel: level,
            isSubjectOfComprehensiveSupport: false,
            grants: [{
              dwsCertificationServiceType: type,
              grantedAmount: 'amount',
              activatedOn: '2022-09-01',
              deactivatedOn: '2023-12-31'
            }]
          })
          expect(observer).toBePassed()
        })
        test.each([
          ['重度訪問介護（重度障害者等包括支援対象者）', DwsCertificationServiceType.visitingCareForPwsd1],
          ['重度訪問介護（障害支援区分6該当者）', DwsCertificationServiceType.visitingCareForPwsd2]
        ])('サービス種別が「%s」の場合にエラーとなる', async (_, type) => {
          await validate({
            effectivatedOn: '2022-04-01',
            dwsLevel: level,
            isSubjectOfComprehensiveSupport: false,
            grants: [{
              dwsCertificationServiceType: type,
              grantedAmount: 'amount',
              activatedOn: '2022-09-01',
              deactivatedOn: '2023-12-31'
            }]
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-grant-dws-certification-service-type] .v-messages').text()).toBe(
            '障害支援区分と矛盾するサービス種別です。間違いがないかご確認ください。'
          )
        })
      })
      describe.each([
        ['障害支援区分2', DwsLevel.level2],
        ['障害支援区分1', DwsLevel.level1],
        ['非該当', DwsLevel.notApplicable]
      ])('障害支援区分が「%s」の場合', (_, level) => {
        test.each([
          ['居宅介護：居宅における身体介護中心', DwsCertificationServiceType.physicalCare],
          ['居宅介護：家事援助中心', DwsCertificationServiceType.housework],
          ['居宅介護：通院等介助（身体介護を伴う場合）中心', DwsCertificationServiceType.accompanyWithPhysicalCare],
          ['居宅介護：通院等介助（身体介護を伴わない場合）中心', DwsCertificationServiceType.accompany]
        ])('サービス種別が「%s」の場合に通過する', async (_, type) => {
          await validate({
            effectivatedOn: '2022-04-01',
            dwsLevel: level,
            isSubjectOfComprehensiveSupport: false,
            grants: [{
              dwsCertificationServiceType: type,
              grantedAmount: 'amount',
              activatedOn: '2022-09-01',
              deactivatedOn: '2023-12-31'
            }]
          })
          expect(observer).toBePassed()
        })
        test.each([
          ['重度訪問介護（重度障害者等包括支援対象者）', DwsCertificationServiceType.visitingCareForPwsd1],
          ['重度訪問介護（障害支援区分6該当者）', DwsCertificationServiceType.visitingCareForPwsd2],
          ['重度訪問介護（その他）', DwsCertificationServiceType.visitingCareForPwsd3]
        ])('サービス種別が「%s」の場合にエラーとなる', async (_, type) => {
          await validate({
            effectivatedOn: '2022-04-01',
            dwsLevel: level,
            isSubjectOfComprehensiveSupport: false,
            grants: [{
              dwsCertificationServiceType: type,
              grantedAmount: 'amount',
              activatedOn: '2022-09-01',
              deactivatedOn: '2023-12-31'
            }]
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-grant-dws-certification-service-type] .v-messages').text()).toBe(
            '障害支援区分と矛盾するサービス種別です。間違いがないかご確認ください。'
          )
        })
      })
    })
  })

  describe('event', () => {
    function mountComponentWithStubs (options: MountOptions<Vue> = {}) {
      wrapper = mount(ZDwsCertificationForm, {
        ...options,
        mocks,
        propsData,
        stubs: { ...options?.stubs, 'z-form-card-item-set': true, 'z-validate-error-messages': true }
      })
    }

    describe('Increase / decrease in input field', () => {
      beforeAll(() => {
        mountComponentWithStubs()
      })

      afterAll(() => {
        unmountComponent()
      })

      it('should add an agreement when click add-agreement', async () => {
        const before = wrapper.vm.form.agreements.length
        await click(() => wrapper.find('[data-add-agreement]'))
        expect(wrapper.vm.form.agreements.length).toBe(before + 1)
      })

      it('should delete an agreement when click delete-agreement', async () => {
        const before = wrapper.vm.form.agreements.length
        await click(() => wrapper.find('[data-delete-agreement]'))
        expect(wrapper.vm.form.agreements.length).toBe(before - 1)
      })

      it('should add a grant when click add-grant', async () => {
        const before = wrapper.vm.form.grants.length
        await click(() => wrapper.find('[data-add-grant]'))
        expect(wrapper.vm.form.grants.length).toBe(before + 1)
      })

      it('should delete a grant when click delete-grant', async () => {
        const before = wrapper.vm.form.grants.length
        await click(() => wrapper.find('[data-delete-grant]'))
        expect(wrapper.vm.form.grants.length).toBe(before - 1)
      })
    })

    it('should emit submit when submit form', async () => {
      const expected = {
        ...form,
        isSubjectOfComprehensiveSupport: false
      }
      await mountComponentWithStubs({
        stubs: {
          'v-row': true,
          'z-form-card': true,
          'z-form-card-item-set': true,
          'z-form-card-item': true,
          'z-subheader': true
        }
      })
      await submit(() => wrapper.find('[data-form]'))
      const emitted = wrapper.emitted('submit')
      expect(emitted).toBeTruthy()
      expect(emitted!.length).toBe(1)
      expect(emitted![0][0]).toMatchObject(expected)
    })
  })
})
