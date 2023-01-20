/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Stubs, Wrapper } from '@vue/test-utils'
import { OfficeQualification } from '@zinger/enums/lib/office-qualification'
import { OfficeStatus } from '@zinger/enums/lib/office-status'
import { Permission } from '@zinger/enums/lib/permission'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { Purpose } from '@zinger/enums/lib/purpose'
import { assign, camelToKebab } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZOfficeForm from '~/components/domain/office/z-office-form.vue'
import { dwsAreaGradesStateKey, dwsAreaGradesStoreKey } from '~/composables/stores/use-dws-area-grades-store'
import { ltcsAreaGradesStateKey, ltcsAreaGradesStoreKey } from '~/composables/stores/use-ltcs-area-grades-store'
import { useOfficeGroups } from '~/composables/use-office-groups'
import { Office } from '~/models/office'
import { OfficesApi } from '~/services/api/offices-api'
import { $datetime } from '~/services/datetime-service'
import { ValidationObserverInstance } from '~/support/validation/types'
import {
  createDwsAreaGradeStub,
  createDwsAreaGradeStubs,
  DWS_AREA_GRADE_ID_MIN
} from '~~/stubs/create-dws-area-grade-stub'
import { createDwsAreaGradesStoreStub } from '~~/stubs/create-dws-area-grades-store-stub'
import {
  createLtcsAreaGradeStub,
  createLtcsAreaGradeStubs,
  LTCS_AREA_GRADE_ID_MIN
} from '~~/stubs/create-ltcs-area-grade-stub'
import { createLtcsAreaGradesStoreStub } from '~~/stubs/create-ltcs-area-grades-store-stub'
import { OFFICE_GROUP_IDS } from '~~/stubs/create-office-group-stub'
import { createUseOfficeGroupsStub } from '~~/stubs/create-use-office-groups-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { submit } from '~~/test/helpers/trigger'

jest.mock('~/composables/use-office-groups')

