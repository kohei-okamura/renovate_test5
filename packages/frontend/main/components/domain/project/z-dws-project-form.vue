<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-user-summary :user="user" />
      <z-form-card title="障害福祉サービス計画">
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
        <z-form-card-item-set :icon="$icons.objective">
          <z-form-card-item
            v-slot="{ errors }"
            data-objective
            vid="objective"
            :rules="rules.objective"
          >
            <z-textarea v-model.trim="form.objective" label="援助目標 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <transition-group name="card-list" tag="div">
        <z-dws-project-weekly-services-edit-card
          v-for="i in form.programs.length"
          :key="programKeys[i - 1]"
          v-model="form.programs[i - 1]"
          :office-id="form.officeId"
          data-weekly-card
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
import { computed, defineComponent, toRefs } from '@nuxtjs/composition-api'
import { resolveDayOfWeek } from '@zinger/enums/lib/day-of-week'
import { Permission } from '@zinger/enums/lib/permission'
import { assign, nonEmpty } from '@zinger/helpers'
import { createArrayWrapper } from '~/composables/create-array-wrapper'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { useStaffs } from '~/composables/use-staffs'
import { User } from '~/models/user'
import { DwsProjectsApi } from '~/services/api/dws-projects-api'
import { required } from '~/support/validation/rules'
import { Rules } from '~/support/validation/types'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<DwsProjectsApi.Form> & Readonly<{
  buttonText: string
  permission: Permission
  user: User
}>

export default defineComponent<Props>({
  name: 'ZDwsProjectForm',
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
        programs: form.programs ?? [{
          contents: [{ duration: 0 }],
          dayOfWeeks: [],
          options: [],
          slot: {},
          summaryIndex: 1
        }],
        officeId: form.officeId ?? undefined,
        staffId: form.staffId ?? undefined
      })
    })
    const programsWrappers = createArrayWrapper(form.programs ?? [])
    const usePrograms = () => ({
      programKeys: programsWrappers.keys,
      addProgram: () => {
        programsWrappers.push({
          contents: [{ menuId: undefined, duration: 0, content: '', memo: '' }],
          dayOfWeeks: [],
          options: [],
          slot: {},
          summaryIndex: (form.programs?.length ?? 0) + 1
        })
      },
      deleteProgram: async (index: number) => {
        const confirmed = await $confirm.show({
          message: `「週間サービス計画（No.${index}）」を削除します。\n\n本当によろしいですか？`,
          positive: '削除'
        })
        if (confirmed) {
          programsWrappers.remove(index - 1)
          form.programs!.forEach((program, i) => {
            program.summaryIndex = i + 1
          })
        }
      },
      copyProgram: (index: number) => {
        programsWrappers.push(assign({}, form.programs![index - 1]))
        form.programs!.forEach((program, i) => {
          program.summaryIndex = i + 1
        })
        $snackbar.success(`「週間サービス計画（No.${index}）」をコピーしました。`)
        context.root.$nuxt.$nextTick(() => {
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
    const rules = computed<Rules>(() => {
      return validationRules({
        effectivatedOn: { required },
        objective: { required, max: 255 },
        officeId: { required },
        problem: { required, max: 255 },
        programs: { nonItemsZero: { itemName: '週間サービス計画表' } },
        requestFromFamily: { required, max: 255 },
        requestFromUser: { required, max: 255 },
        staffId: { required },
        writtenOn: { required }
      })
    })
    return {
      ...useOffices({ permission: propRefs.permission, internal: true }),
      ...usePrograms(),
      form,
      isLoadingStaffs,
      isStaffSelectable: computed(() => nonEmpty(form.officeId) && !isLoadingStaffs.value),
      observer,
      resolveDayOfWeek,
      rules,
      staffOptions,
      submit
    }
  }
})
</script>
