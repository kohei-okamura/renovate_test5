<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-z-attendance-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-form-card title="基本情報">
        <z-form-card-item-set :icon="$icons.category">
          <z-form-card-item v-slot="{ errors }" data-task vid="task" :rules="rules.task">
            <z-select
              v-model="form.task"
              data-task-input-field
              label="勤務実績区分 *"
              :error-messages="errors"
              :items="tasks"
              @input="validateDurations"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set v-if="task.isLongTermCareService" :icon="$icons.serviceCode">
          <z-form-card-item v-slot="{ errors }" data-service-code vid="serviceCode" :rules="rules.serviceCode">
            <z-text-field
              v-model.trim="form.serviceCode"
              v-auto-ascii
              data-service-code-input
              label="サービスコード"
              :error-messages="errors"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.office">
          <z-form-card-item v-slot="{ errors }" data-office vid="officeId" :rules="rules.officeId">
            <z-keyword-filter-autocomplete
              v-model="form.officeId"
              label="事業所 *"
              :error-messages="errors"
              :items="officeOptions"
              :loading="isLoadingOffices"
            />
            <z-select
              v-model="form.officeId"
              label="事業所 *"
              :error-messages="errors"
              :items="officeOptions"
              :loading="isLoadingOffices"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set v-if="task.isVisiting" :icon="$icons.user">
          <z-form-card-item v-slot="{ errors }" data-user vid="userId" :rules="rules.userId">
            <z-select
              v-model="form.userId"
              label="利用者 *"
              persistent-hint
              :disabled="!isUserSelectable"
              :error-messages="errors"
              :items="userOptions"
              :loading="isLoadingUsers"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.staff">
          <z-form-card-item v-slot="{ errors }" data-assigner vid="assignerId" :rules="rules.assignerId">
            <z-select
              v-model="form.assignerId"
              label="管理スタッフ *"
              persistent-hint
              :disabled="!isStaffSelectable"
              :error-messages="errors"
              :hint="assignerHint"
              :items="staffOptions"
              :loading="isLoadingStaffs"
            />
          </z-form-card-item>
          <z-flex>
            <z-form-card-item
              v-slot="{ errors }"
              data-first-assignee
              vid="assignee.0.staffId"
              :rules="rules.assignees.first.staffId"
            >
              <z-select
                v-model="form.assignees[0].staffId"
                label="担当スタッフ（1人目） *"
                persistent-hint
                :disabled="!isStaffSelectable || form.assignees[0].isUndecided"
                :error-messages="errors"
                :hint="assigneeHint"
                :items="getSelectableStaffOptions(form.assignees[1])"
                :loading="isLoadingStaffs"
              />
            </z-form-card-item>
            <z-checkbox-container v-if="task.isTrainingEnabled">
              <v-checkbox
                v-model="form.assignees[0].isTraining"
                label="研修"
                :disabled="!isOfficeSelected || form.assignees[0].isUndecided"
              />
            </z-checkbox-container>
            <z-checkbox-container>
              <v-checkbox v-model="form.assignees[0].isUndecided" label="未定" :disabled="!isOfficeSelected" />
            </z-checkbox-container>
          </z-flex>
          <z-form-card-item vid="second.assignee.enabled" :rules="rules.options">
            <v-checkbox
              v-model="isSecondAssigneeEnabled"
              data-second-assignee-enabled
              hide-details
              label="担当スタッフ（2人目）を設定する"
            />
          </z-form-card-item>
          <z-flex v-if="isSecondAssigneeEnabled" data-second-assignee-wrapper>
            <z-form-card-item
              v-slot="{ errors }"
              data-second-assignee
              vid="assignee.1.staffId"
              :rules="rules.assignees.second.staffId"
            >
              <z-select
                v-model="form.assignees[1].staffId"
                label="担当スタッフ（2人目） *"
                persistent-hint
                :disabled="!isStaffSelectable || form.assignees[1].isUndecided"
                :error-messages="errors"
                :hint="assigneeHint"
                :items="getSelectableStaffOptions(form.assignees[0])"
                :loading="isLoadingStaffs"
              />
            </z-form-card-item>
            <z-checkbox-container v-if="task.isTrainingEnabled">
              <v-checkbox
                v-model="form.assignees[1].isTraining"
                label="研修"
                :disabled="!isOfficeSelected || form.assignees[1].isUndecided"
              />
            </z-checkbox-container>
            <z-checkbox-container>
              <v-checkbox v-model="form.assignees[1].isUndecided" label="未定" :disabled="!isOfficeSelected" />
            </z-checkbox-container>
          </z-flex>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.note">
          <z-form-card-item v-slot="{ errors }" data-note vid="note" :rules="rules.note">
            <z-text-field v-model.trim="form.note" label="備考" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-card title="勤務日・勤務時間">
        <z-form-card-item-set :icon="$icons.schedule">
          <v-row no-gutters>
            <v-col cols="12" md="6">
              <z-form-card-item v-slot="{ errors }" data-schedule-date vid="schedule.date" :rules="rules.schedule.date">
                <z-date-field v-model="form.schedule.date" label="勤務日 *" :error-messages="errors" :max="today" />
              </z-form-card-item>
            </v-col>
            <v-col class="d-flex" cols="12" md="6">
              <z-form-card-item
                v-slot="{ errors }"
                data-schedule-start
                vid="schedule.start"
                :rules="rules.schedule.start"
              >
                <z-text-field
                  v-model="form.schedule.start"
                  type="time"
                  label="開始 *"
                  :error-messages="errors"
                  @input="validateDurations"
                />
              </z-form-card-item>
              <z-flex-shrink class="pb-2 pl-2 pt-4">〜</z-flex-shrink>
              <z-form-card-item v-slot="{ errors }" data-schedule-end vid="schedule.end" :rules="rules.schedule.end">
                <z-text-field
                  v-model="form.schedule.end"
                  type="time"
                  label="終了 *"
                  :error-messages="errors"
                  @input="validateDurations"
                />
              </z-form-card-item>
            </v-col>
          </v-row>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.timeAmount">
          <validation-observer ref="durationsObserver" tag="div" vid="durations">
            <v-row no-gutters>
              <template v-if="task.isPhysicalCareAndHousework">
                <v-col cols="12" sm="6">
                  <z-form-card-item
                    v-slot="{ errors }"
                    data-duration-physical-care
                    vid="durationPhysicalCare"
                    :rules="rules.durationPhysicalCare"
                  >
                    <z-hour-and-minute-field
                      v-model="durations.physicalCare"
                      label="身体介護 *"
                      :error-messages="errors"
                      @input="validateDurations"
                    />
                  </z-form-card-item>
                </v-col>
                <v-col cols="12" sm="6">
                  <z-form-card-item
                    v-slot="{ errors }"
                    data-duration-housework
                    vid="durationHousework"
                    :rules="rules.durationHousework"
                  >
                    <z-hour-and-minute-field
                      v-model="durations.housework"
                      label="生活援助 *"
                      :error-messages="errors"
                      @input="validateDurations"
                    />
                  </z-form-card-item>
                </v-col>
              </template>
              <v-col v-if="task.isDwsVisitingCareForPwsd" cols="12" sm="6">
                <z-form-card-item
                  v-slot="{ errors }"
                  data-duration-dws-outing-support-for-pwsd
                  vid="durationDwsOutingSupportForPwsd"
                  :rules="rules.durationDwsOutingSupportForPwsd"
                >
                  <z-hour-and-minute-field
                    v-model="durations.dwsOutingSupportForPwsd"
                    data-duration-dws-outing-support-for-pwsd-input-field
                    label="移動加算 *"
                    :error-messages="errors"
                    @input="validateDurations"
                  />
                </z-form-card-item>
              </v-col>
              <v-col cols="12" sm="6">
                <z-form-card-item
                  v-slot="{ errors }"
                  data-duration-resting
                  vid="durationResting"
                  :rules="rules.durationResting"
                >
                  <z-hour-and-minute-field
                    v-model="durations.resting"
                    label="休憩 *"
                    :error-messages="errors"
                    @input="validateDurations"
                  />
                </z-form-card-item>
              </v-col>
              <v-col cols="12" sm="6">
                <z-form-card-item>
                  <z-hour-and-minute-field disabled label="勤務時間" readonly :value="totalDuration" />
                </z-form-card-item>
              </v-col>
            </v-row>
          </validation-observer>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-card v-if="hasOptionItems" title="各種オプション">
        <z-form-card-item-set class="mt-n3" no-icon>
          <template v-for="x in optionItems">
            <z-form-card-item
              v-if="x.enabled"
              :key="x.value"
              :class="$style.option"
              :rules="rules.options"
            >
              <v-checkbox v-model="form.options" persistent-hint :hint="x.hint" :label="x.text" :value="x.value" />
            </z-form-card-item>
          </template>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-action-button :disabled="progress" :icon="$icons.save" :loading="progress" :text="buttonText" />
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { computed, defineComponent, toRefs } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { Task } from '@zinger/enums/lib/task'
import { assign, nonEmpty } from '@zinger/helpers'
import { createTaskServiceOptions } from '~/composables/create-service-options'
import { enumerableOptions } from '~/composables/enumerable-options'
import { useAsyncValidate } from '~/composables/use-async-validate'
import { useDurations } from '~/composables/use-durations'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { useServiceOptionItems } from '~/composables/use-service-option-items'
import { useStaffs } from '~/composables/use-staffs'
import { useUsers } from '~/composables/use-users'
import { autoAscii } from '~/directives/auto-ascii'
import { Assignee } from '~/models/assignee'
import { DateString } from '~/models/date'
import { TimeRange } from '~/models/range'
import { scheduleFromTimeRange } from '~/models/schedule'
import { isLongTermCareService, isTrainingEnabled, isVisiting } from '~/models/task-utils'
import { AttendancesApi } from '~/services/api/attendances-api'
import { observerRef } from '~/support/reactive'
import { asciiAlphaNum, required, validTimeDuration } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Form = Overwrite<AttendancesApi.Form, {
  schedule: Partial<TimeRange> & {
    date: DateString | undefined
  }
}>