describe('z-office-form.vue', () => {
  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const propsData = {
    buttonText: '登録',
    errors: {},
    permission: Permission.updateInternalOffices,
    progress: false,
    value: {}
  }
  const dwsAreaGradesStore = createDwsAreaGradesStoreStub({
    dwsAreaGrades: createDwsAreaGradeStubs()
  })
  const ltcsAreaGradesStore = createLtcsAreaGradesStoreStub({
    ltcsAreaGrades: createLtcsAreaGradeStubs()
  })
  const mocks = {
    $form
  }

  let wrapper: Wrapper<Vue & any>

  type MountComponentArguments = MountOptions<Vue> & {
    isShallow?: true
  }

  function mountComponent ({ isShallow, ...options }: MountComponentArguments = {}) {
    const stubs: Stubs | undefined = isShallow
      ? ['z-form-card-item-set', 'z-form-card-item', 'v-row', 'v-col', 'z-flex']
      : undefined

    wrapper = mount(ZOfficeForm, {
      ...options,
      ...provides(
        [dwsAreaGradesStoreKey, dwsAreaGradesStore],
        [dwsAreaGradesStateKey, dwsAreaGradesStore.state],
        [ltcsAreaGradesStoreKey, ltcsAreaGradesStore],
        [ltcsAreaGradesStateKey, ltcsAreaGradesStore.state]
      ),
      mocks,
      stubs,
      propsData
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOfficeGroups).mockReturnValue(createUseOfficeGroupsStub())
  })

  afterAll(() => {
    mocked(useOfficeGroups).mockReset()
  })

  describe('initial display', () => {
    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should be rendered correctly', () => {
      expect(wrapper).toMatchSnapshot()
    })

    describe('internal', () => {
      const purpose = Purpose.internal

      it.each([
        ['corporationName'],
        ['phoneticCorporationName']
      ])('should not be rendered %s', async name => {
        await wrapper.setProps({ value: { purpose } })
        expect(wrapper.find(`[data-${camelToKebab(name)}]`)).not.toExist()
      })

      it.each([
        ['code'],
        ['openedOn'],
        ['designationExpiredOn'],
        ['dwsAreaGradeId']
      ])('should be rendered dwsGenericService.%s when qualifications contain dwsHomeHelpService', async name => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.dwsHomeHelpService] } })
        expect(wrapper.find(`[data-dws-generic-service-${camelToKebab(name)}]`)).toExist()
      })

      it.each([
        ['code'],
        ['openedOn'],
        ['designationExpiredOn'],
        ['dwsAreaGradeId']
      ])('should be rendered dwsGenericService.%s when qualifications contain dwsVisitingCareForPwsd', async name => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.dwsVisitingCareForPwsd] } })
        expect(wrapper.find(`[data-dws-generic-service-${camelToKebab(name)}]`)).toExist()
      })

      it('should be rendered dwsGenericService.code when qualifications contain dwsOthers', async () => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.dwsOthers] } })
        expect(wrapper.find('[data-dws-generic-service-code]')).toExist()
      })

      it.each([
        ['openedOn'],
        ['designationExpiredOn'],
        ['dwsAreaGradeId']
      ])('should not be rendered dwsGenericService.%s when qualifications contain dwsOthers', async name => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.dwsOthers] } })
        expect(wrapper.find(`[data-dws-generic-service-${camelToKebab(name)}]`)).not.toExist()
      })

      it.each([
        ['code'],
        ['openedOn'],
        ['designationExpiredOn']
      ])('should be rendered dwsCommAccompanyService.%s when qualifications contain dwsCommAccompany', async name => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.dwsCommAccompany] } })
        expect(wrapper.find(`[data-dws-comm-accompany-service-${camelToKebab(name)}]`)).toExist()
      })

      it.each([
        ['code'],
        ['openedOn'],
        ['designationExpiredOn'],
        ['ltcsAreaGradeId']
      ])('should be rendered ltcsHomeVisitLongTermCareService.%s when qualifications contain ltcsHomeVisitLongTermCare', async name => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.ltcsHomeVisitLongTermCare] } })
        expect(wrapper.find(`[data-ltcs-home-visit-long-term-care-service-${camelToKebab(name)}]`)).toExist()
      })

      it.each([
        ['code'],
        ['openedOn'],
        ['designationExpiredOn'],
        ['ltcsAreaGradeId']
      ])('should be rendered ltcsCareManagementService.%s when qualifications contain ltcsCareManagement', async name => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.ltcsCareManagement] } })
        expect(wrapper.find(`[data-ltcs-care-management-service-${camelToKebab(name)}]`)).toExist()
      })

      it.each([
        ['code'],
        ['openedOn'],
        ['designationExpiredOn']
      ])('should be rendered ltcsCompHomeVisitingService.%s when qualifications contain ltcsCompHomeVisiting', async name => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.ltcsCompHomeVisiting] } })
        expect(wrapper.find(`[data-ltcs-comp-home-visiting-service-${camelToKebab(name)}]`)).toExist()
      })

      it.each([
        ['code'],
        ['openedOn'],
        ['designationExpiredOn']
      ])('should be rendered ltcsPreventionService.%s when qualifications contain ltcsPrevention', async name => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.ltcsPrevention] } })
        expect(wrapper.find(`[data-ltcs-prevention-service-${camelToKebab(name)}]`)).toExist()
      })
    })

    describe('external', () => {
      const purpose = Purpose.external

      it.each([
        ['corporationName'],
        ['phoneticCorporationName']
      ])('should be rendered %s when purpose is external', async name => {
        await wrapper.setProps({ value: { purpose } })
        expect(wrapper.find(`[data-${camelToKebab(name)}]`)).toExist()
      })

      it.each([
        ['dwsGenericService', 'dwsHomeHelpService', OfficeQualification.dwsHomeHelpService],
        ['dwsGenericService', 'dwsVisitingCareForPwsd', OfficeQualification.dwsVisitingCareForPwsd],
        ['dwsGenericService', 'dwsOthers', OfficeQualification.dwsOthers],
        ['dwsCommAccompanyService', 'dwsCommAccompany', OfficeQualification.dwsCommAccompany],
        ['ltcsHomeVisitLongTermCareService', 'ltcsHomeVisitLongTermCare', OfficeQualification.ltcsHomeVisitLongTermCare],
        ['ltcsCareManagementService', 'ltcsCareManagement', OfficeQualification.ltcsCareManagement],
        ['ltcsCompHomeVisitingService', 'ltcsCompHomeVisiting', OfficeQualification.ltcsCompHomeVisiting],
        ['ltcsPreventionService', 'ltcsPrevention', OfficeQualification.ltcsPrevention]
      ])('should be rendered %s.code when qualifications contain %s', async (name, _, qualification) => {
        await wrapper.setProps({ value: { purpose, qualifications: [qualification] } })
        expect(wrapper.find(`[data-${camelToKebab(name)}-code]`)).toExist()
      })

      it.each([
        ['openedOn'],
        ['designationExpiredOn'],
        ['dwsAreaGradeId']
      ])('should not be rendered dwsGenericService.%s when qualifications contain dwsHomeHelpService', async name => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.dwsHomeHelpService] } })
        expect(wrapper.find(`[data-dws-generic-service-${camelToKebab(name)}]`)).not.toExist()
      })

      it.each([
        ['openedOn'],
        ['designationExpiredOn'],
        ['dwsAreaGradeId']
      ])('should not be rendered dwsGenericService.%s when qualifications contain dwsVisitingCareForPwsd', async name => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.dwsVisitingCareForPwsd] } })
        expect(wrapper.find(`[data-dws-generic-service-${camelToKebab(name)}]`)).not.toExist()
      })

      it.each([
        ['openedOn'],
        ['designationExpiredOn'],
        ['dwsAreaGradeId']
      ])('should not be rendered dwsGenericService.%s when qualifications contain dwsOthers', async name => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.dwsOthers] } })
        expect(wrapper.find(`[data-dws-generic-service-${camelToKebab(name)}]`)).not.toExist()
      })

      it.each([
        ['openedOn'],
        ['designationExpiredOn']
      ])('should not be rendered dwsCommAccompanyService.%s when qualifications contain dwsCommAccompany', async name => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.dwsCommAccompany] } })
        expect(wrapper.find(`[data-dws-comm-accompany-service-${camelToKebab(name)}]`)).not.toExist()
      })

      it.each([
        ['openedOn'],
        ['designationExpiredOn'],
        ['ltcsAreaGradeId']
      ])('should not be rendered ltcsHomeVisitLongTermCareService.%s when qualifications contain ltcsHomeVisitLongTermCare', async name => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.ltcsHomeVisitLongTermCare] } })
        expect(wrapper.find(`[data-ltcs-home-visit-long-term-care-service-${camelToKebab(name)}]`)).not.toExist()
      })

      it.each([
        ['openedOn'],
        ['designationExpiredOn'],
        ['ltcsAreaGradeId']
      ])('should not be rendered ltcsCareManagementService.%s when qualifications contain ltcsCareManagement', async name => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.ltcsCareManagement] } })
        expect(wrapper.find(`[data-ltcs-care-management-service-${camelToKebab(name)}]`)).not.toExist()
      })

      it.each([
        ['openedOn'],
        ['designationExpiredOn']
      ])('should not be rendered ltcsCompHomeVisitingService.%s when qualifications contain ltcsCompHomeVisiting', async name => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.ltcsCompHomeVisiting] } })
        expect(wrapper.find(`[data-ltcs-comp-home-visiting-service-${camelToKebab(name)}]`)).not.toExist()
      })

      it.each([
        ['openedOn'],
        ['designationExpiredOn']
      ])('should not be rendered ltcsPreventionService.%s when qualifications contain ltcsPrevention', async name => {
        await wrapper.setProps({ value: { purpose, qualifications: [OfficeQualification.ltcsPrevention] } })
        expect(wrapper.find(`[data-ltcs-prevention-service-${camelToKebab(name)}]`)).not.toExist()
      })
    })
  })

  describe('validation', () => {
    const form: OfficesApi.Form = {
      name: '土屋訪問介護事業所 中野坂上',
      abbr: '中野坂上',
      phoneticName: 'ツチヤホウモンカイゴジギョウショナカノサカウエ',
      corporationName: '土屋訪問介護事業所 中野坂上の法人名',
      phoneticCorporationName: 'ツチヤホウモンカイゴジギョウショナカノサカウエノホウジンメイ',
      officeGroupId: OFFICE_GROUP_IDS[0],
      postcode: '123-4567',
      prefecture: Prefecture.tokyo,
      city: '中野区',
      street: '中央1-2-3',
      apartment: '',
      tel: '03-1111-1111',
      fax: '',
      email: 'smith@example.jp',
      purpose: Purpose.internal,
      qualifications: [],
      dwsGenericService: {},
      dwsCommAccompanyService: {},
      ltcsHomeVisitLongTermCareService: {},
      ltcsCareManagementService: {},
      ltcsCompHomeVisitingService: {},
      ltcsPreventionService: {},
      status: OfficeStatus.inOperation
    }
    const dwsGenericService: Office['dwsGenericService'] = {
      code: '1234567890',
      openedOn: $datetime.now,
      designationExpiredOn: $datetime.now,
      dwsAreaGradeId: createDwsAreaGradeStub(DWS_AREA_GRADE_ID_MIN)?.id
    }
    const dwsCommAccompanyService: Office['dwsCommAccompanyService'] = {
      code: '4567890123',
      openedOn: $datetime.now,
      designationExpiredOn: $datetime.now
    }
    const ltcsHomeVisitLongTermCareService: Office['ltcsHomeVisitLongTermCareService'] = {
      code: '1234567890',
      openedOn: $datetime.now,
      designationExpiredOn: $datetime.now,
      ltcsAreaGradeId: createLtcsAreaGradeStub(LTCS_AREA_GRADE_ID_MIN)?.id
    }
    const ltcsCareManagementService: Office['ltcsCareManagementService'] = {
      code: '4567890123',
      openedOn: $datetime.now,
      designationExpiredOn: $datetime.now,
      ltcsAreaGradeId: createLtcsAreaGradeStub(LTCS_AREA_GRADE_ID_MIN)?.id
    }
    const ltcsCompHomeVisitingService: Office['ltcsCompHomeVisitingService'] = {
      code: '8901234567',
      openedOn: $datetime.now,
      designationExpiredOn: $datetime.now
    }
    const ltcsPreventionService: Office['ltcsPreventionService'] = {
      code: '8901234567',
      openedOn: $datetime.now,
      designationExpiredOn: $datetime.now
    }
    let observer: ValidationObserverInstance

    async function validate (values: OfficesApi.Form = {}) {
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
      await validate()
      expect(observer).toBePassed()
    })

    describe('value is empty', () => {
      it.each([
        ['purpose'],
        ['name'],
        ['abbr'],
        ['phoneticName'],
        ['postcode'],
        ['prefecture'],
        ['city'],
        ['street'],
        ['tel']
      ])('should fail when %s is empty', async name => {
        await validate({
          [name]: ''
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find(`[data-${camelToKebab(name)}] .v-messages`).text()).toBe('入力してください。')
      })

      it.each<[string, Purpose | undefined]>([
        ['corporationName', Purpose.external],
        ['phoneticCorporationName', Purpose.external],
        ['apartment', undefined],
        ['fax', undefined],
        ['email', undefined]
      ])('should not fail even if %s is empty', async (name, purpose = Purpose.internal) => {
        await validate({
          purpose,
          [name]: ''
        })
        expect(observer).toBePassed()
      })

      describe('internal', () => {
        const purpose = Purpose.internal

        it.each([
          ['code'],
          ['openedOn'],
          ['designationExpiredOn'],
          ['dwsAreaGradeId']
        ])('should fail if dwsGenericService.%s is empty when qualifications contain dwsHomeHelpService', async name => {
          await validate({
            purpose,
            qualifications: [OfficeQualification.dwsHomeHelpService],
            dwsGenericService: {
              ...dwsGenericService,
              [name]: ''
            }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find(`[data-dws-generic-service-${camelToKebab(name)}] .v-messages`).text()).toBe('入力してください。')
        })

        it.each([
          ['code'],
          ['openedOn'],
          ['designationExpiredOn'],
          ['dwsAreaGradeId']
        ])('should fail if dwsGenericService.%s is empty when qualifications contain dwsVisitingCareForPwsd', async name => {
          await validate({
            purpose,
            qualifications: [OfficeQualification.dwsVisitingCareForPwsd],
            dwsGenericService: {
              ...dwsGenericService,
              [name]: ''
            }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find(`[data-dws-generic-service-${camelToKebab(name)}] .v-messages`).text()).toBe('入力してください。')
        })

        it('should fail if dwsGenericService.code is empty when qualifications contain dwsOthers', async () => {
          await validate({
            purpose,
            qualifications: [OfficeQualification.dwsOthers],
            dwsGenericService: {
              ...dwsGenericService,
              code: ''
            }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-dws-generic-service-code] .v-messages').text()).toBe('入力してください。')
        })

        it.each([
          ['openedOn'],
          ['designationExpiredOn'],
          ['dwsAreaGradeId']
        ])('should pass if dwsGenericService.%s is empty when qualifications contain dwsOthers', async name => {
          await validate({
            purpose,
            qualifications: [OfficeQualification.dwsOthers],
            dwsGenericService: {
              ...dwsGenericService,
              [name]: ''
            }
          })
          expect(observer).toBePassed()
        })

        it.each([
          ['code'],
          ['openedOn'],
          ['designationExpiredOn']
        ])('should fail if dwsCommAccompanyService.%s is empty when qualifications contain dwsCommAccompany', async name => {
          await validate({
            purpose,
            qualifications: [OfficeQualification.dwsCommAccompany],
            dwsCommAccompanyService: {
              ...dwsCommAccompanyService,
              [name]: ''
            }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find(`[data-dws-comm-accompany-service-${camelToKebab(name)}] .v-messages`).text()).toBe('入力してください。')
        })

        it.each([
          ['code'],
          ['openedOn'],
          ['designationExpiredOn'],
          ['ltcsAreaGradeId']
        ])('should fail if ltcsHomeVisitLongTermCareService.%s is empty when qualifications contain ltcsHomeVisitLongTermCare', async name => {
          await validate({
            purpose,
            qualifications: [OfficeQualification.ltcsHomeVisitLongTermCare],
            ltcsHomeVisitLongTermCareService: {
              ...ltcsHomeVisitLongTermCareService,
              [name]: ''
            }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find(`[data-ltcs-home-visit-long-term-care-service-${camelToKebab(name)}] .v-messages`).text()).toBe('入力してください。')
        })

        it.each([
          ['code'],
          ['openedOn'],
          ['designationExpiredOn'],
          ['ltcsAreaGradeId']
        ])('should fail if ltcsCareManagementService.%s is empty when qualifications contain ltcsCareManagement', async name => {
          await validate({
            purpose,
            qualifications: [OfficeQualification.ltcsCareManagement],
            ltcsCareManagementService: {
              ...ltcsCareManagementService,
              [name]: ''
            }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find(`[data-ltcs-care-management-service-${camelToKebab(name)}] .v-messages`).text()).toBe('入力してください。')
        })

        it.each([
          ['code'],
          ['openedOn'],
          ['designationExpiredOn']
        ])('should fail if ltcsCompHomeVisitingService.%s is empty when qualifications contain ltcsCompHomeVisiting', async name => {
          await validate({
            purpose,
            qualifications: [OfficeQualification.ltcsCompHomeVisiting],
            ltcsCompHomeVisitingService: {
              ...ltcsCompHomeVisitingService,
              [name]: ''
            }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find(`[data-ltcs-comp-home-visiting-service-${camelToKebab(name)}] .v-messages`).text()).toBe('入力してください。')
        })

        it.each([
          ['code'],
          ['openedOn'],
          ['designationExpiredOn']
        ])('should fail if ltcsPreventionService.%s is empty when qualifications contain ltcsPrevention', async name => {
          await validate({
            purpose,
            qualifications: [OfficeQualification.ltcsPrevention],
            ltcsPreventionService: {
              ...ltcsPreventionService,
              [name]: ''
            }
          })
          expect(observer).not.toBePassed()
          expect(wrapper.find(`[data-ltcs-prevention-service-${camelToKebab(name)}] .v-messages`).text()).toBe('入力してください。')
        })
      })
    })

    describe('external', () => {
      const purpose = Purpose.external

      it('should fail if dwsGenericService.code is empty when qualifications contain dwsHomeHelpService', async () => {
        await validate({
          purpose,
          qualifications: [OfficeQualification.dwsHomeHelpService],
          dwsGenericService: {
            ...dwsGenericService,
            code: ''
          }
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-dws-generic-service-code] .v-messages').text()).toBe('入力してください。')
      })

      it('should fail if dwsGenericService.code is empty when qualifications contain dwsVisitingCareForPwsd', async () => {
        await validate({
          purpose,
          qualifications: [OfficeQualification.dwsVisitingCareForPwsd],
          dwsGenericService: {
            ...dwsGenericService,
            code: ''
          }
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-dws-generic-service-code] .v-messages').text()).toBe('入力してください。')
      })

      it('should fail if dwsGenericService.code is empty when qualifications contain dwsOthers', async () => {
        await validate({
          purpose,
          qualifications: [OfficeQualification.dwsOthers],
          dwsGenericService: {
            ...dwsGenericService,
            code: ''
          }
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-dws-generic-service-code] .v-messages').text()).toBe('入力してください。')
      })

      it('should fail if dwsCommAccompanyService.code is empty when qualifications contain dwsCommAccompany', async () => {
        await validate({
          purpose,
          qualifications: [OfficeQualification.dwsCommAccompany],
          dwsCommAccompanyService: {
            ...dwsCommAccompanyService,
            code: ''
          }
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-dws-comm-accompany-service-code] .v-messages').text()).toBe('入力してください。')
      })

      it('should fail if ltcsHomeVisitLongTermCareService.code is empty when qualifications contain ltcsHomeVisitLongTermCare', async () => {
        await validate({
          purpose,
          qualifications: [OfficeQualification.ltcsHomeVisitLongTermCare],
          ltcsHomeVisitLongTermCareService: {
            ...ltcsHomeVisitLongTermCareService,
            code: ''
          }
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-ltcs-home-visit-long-term-care-service-code] .v-messages').text()).toBe('入力してください。')
      })

      it('should fail if ltcsCareManagementService.code is empty when qualifications contain ltcsCareManagement', async () => {
        await validate({
          purpose,
          qualifications: [OfficeQualification.ltcsCareManagement],
          ltcsCareManagementService: {
            ...ltcsCareManagementService,
            code: ''
          }
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-ltcs-care-management-service-code] .v-messages').text()).toBe('入力してください。')
      })

      it('should fail if ltcsCompHomeVisitingService.code is empty when qualifications contain ltcsCompHomeVisiting', async () => {
        await validate({
          purpose,
          qualifications: [OfficeQualification.ltcsCompHomeVisiting],
          ltcsCompHomeVisitingService: {
            ...ltcsCompHomeVisitingService,
            code: ''
          }
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-ltcs-comp-home-visiting-service-code] .v-messages').text()).toBe('入力してください。')
      })

      it('should fail if ltcsPreventionService.code is empty when qualifications contain ltcsPrevention', async () => {
        await validate({
          purpose,
          qualifications: [OfficeQualification.ltcsPrevention],
          ltcsPreventionService: {
            ...ltcsPreventionService,
            code: ''
          }
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-ltcs-prevention-service-code] .v-messages').text()).toBe('入力してください。')
      })
    })

    describe('value is too long', () => {
      it.each<[string, number, string | undefined, Purpose | undefined]>([
        ['name', 200, undefined, undefined],
        ['abbr', 200, undefined, undefined],
        ['phoneticName', 200, 'ア', undefined],
        ['corporationName', 200, 'x', Purpose.external],
        ['phoneticCorporationName', 200, 'ア', Purpose.external],
        ['city', 200, undefined, undefined],
        ['street', 200, undefined, undefined],
        ['apartment', 200, undefined, undefined]
      ])('should fail when %s is longer than %i characters', async (
        name,
        length,
        char = 'x',
        purpose = Purpose.internal
      ) => {
        const maxLengthString = char.repeat(length)
        await validate({
          purpose,
          [name]: maxLengthString
        })
        expect(observer).toBePassed()

        await validate({
          purpose,
          [name]: `${maxLengthString}${char}`
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find(`[data-${camelToKebab(name)}] .v-messages`).text()).toBe(`${length}文字以内で入力してください。`)
      })

      it('should fail when email is longer than 255 characters', async () => {
        const maxLengthEmail = `${'a'.repeat(243)}@example.com`
        await validate({
          email: maxLengthEmail
        })
        expect(observer).toBePassed()

        await validate({
          email: `a${maxLengthEmail}`
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-email] .v-messages').text()).toBe('255文字以内で入力してください。')
      })

      it.each([
        ['dwsGenericService', 'dwsHomeHelpService', OfficeQualification.dwsHomeHelpService],
        ['dwsGenericService', 'dwsVisitingCareForPwsd', OfficeQualification.dwsVisitingCareForPwsd],
        ['dwsCommAccompanyService', 'dwsCommAccompany', OfficeQualification.dwsCommAccompany],
        ['ltcsHomeVisitLongTermCareService', 'ltcsHomeVisitLongTermCare', OfficeQualification.ltcsHomeVisitLongTermCare],
        ['ltcsCareManagementService', 'ltcsCareManagement', OfficeQualification.ltcsCareManagement],
        ['ltcsCompHomeVisitingService', 'ltcsCompHomeVisiting', OfficeQualification.ltcsCompHomeVisiting]
      ])('should fail if %s.code is other than 10 digits when qualifications contain %s', async (name, _, qualification) => {
        const code = '2'.repeat(10)
        await validate({
          purpose: Purpose.external,
          qualifications: [qualification],
          [name]: { code }
        })
        expect(observer).toBePassed()

        await validate({
          purpose: Purpose.external,
          qualifications: [qualification],
          [name]: {
            code: `x${code}`
          }
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find(`[data-${camelToKebab(name)}-code] .v-messages`).text()).toBe('10文字で入力してください。')
      })
    })

    it.each<[string, Purpose | undefined]>([
      ['phoneticName', undefined],
      ['phoneticCorporationName', Purpose.external]
    ])('should fail when %s contains non-katakana character(s)', async (name, purpose = Purpose.internal) => {
      await validate({
        purpose,
        [name]: 'トウキョウスカイツリー'
      })
      expect(observer).toBePassed()

      await validate({
        purpose,
        [name]: '東京スカイツリー'
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find(`[data-${camelToKebab(name)}] .v-messages`).text()).toBe('カタカナで入力してください。')
    })

    it('should fail when officeGroupId is empty and purpose is internal', async () => {
      await validate({
        purpose: Purpose.internal,
        officeGroupId: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-office-group-id] .v-messages').text()).toBe('入力してください。')
    })

    it('should not fail even if officeGroupId is empty when purpose is not internal', async () => {
      await validate({
        purpose: Purpose.external,
        officeGroupId: undefined
      })
      expect(observer).toBePassed()
    })

    it('should fail when postcode is not a valid postcode', async () => {
      await validate({
        postcode: '123'
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-postcode] .v-messages').text()).toBe('郵便番号は7桁で入力してください。')
    })

    it('should fail when serviceCode is not a valid alphabet or numeric', async () => {
      await validate({
        qualifications: [OfficeQualification.dwsVisitingCareForPwsd],
        dwsGenericService: {
          ...dwsGenericService,
          code: '123456789あ'
        }
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-dws-generic-service-code] .v-messages').text()).toBe('半角英数字で入力してください。')
    })

    it('should fail when tel is not a valid phone number', async () => {
      await validate({
        tel: '0-123-4567-89'
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-tel] .v-messages').text()).toBe('有効な電話番号を入力してください。')
    })

    it('should fail when fax is not a valid fax number', async () => {
      await validate({
        fax: '0-123-4567-89'
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-fax] .v-messages').text()).toBe('有効なFAX番号を入力してください。')
    })

    it('should fail when email is not a valid email address', async () => {
      await validate({
        email: 'this is not an email address'
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-email] .v-messages').text()).toBe('有効なメールアドレスを入力してください。')
    })

    it('should fail when status is undefined', async () => {
      await validate({
        status: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-status] .v-messages').text()).toBe('入力してください。')
    })
  })

  describe('autoKana', () => {
    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should update phoneticName when it blur', () => {
      const input = wrapper.find('[data-phonetic-name-input]')
      input.setValue('ゆーすたいるらぼらとりー')
      input.trigger('blur')

      expect(wrapper.vm.form.phoneticName).toBe('ユースタイルラボラトリー')
    })
  })

  describe('formatPhoneNumber', () => {
    beforeAll(() => {
      mountComponent()
    })

    it('should format tel', () => {
      const input = wrapper.find('[data-tel-input]')
      input.setValue('0359376825')
      input.trigger('blur')

      expect(wrapper.vm.form.tel).toBe('03-5937-6825')
    })

    it('should format fax', () => {
      const input = wrapper.find('[data-fax-input]')
      input.setValue('0359376828')
      input.trigger('blur')

      expect(wrapper.vm.form.fax).toBe('03-5937-6828')
    })
  })

  describe('input form control', () => {
    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should be remove officeGroupId settings when purpose become other than internal', async () => {
      const values = {
        officeGroupId: OFFICE_GROUP_IDS[0],
        purpose: Purpose.internal
      }
      await setData(wrapper, { form: values })
      const form = wrapper.vm.$data.form
      expect(form.officeGroupId).not.toBeUndefined()
      await assign(form, { purpose: Purpose.external })
      expect(form.officeGroupId).toBeUndefined()
    })

    it('should be remove email settings when purpose become other than internal', async () => {
      const values = {
        email: 'john@example.com',
        purpose: Purpose.internal
      }
      await setData(wrapper, { form: values })
      const form = wrapper.vm.$data.form
      expect(form.email).not.toBeUndefined()
      await assign(form, { purpose: Purpose.external })
      expect(form.email).toBeUndefined()
    })

    it.each([
      ['dwsHomeHelpService', OfficeQualification.dwsHomeHelpService],
      ['dwsVisitingCareForPwsd', OfficeQualification.dwsVisitingCareForPwsd]
    ])('should be remove dwsGenericService settings when qualifications become not include %s', async (_, qualification) => {
      const dwsGenericService = {
        dwsAreaGradeId: 10,
        code: 'string'
      }
      const values = {
        qualifications: [qualification],
        dwsGenericService
      }
      await setData(wrapper, { form: values })
      const form = wrapper.vm.$data.form
      expect(form.dwsGenericService).toStrictEqual(dwsGenericService)
      await assign(form, { qualifications: [] })
      expect(form.dwsGenericService).toStrictEqual({})
    })

    it.each([
      ['dwsHomeHelpService', OfficeQualification.dwsHomeHelpService],
      ['dwsVisitingCareForPwsd', OfficeQualification.dwsVisitingCareForPwsd]
    ])('should be remain only dwsGenericService.code when remove %s from qualifications including %s and dwsOthers', async (_, qualification) => {
      const dwsGenericService = {
        code: 'string',
        openedOn: $datetime.now,
        designationExpiredOn: $datetime.now,
        dwsAreaGradeId: 10
      }
      const expectedValue = { code: 'string' }
      const values = {
        qualifications: [qualification, OfficeQualification.dwsOthers],
        dwsGenericService
      }
      await setData(wrapper, { form: values })
      const form = wrapper.vm.$data.form
      expect(form.dwsGenericService).toStrictEqual(dwsGenericService)
      await assign(form, { qualifications: [OfficeQualification.dwsOthers] })
      expect(form.dwsGenericService).toStrictEqual(expectedValue)
    })

    it('should be remove dwsCommAccompanyService settings when qualifications become not include dwsCommAccompany', async () => {
      const dwsCommAccompanyService = {
        code: 'string'
      }
      const values = {
        qualifications: [OfficeQualification.dwsCommAccompany],
        dwsCommAccompanyService
      }
      await setData(wrapper, { form: values })
      const form = wrapper.vm.$data.form
      expect(form.dwsCommAccompanyService).toStrictEqual(dwsCommAccompanyService)
      await assign(form, { qualifications: [] })
      expect(form.dwsCommAccompanyService).toStrictEqual({})
    })

    it('should be remove ltcsHomeVisitLongTermCareService settings when qualifications become not include ltcsHomeVisitLongTermCare', async () => {
      const ltcsHomeVisitLongTermCareService = {
        ltcsAreaGradeId: 10,
        code: 'string'
      }
      const values = {
        qualifications: [OfficeQualification.ltcsHomeVisitLongTermCare],
        ltcsHomeVisitLongTermCareService
      }
      await setData(wrapper, { form: values })
      const form = wrapper.vm.$data.form
      expect(form.ltcsHomeVisitLongTermCareService).toStrictEqual(ltcsHomeVisitLongTermCareService)
      await assign(form, { qualifications: [] })
      expect(form.ltcsHomeVisitLongTermCareService).toStrictEqual({})
    })

    it('should be remove ltcsCareManagementService settings when qualifications become not include ltcsCareManagement', async () => {
      const ltcsCareManagementService = {
        ltcsAreaGradeId: 20,
        code: 'string'
      }
      const values = {
        qualifications: [OfficeQualification.ltcsCareManagement],
        ltcsCareManagementService
      }
      await setData(wrapper, { form: values })
      const form = wrapper.vm.$data.form
      expect(form.ltcsCareManagementService).toStrictEqual(ltcsCareManagementService)
      await assign(form, { qualifications: [] })
      expect(form.ltcsCareManagementService).toStrictEqual({})
    })

    it('should be remove ltcsPreventionService settings when qualifications become not include ltcsPrevention', async () => {
      const ltcsPreventionService = {
        code: 'string'
      }
      const values = {
        qualifications: [OfficeQualification.ltcsPrevention],
        ltcsPreventionService
      }
      await setData(wrapper, { form: values })
      const form = wrapper.vm.$data.form
      expect(form.ltcsPreventionService).toStrictEqual(ltcsPreventionService)
      await assign(form, { qualifications: [] })
      expect(form.ltcsPreventionService).toStrictEqual({})
    })
  })

  describe('submit', () => {
    const baseValue = {
      name: '土屋訪問介護事業所 中野坂上',
      abbr: '中野坂上',
      phoneticName: 'ツチヤホウモンカイゴジギョウショナカノサカウエ',
      corporationName: '土屋訪問介護事業所 中野坂上の法人名',
      phoneticCorporationName: 'ツチヤホウモンカイゴジギョウショナカノサカウエノホウジンメイ',
      officeGroupId: OFFICE_GROUP_IDS[0],
      postcode: '123-4567',
      prefecture: Prefecture.tokyo,
      city: '中野区',
      street: '中央1-2-3',
      apartment: '',
      tel: '03-1111-1111',
      fax: '',
      email: 'smith@example.jp',
      purpose: Purpose.internal,
      qualifications: [],
      dwsGenericService: {},
      dwsCommAccompanyService: {},
      ltcsHomeVisitLongTermCareService: {},
      ltcsCareManagementService: {},
      ltcsCompHomeVisitingService: {},
      ltcsPreventionService: {},
      status: OfficeStatus.inOperation
    }

    beforeEach(() => {
      mountComponent({ isShallow: true })
    })

    afterEach(() => {
      unmountComponent()
    })

    describe('corporationName', () => {
      it('should be sent blank if the purpose is internal.', async () => {
        const expected = {
          corporationName: '',
          phoneticCorporationName: ''
        }
        await wrapper.setProps({
          value: {
            ...baseValue,
            corporationName: '土屋訪問介護事業所 中野坂上の法人名',
            phoneticCorporationName: 'ツチヤホウモンカイゴジギョウショナカノサカウエノホウジンメイ'
          }
        })
        await submit(() => wrapper.find('[data-form]'))
        expect(wrapper.emitted('submit')![0][0]).toEqual(expect.objectContaining(expected))
      })

      it('should be sent input value if the purpose is external.', async () => {
        const expected = {
          corporationName: '土屋訪問介護事業所 中野坂上の法人名',
          phoneticCorporationName: 'ツチヤホウモンカイゴジギョウショナカノサカウエノホウジンメイ'
        }
        await wrapper.setProps({
          value: {
            ...baseValue,
            ...expected,
            purpose: Purpose.external
          }
        })
        await submit(() => wrapper.find('[data-form]'))
        expect(wrapper.emitted('submit')![0][0]).toEqual(expect.objectContaining(expected))
      })

      it('should be sent blank if no value is entered when purpose is external.', async () => {
        const expected = {
          corporationName: '',
          phoneticCorporationName: ''
        }
        await wrapper.setProps({
          value: {
            ...baseValue,
            corporationName: undefined,
            phoneticCorporationName: undefined,
            purpose: Purpose.external
          }
        })
        await submit(() => wrapper.find('[data-form]'))
        expect(wrapper.emitted('submit')![0][0]).toEqual(expect.objectContaining(expected))
      })
    })
  })
})
