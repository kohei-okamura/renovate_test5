<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-user-summary :user="user" />
      <z-form-card title="介護保険サービス計画">
        <z-form-card-item-set :icon="$icons.office">
          <z-form-card-item v-slot="{ errors }" data-office-id vid="officeId" :rules="rules.officeId">
            <z-keyword-filter-autocomplete
              v-model="form.officeId"
              label="事業所 *"
              :error-messages="errors"
              :items="officeOptions"
              :loading="isLoadingOffices"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.staff">
          <z-form-card-item v-slot="{ errors }" data-staff-id vid="staffId" :rules="rules.staffId">
            <z-select
              v-model="form.staffId"
              label="計画責任者（サ責） *"
              :disabled="!isStaffSelectable"
              :error-messages="errors"
              :items="staffOptions"
              :loading="isLoadingStaffs"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.date">
          <z-form-card-item
            v-slot="{ errors }"
            data-written-on
            vid="writtenOn"
            :rules="rules.writtenOn"
          >
            <z-date-field v-model="form.writtenOn" label="作成日 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.blank">
          <z-form-card-item
            v-slot="{ errors }"
            data-effectivated-on
            vid="effectivatedOn"
            :rules="rules.effectivatedOn"
          >
            <z-date-field v-model="form.effectivatedOn" label="適用日 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.problem">
          <z-form-card-item
            v-slot="{ errors }"
            data-problem
            vid="problem"
            :rules="rules.problem"
          >
            <z-textarea v-model.trim="form.problem" label="解決すべき課題 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.wish">
          <z-form-card-item
            v-slot="{ errors }"
            data-request-from-user
            vid="requestFromUser"
            :rules="rules.requestFromUser"
          >
            <z-textarea v-model.trim="form.requestFromUser" label="ご本人の希望 *" :error-messages="errors" />
          </z-form-card-item>
          <z-form-card-item
            v-slot="{ errors }"
            data-request-from-family
            vid="requestFromFamily"
            :rules="rules.requestFromFamily"
          >
            <z-textarea v-model.trim="form.requestFromFamily" label="ご家族の希望 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.dateRange">
          <z-flex>
            <z-form-card-item
              v-slot="{ errors }"
              data-long-term-objective-term-start
              vid="longTermObjectiveTermStart"
              :rules="rules.longTermObjective.term.start"
            >
              <z-date-field
                v-model="form.longTermObjective.term.start"
                label="長期目標期間 *"
                :error-messages="errors"
              />
            </z-form-card-item>
            <z-flex-shrink class="pb-2 pl-2 pt-4">〜</z-flex-shrink>
            <z-form-card-item
              v-slot="{ errors }"
              data-long-term-objective-term-end
              vid="longTermObjectiveTermEnd"
              :rules="rules.longTermObjective.term.end"
            >
              <z-date-field v-model="form.longTermObjective.term.end" :error-messages="errors" />
            </z-form-card-item>
          </z-flex>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.blank">
          <z-form-card-item
            v-slot="{ errors }"
            data-long-term-objective-text
            vid="longTermObjectiveText"
            :rules="rules.longTermObjective.text"
          >
            <z-textarea v-model.trim="form.longTermObjective.text" label="長期目標 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.dateRange">
          <z-flex>
            <z-form-card-item
              v-slot="{ errors }"
              data-short-term-objective-term-start
              vid="shortTermObjectiveTermStart"
              :rules="rules.shortTermObjective.term.start"
            >
              <z-date-field
                v-model="form.shortTermObjective.term.start"
                label="短期目標期間 *"
                :error-messages="errors"
              />
            </z-form-card-item>
            <z-flex-shrink class="pb-2 pl-2 pt-4">〜</z-flex-shrink>
            <z-form-card-item
              v-slot="{ errors }"
              data-short-term-objective-term-end
              vid="shortTermObjectiveTermEnd"
              :rules="rules.shortTermObjective.term.end"
            >
              <z-date-field v-model="form.shortTermObjective.term.end" :error-messages="errors" />
            </z-form-card-item>
          </z-flex>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.blank">
          <z-form-card-item
            v-slot="{ errors }"
            data-short-term-objective-text
            vid="shortTermObjectiveText"
            :rules="rules.shortTermObjective.text"
          >
            <z-textarea v-model.trim="form.shortTermObjective.text" label="短期目標 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <transition-group name="card-list" tag="div">
        <z-ltcs-project-weekly-services-edit-card
          v-for="i in form.programs.length"
          :key="programKeys[i - 1]"
          v-model="form.programs[i - 1]"
          data-weekly-card
          :effectivated-on="form.effectivatedOn"
          :office-id="form.officeId"
          @click:copy="copyProgram"
          @click:delete="deleteProgram"
        />
      </transition-group>
      <z-validate-error-messages
        v-slot="{ errors }"
        v-model="form.programs.length"
        class="mt-3"
        data-programs
        vid="programs"
        :rules="rules.programs"
      >
        <v-alert class="mb-2" dense type="error" :icon="false">
          {{ errors[0] }}
        </v-alert>
      </z-validate-error-messages>
      <v-card class="mt-3">
        <v-card-text class="align-center d-flex justify-center pa-2">
          <v-btn block color="primary" data-add-program text @click="addProgram()">
            <v-icon left>{{ $icons.add }}</v-icon>
            <span>週間サービス計画表を追加</span>
          </v-btn>
        </v-card-text>
      </v-card>
      <z-form-action-button
        :disabled="progress"
        :fixed="true"
        :icon="$icons.save"
        :loading="progress"
        :text="buttonText"
      />
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { computed, defineComponent, nextTick, toRefs } from '@nuxtjs/composition-api'
import { resolveDayOfWeek } from '@zinger/enums/lib/day-of-week'
import { Permission } from '@zinger/enums/lib/permission'
import { assign, isEmpty, nonEmpty } from '@zinger/helpers'
import { createArrayWrapper } from '~/composables/create-array-wrapper'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { useStaffs } from '~/composables/use-staffs'
import { User } from '~/models/user'
import { LtcsProjectsApi } from '~/services/api/ltcs-projects-api'
import { $datetime } from '~/services/datetime-service'
import { required } from '~/support/validation/rules'
import { Rules } from '~/support/validation/types'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<Writable<LtcsProjectsApi.Form>> & Readonly<{
  buttonText: string
  permission: Permission
  user: User
}>

