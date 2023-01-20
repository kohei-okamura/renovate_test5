/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { ServiceOption } from '@zinger/enums/lib/service-option'
import { Task } from '@zinger/enums/lib/task'
import { mocked } from '@zinger/helpers/testing/mocked'
import flushPromises from 'flush-promises'
import Vue from 'vue'
import ZAttendanceForm from '~/components/domain/shift/z-attendance-form.vue'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { useUsers } from '~/composables/use-users'
import { Time } from '~/models/time'
import { TimeDuration } from '~/models/time-duration'
import { AttendancesApi } from '~/services/api/attendances-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { CONTRACT_ID_MIN } from '~~/stubs/create-contract-stub'
import { OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createStaffStubs } from '~~/stubs/create-staff-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseStaffsStub } from '~~/stubs/create-use-staffs-stub'
import { createUseUsersStub } from '~~/stubs/create-use-users-stub'
import { USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createFaker } from '~~/stubs/fake'
import ramenIpsum from '~~/stubs/fake/ramen-ipsum'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setProps } from '~~/test/helpers/set-props'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { blur, click, submit } from '~~/test/helpers/trigger'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-staffs')
jest.mock('~/composables/use-users')

describe('z-attendance-form.vue', () => {
  const { mount, shallowMount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const staffs = createStaffStubs()
  const faker = createFaker('STAR WARS')
  const ramen = ramenIpsum.factory('DARK SIDE')
  const officeId = OFFICE_ID_MIN
  const assignerId = staffs.find(x => x.officeIds.includes(officeId))!.id
  const assigneeId = staffs.find(x => x.officeIds.includes(officeId) && x.id !== assignerId)!.id

  type Form = DeepPartial<AttendancesApi.Form>
  const form: Form = {
    officeId,
    userId: USER_ID_MIN,
    contractId: CONTRACT_ID_MIN,
    assignerId,
    task: faker.randomElement(Task.values),
    assignees: [
      {
        staffId: assigneeId,
        isTraining: false,
        isUndecided: false
      },
      {
        staffId: assigneeId + 1,
        isTraining: false,
        isUndecided: false
      }
    ],
    schedule: {
      date: '2008-05-17',
      end: '19:00',
      start: '11:00'
    },
    durations: [],
    options: faker.randomElements(ServiceOption.values, faker.intBetween(0, ServiceOption.size)),
    note: ramen.ipsum(40)
  }

  let wrapper: Wrapper<Vue & any>

  type MountComponentArguments = MountOptions<Vue> & {
    isShallow?: true
    form?: Form
  }

  function mountComponent ({ isShallow, form, ...options }: MountComponentArguments = {}) {
    const value = { ...form, headcount: form?.assignees?.length ?? 1 }
    const fn = isShallow ? shallowMount : mount
    wrapper = fn(ZAttendanceForm, {
      propsData: {
        errors: {},
        buttonText: '登録',
        permission: Permission.updateAttendances,
        progress: false,
        value
      },
      ...options,
      mocks: {
        ...mocks,
        ...options?.mocks
      }
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    mocked(useStaffs).mockReturnValue(createUseStaffsStub(staffs))
    mocked(useUsers).mockReturnValue(createUseUsersStub())
  })

  afterAll(() => {
    mocked(useUsers).mockRestore()
    mocked(useStaffs).mockRestore()
    mocked(useOffices).mockRestore()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validateDurations', () => {
    let observer: ValidationObserverInstance

    beforeAll(() => {
      mountComponent({
        stubs: [
          'z-form-card',
          'z-form-card-item-set',
          'z-checkbox-container',
          'v-checkbox',
          'z-hour-and-minute-field',
          'z-form-action-button'
        ]
      })
      observer = wrapper.findComponent({ ref: 'durationsObserver' }).vm as any
      jest.spyOn(observer, 'validate').mockResolvedValue(true)
    })

    afterAll(() => {
      unmountComponent()
      mocked(observer.validate).mockRestore()
    })

    afterEach(() => {
      mocked(observer.validate).mockClear()
    })

    // TODO: validateDurations を呼び出しているすべてのフィールドでテストを行う
    it.each([
      ['[data-task-input-field]']
    ])('should be called when the %s emit an input event', async selector => {
      await wrapper.find(selector).trigger('input')
      expect(observer.validate).toHaveBeenCalledTimes(1)
    })
  })

  describe('second assignee area', () => {
    it('should be appeared when check enabled', async () => {
      await mountComponent({
        stubs: [
          'z-form-card',
          'z-form-card-item-set',
          'z-form-card-item',
          'z-checkbox-container',
          'z-hour-and-minute-field',
          'z-form-action-button'
        ]
      })
      expect(wrapper.find('[data-second-assignee-wrapper]')).not.toExist()
      await wrapper.find('[data-second-assignee-enabled]').trigger('click')
      expect(wrapper.find('[data-second-assignee-wrapper]')).toExist()
      unmountComponent()
    })
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: DeepPartial<AttendancesApi.Form> = {}) {
      await setData(wrapper, {
        form: { ...form, ...values }
      })
      await observer.validate()
      jest.runOnlyPendingTimers()
      await flushPromises()
      jest.runOnlyPendingTimers()
    }

    beforeAll(() => {
      mountComponent({
        stubs: [
          'z-form-card',
          'z-form-card-item-set',
          'z-checkbox-container',
          'v-checkbox',
          'z-hour-and-minute-field',
          'z-form-action-button'
        ]
      })
      observer = getValidationObserver(wrapper)
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should pass when input correctly', async () => {
      await validate()
      expect(observer).toBePassed()
    })

    it('should fail when task is empty', async () => {
      await validate({ task: undefined })
      expect(observer).not.toBePassed()
    })

    it.each<string, string, Task>([
      ['fail', 'DwsPhysicalCare', Task.dwsPhysicalCare],
      ['fail', 'DwsHousework', Task.dwsHousework],
      ['fail', 'DwsAccompanyWithPhysicalCare', Task.dwsAccompanyWithPhysicalCare],
      ['fail', 'DwsAccompany', Task.dwsAccompany],
      ['fail', 'DwsVisitingCareForPwsd', Task.dwsVisitingCareForPwsd],
      ['fail', 'LtcsPhysicalCare', Task.ltcsPhysicalCare],
      ['fail', 'LtcsHousework', Task.ltcsHousework],
      ['fail', 'LtcsPhysicalCareAndHousework', Task.ltcsPhysicalCareAndHousework],
      ['fail', 'Comprehensive', Task.comprehensive],
      ['fail', 'CommAccompanyWithPhysicalCare', Task.commAccompanyWithPhysicalCare],
      ['fail', 'CommAccompany', Task.commAccompany],
      ['fail', 'OwnExpense', Task.ownExpense],
      ['fail', 'Fieldwork', Task.fieldwork],
      ['fail', 'Assessment', Task.assessment],
      ['fail', 'Visit', Task.visit],
      ['pass', 'OfficeWork', Task.officeWork],
      ['pass', 'Sales', Task.sales],
      ['pass', 'Meeting', Task.meeting],
      ['pass', 'Other', Task.other]
    ])('should %s when userId is empty and task is %s', async (pass: string, _: string, task: Task) => {
      await validate({ userId: undefined, task })
      pass === 'pass' ? expect(observer).toBePassed() : expect(observer).not.toBePassed()
    })

    it('should pass even if serviceCode is empty', async () => {
      await validate({
        task: Task.ltcsPhysicalCare,
        serviceCode: undefined
      })
      expect(observer).toBePassed()
    })

    it.each([
      ['pass', '123456'],
      ['pass', '12A456'],
      ['fail', '12#456']
    ])('should %s when serviceCode is %s', async (pass, serviceCode) => {
      await validate({
        task: Task.ltcsPhysicalCare,
        serviceCode
      })
      pass === 'pass' ? expect(observer).toBePassed() : expect(observer).not.toBePassed()
    })

    it.each([
      ['pass', '', 0],
      ['fail', '1', 1],
      ['fail', '12', 2],
      ['fail', '123', 3],
      ['fail', '1234', 4],
      ['fail', '12345', 5],
      ['pass', '123456', 6],
      ['fail', '1234567', 7],
      ['fail', '12345678', 8]
    ])('should %s when serviceCode is %s (length = %d)', async (pass, serviceCode) => {
      await validate({
        task: Task.ltcsPhysicalCare,
        serviceCode
      })
      pass === 'pass' ? expect(observer).toBePassed() : expect(observer).not.toBePassed()
    })

    it('should fail when officeId is empty', async () => {
      await validate({ officeId: undefined })
      expect(observer).not.toBePassed()
    })

    it('should fail when assignerId is empty', async () => {
      await validate({ assignerId: undefined })
      expect(observer).not.toBePassed()
    })

    it('should pass even if assignee[0].staffId is empty when it is undefined', async () => {
      await validate({
        headcount: 1,
        assignees: [
          { staffId: undefined, isTraining: false, isUndecided: true }
        ]
      })
      expect(observer).toBePassed()
    })

    it('should fail assignee[0].staffId is empty and it is not undefined', async () => {
      await validate({
        headcount: 1,
        assignees: [
          { staffId: undefined, isTraining: false, isUndecided: false }
        ]
      })
      expect(observer).not.toBePassed()
    })

    it('should pass even if assignee[1].staffId is empty when headcount === 1', async () => {
      await validate({
        headcount: 1,
        assignees: [
          { staffId: undefined, isTraining: false, isUndecided: true },
          { staffId: undefined, isTraining: false, isUndecided: false }
        ]
      })
      expect(observer).toBePassed()
    })

    it('should pass even if assignee[1].staffId is empty when it is undefined', async () => {
      await validate({
        headcount: 2,
        assignees: [
          { staffId: undefined, isTraining: false, isUndecided: true },
          { staffId: undefined, isTraining: false, isUndecided: true }
        ]
      })
      expect(observer).toBePassed()
    })

    it('should fail assignee[1].staffId is empty and it is not undefined', async () => {
      await validate({
        headcount: 2,
        assignees: [
          { staffId: undefined, isTraining: false, isUndecided: true },
          { staffId: undefined, isTraining: false, isUndecided: false }
        ]
      })
      expect(observer).not.toBePassed()
    })

    it('should fail when schedule.date is empty', async () => {
      await validate({ schedule: { date: undefined } })
      expect(observer).not.toBePassed()
    })

    it('should fail when schedule.start is empty', async () => {
      await validate({ schedule: { start: undefined } })
      expect(observer).not.toBePassed()
    })

    it('should fail when schedule.end is empty', async () => {
      await validate({ schedule: { end: undefined } })
      expect(observer).not.toBePassed()
    })

    it('should fail when totalDuration does not equal to sum of durations', async () => {
      const durations = {
        housework: TimeDuration.create(1, 0),
        physicalCare: TimeDuration.create(3, 0),
        resting: TimeDuration.create(1, 0)
      }
      await setData(wrapper, { durations })
      await validate({
        task: Task.ltcsPhysicalCareAndHousework,
        schedule: {
          end: '13:00',
          start: '12:00'
        }
      })
      expect(observer).not.toBePassed()
    })
  })

  describe('autoAscii', () => {
    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    // FIXME
    it.skip('should update serviceCode when it blur', async () => {
      const value = {
        task: Task.ltcsPhysicalCare
      }
      await setProps(wrapper, { value })
      const input = wrapper.find('[data-service-code-input]')
      input.setValue('１２Ａ７８９')

      await blur(() => input)

      expect(wrapper.vm.form.serviceCode).toBe('12A789')
    })
  })

  describe('totalDuration', () => {
    beforeAll(() => {
      mountComponent({ isShallow: true })
    })

    afterAll(() => {
      unmountComponent()
    })

    it.each<number, Time, Time>([
      [150, '0:00', '2:30'],
      [600, '8:00', '18:00'],
      [720, '20:00', '8:00'],
      [1410, '12:00', '11:30']
    ])('should be %i when durationStart = %s and durationEnd = %s', async (minutes, start, end) => {
      const schedule = { start, end }
      const form = { schedule }
      await setData(wrapper, { form })
      expect(wrapper.vm.totalDuration.totalMinutes).toBe(minutes)
    })
  })

  describe('assignees', () => {
    it('should be set the initial value', () => {
      mountComponent({ isShallow: true })
      expect(wrapper.vm.form.assignees.length).toBe(1)
      expect(wrapper.vm.form.assignees[0]).toStrictEqual({
        staffId: undefined,
        isTraining: false,
        isUndecided: false
      })
      unmountComponent()
    })

    it('should be set the initial value when append second assignee', async () => {
      mountComponent({
        stubs: [
          'z-form-card',
          'z-form-card-item-set',
          'z-form-card-item',
          'z-checkbox-container',
          'z-hour-and-minute-field',
          'z-form-action-button'
        ]
      })
      await click(() => wrapper.find('[data-second-assignee-enabled]'))
      expect(wrapper.vm.form.assignees.length).toBe(2)
      expect(wrapper.vm.form.assignees[1]).toStrictEqual({
        staffId: undefined,
        isTraining: false,
        isUndecided: false
      })
      unmountComponent()
    })

    it('should have only 1 item when uncheck "second assignee enabled"', async () => {
      mountComponent({
        form,
        stubs: [
          'z-form-card',
          'z-form-card-item-set',
          'z-form-card-item',
          'z-checkbox-container',
          'z-hour-and-minute-field',
          'z-form-action-button'
        ]
      })
      expect(wrapper.vm.form.assignees.length).toBe(2)
      await click(() => wrapper.find('[data-second-assignee-enabled]'))
      expect(wrapper.vm.form.assignees.length).toBe(1)
      unmountComponent()
    })

    it('should item property is only isUndecided when submit forms if isUndecided is true', async () => {
      mountComponent({
        form: {
          ...form,
          assignees: [
            {
              staffId: undefined,
              isTraining: false,
              isUndecided: true
            },
            {
              staffId: assigneeId,
              isTraining: false,
              isUndecided: false
            }
          ]
        },
        stubs: [
          'z-form-card',
          'z-form-card-item-set',
          'z-form-card-item',
          'z-checkbox-container',
          'v-checkbox',
          'z-hour-and-minute-field',
          'z-form-action-button'
        ]
      })
      await submit(() => wrapper.find('[data-form]'))
      expect(wrapper.emitted('submit')![0][0].assignees).toStrictEqual([
        { isUndecided: true },
        {
          staffId: assigneeId,
          isTraining: false,
          isUndecided: false
        }
      ])
      unmountComponent()
    })
  })

  // TODO: 「担当スタッフ（1人目）」で選択済みのスタッフが「担当スタッフ（2人目）」に表示されないことの確認
  //  変則的だが、staffOptions を取得する関数が想定通り動いているかで確認している
  describe('getSelectableStaffOptions', () => {
    it('should return non-selected staff.', () => {
      const staff = staffs.find(({ id }) => id === assigneeId)!
      const expected = { text: staff.name.displayName, value: staff.id }
      mountComponent({ form, isShallow: true })
      expect(wrapper.vm.getSelectableStaffOptions()).toEqual(
        expect.arrayContaining([
          expect.objectContaining(expected)
        ])
      )
      expect(wrapper.vm.getSelectableStaffOptions()).toEqual(
        expect.arrayContaining([
          expect.not.objectContaining(expected)
        ])
      )
      unmountComponent()
    })
  })
})