type Props = FormProps<Form> & Readonly<{
  buttonText: string
  permission: Permission
}>

export default defineComponent<Props>({
  name: 'ZAttendanceForm',
  directives: {
    autoAscii
  },
  props: {
    ...getFormPropsOptions(),
    buttonText: { type: String, required: true },
    permission: { type: String, required: true }
  },
  setup (props, context) {
    const propRefs = toRefs(props)
    const createAssignee = () => ({
      staffId: undefined,
      isUndecided: false,
      isTraining: false
    })
    const { $datetime } = usePlugins()
    const { durations, getTotalDuration, getOutputDurations } = useDurations(props.value.durations)
    const { form, observer, submit } = useFormBindings(props, context, {
      init: form => ({
        task: form.task ?? undefined,
        officeId: form.officeId ?? undefined,
        headcount: form.headcount ?? 1,
        schedule: form.schedule ?? {
          date: undefined,
          start: undefined,
          end: undefined
        },
        assignees: form.assignees ?? [createAssignee()],
        options: form.options ?? []
      }),
      processOutput: output => ({
        ...output,
        assignees: output.assignees?.map(x => x.isUndecided ? { isUndecided: x.isUndecided } : x),
        durations: getOutputDurations(output.task!, output.schedule),
        schedule: scheduleFromTimeRange(output.schedule)
      })
    })
    const useSelectOptions = () => ({
      tasks: enumerableOptions(Task)
    })
    const durationsObserver = observerRef()
    const validateDurations = useAsyncValidate(durationsObserver)
    const officeIds = computed(() => form.officeId ? [form.officeId] : undefined)
    const { isLoadingStaffs, staffOptions } = useStaffs({ officeIds, permission: propRefs.permission })
    const getSelectableStaffOptions = computed(() => {
      const xs = staffOptions.value
      return (opposite?: Assignee) => opposite ? xs.filter(({ value }) => value !== opposite.staffId) : xs
    })
    const { userOptions, isLoadingUsers } = useUsers({ officeIds, permission: propRefs.permission })
    const isOfficeSelected = computed(() => nonEmpty(form.officeId))
    const isSecondAssigneeEnabled = computed({
      get: () => form.headcount === 2,
      set: value => {
        const firstAssignee = form.assignees![0]
        const [headcount, assignees] = value ? [2, [firstAssignee, createAssignee()]] : [1, [firstAssignee]]
        assign(form, { headcount, assignees })
      }
    })
    const isStaffSelectable = computed(() => isOfficeSelected.value && !isLoadingStaffs.value)
    const isUserSelectable = computed(() => isOfficeSelected.value && !isLoadingUsers.value)
    const useHints = () => {
      const createHint = (hint: string) => computed(() => {
        return isOfficeSelected.value ? (isLoadingStaffs.value ? undefined : hint) : ''
      })
      return {
        assigneeHint: createHint('実際に業務を担当するスタッフを選択してください。'),
        assignerHint: createHint('勤務実績を管理するスタッフ（責任者）を選択してください。')
      }
    }
    const totalDuration = computed(() => getTotalDuration(form.schedule))
    const rules = computed(() => {
      const assignees = form.assignees ?? []
      const task = form.task
      const custom = ((task, total) => {
        let message = ''
        let expression = true
        if (!total.isZero) {
          if (task === Task.ltcsPhysicalCareAndHousework) {
            message = '合計と勤務時間が一致するようにしてください。'
            expression = durations.housework.plus(durations.physicalCare).plus(durations.resting).equalsTo(total)
          } else if (task === Task.dwsVisitingCareForPwsd) {
            message = '合計が勤務時間を超えないようにしてください。'
            expression = durations.dwsOutingSupportForPwsd.plus(durations.resting).lessThanOrEqualTo(total)
          } else {
            message = '休憩が勤務時間を超えないようにしてください。'
            expression = durations.resting.lessThanOrEqualTo(total)
          }
        }
        return { message, validate: () => expression ?? true }
      })(task, totalDuration.value)
      return validationRules({
        task: { required },
        serviceCode: { asciiAlphaNum, length: 6 },
        officeId: { required },
        assignerId: { required },
        userId: { required: isVisiting(task) },
        headcount: { required },
        assignees: {
          first: {
            staffId: { required: !assignees[0]?.isUndecided },
            isUndecided: {},
            isTraining: {}
          },
          second: {
            staffId: { required: isSecondAssigneeEnabled.value && !assignees[1]?.isUndecided },
            isUndecided: {},
            isTraining: {}
          }
        },
        schedule: {
          date: { required },
          start: { required },
          end: { required }
        },
        durationPhysicalCare: { validTimeDuration, custom },
        durationHousework: { validTimeDuration, custom },
        durationDwsOutingSupportForPwsd: { validTimeDuration, custom },
        durationResting: { validTimeDuration, custom },
        note: { max: 200 }
      })
    })
    const task = computed(() => {
      const task = form.task
      return {
        isLongTermCareService: isLongTermCareService(task),
        isPhysicalCareAndHousework: task === Task.ltcsPhysicalCareAndHousework,
        isDwsVisitingCareForPwsd: task === Task.dwsVisitingCareForPwsd,
        isTrainingEnabled: isTrainingEnabled(task),
        isVisiting: isVisiting(task)
      }
    })
    return {
      ...useHints(),
      ...useOffices({ permission: propRefs.permission, internal: true }),
      ...useSelectOptions(),
      ...useServiceOptionItems(() => createTaskServiceOptions(form.task), () => form.options?.splice(0)),
      durationsObserver,
      durations,
      form,
      getSelectableStaffOptions,
      isLoadingStaffs,
      isLoadingUsers,
      isOfficeSelected,
      isSecondAssigneeEnabled,
      isStaffSelectable,
      isUserSelectable,
      observer,
      rules,
      task,
      staffOptions,
      today: $datetime.now.toISODate(),
      totalDuration,
      userOptions,
      validateDurations,
      submit
    }
  }
})
</script>

<style lang="scss" module>
.option {
  margin-left: 0;

  & + .option {
    margin-top: 16px;
  }
}
</style>