export default defineComponent<Props>({
  name: 'ZLtcsProjectForm',
  props: {
    ...getFormPropsOptions(),
    buttonText: { type: String, required: true },
    permission: { type: String, required: true },
    user: { type: Object, required: true }
  },
  setup: (props, context) => {
    const propRefs = toRefs(props)
    const { $confirm, $snackbar } = usePlugins()
    const { form, observer, submit } = useFormBindings(props, context, {
      init: form => ({
        longTermObjective: form.longTermObjective ?? { term: {} },
        shortTermObjective: form.shortTermObjective ?? { term: {} },
        programs: form.programs ?? [{
          amounts: [],
          contents: [{ duration: 0 }],
          dayOfWeeks: [],
          headcount: 1,
          options: [],
          ownExpenseProgramId: undefined,
          programIndex: 1,
          slot: {}
        }],
        officeId: form.officeId,
        staffId: form.staffId
      })
    })
    const programsWrappers = createArrayWrapper(form.programs ?? [])
    const usePrograms = () => ({
      programKeys: programsWrappers.keys,
      addProgram: () => {
        programsWrappers.push({
          amounts: [],
          contents: [{ menuId: undefined, duration: 0, content: '', memo: '' }],
          dayOfWeeks: [],
          headcount: 1,
          options: [],
          ownExpenseProgramId: undefined,
          programIndex: (form.programs?.length ?? 0) + 1,
          serviceCode: undefined,
          slot: {}
        })
      },
      deleteProgram: async (index: number) => {
        const confirmed = await $confirm.show({
          message: `「週間サービス計画（No.${index}）」を削除します。\n\n本当によろしいですか？`,
          positive: '削除'
        })
        if (confirmed) {
          programsWrappers.remove(index - 1)
          form.programs!.forEach((program, i) => { program.programIndex = i + 1 })
        }
      },
      copyProgram: (index: number) => {
        programsWrappers.push(assign({}, form.programs![index - 1]))
        form.programs!.forEach((program, i) => { program.programIndex = i + 1 })
        $snackbar.success(`「週間サービス計画（No.${index}）」をコピーしました。`)
        nextTick(() => {
          const element = document.querySelector<HTMLElement>(`#weekly-services-card_${form.programs?.length}`)
          if (element) {
            window.scrollTo({
              top: element.offsetTop,
              behavior: 'smooth'
            })
          }
        })
      }
    })
    const { staffOptions, isLoadingStaffs } = useStaffs({
      officeIds: computed(() => form.officeId ? [form.officeId] : undefined),
      permission: propRefs.permission
    })
    const isOfficeSelected = computed(() => nonEmpty(form.officeId))
    const rules = computed<Rules>(() => {
      const longTermStart = form.longTermObjective?.term?.start
      const longTermEnd = form.longTermObjective?.term?.end
      const shortTermStart = form.shortTermObjective?.term?.start
      const shortTermEnd = form.shortTermObjective?.term?.end
      const customLongTerm = {
        message: '開始日より終了日の日付を後にしてください。',
        validate: () =>
          isEmpty(longTermStart) ||
          isEmpty(longTermEnd) ||
          $datetime.parse(longTermStart) < $datetime.parse(longTermEnd)
      }
      const customShortTerm = {
        message: '開始日より終了日の日付を後にしてください。',
        validate: () =>
          isEmpty(shortTermStart) ||
          isEmpty(shortTermEnd) ||
          $datetime.parse(shortTermStart) < $datetime.parse(shortTermEnd)
      }
      return validationRules({
        effectivatedOn: { required },
        officeId: { required },
        problem: { required, max: 255 },
        programs: {
          nonItemsZero: { itemName: '週間サービス計画表' }
        },
        requestFromUser: { required, max: 255 },
        requestFromFamily: { required, max: 255 },
        longTermObjective: {
          term: {
            start: { custom: customLongTerm, required },
            end: { custom: customLongTerm, required }
          },
          text: { required, max: 255 }
        },
        shortTermObjective: {
          term: {
            start: { custom: customShortTerm, required },
            end: { custom: customShortTerm, required }
          },
          text: { required, max: 255 }
        },
        staffId: { required },
        writtenOn: { required }
      })
    })
    return {
      ...useOffices({ permission: propRefs.permission, internal: true }),
      ...usePrograms(),
      form,
      isLoadingStaffs,
      isOfficeSelected,
      isStaffSelectable: computed(() => isOfficeSelected.value && !isLoadingStaffs.value),
      observer,
      resolveDayOfWeek,
      rules,
      staffOptions,
      submit
    }
  }
})
</script>
