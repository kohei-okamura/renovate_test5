<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-dialog v-model="isActive" persistent transition="dialog" width="500">
    <v-form data-form @submit.prevent="submit">
      <v-card>
        <z-card-titlebar color="blue-grey">スタッフを招待</z-card-titlebar>
        <v-card-text>
          <validation-observer v-slot="{ errors: observerErrors }" ref="observer" tag="div">
            <validation-provider data-emails tag="div" vid="emails" :rules="rules.email">
              <z-multiple-entry-field
                v-model="form.emails"
                label="メールアドレス *"
                :error-messages="emailsError(observerErrors)"
              />
            </validation-provider>
            <validation-provider v-for="(email, i) in form.emails" v-show="false" :key="email" :vid="`emails.${i}`" />
            <validation-provider v-slot="{ errors }" data-office-ids tag="div" vid="officeIds" :rules="rules.officeIds">
              <z-keyword-filter-autocomplete
                v-model="form.officeIds"
                label="所属事業所 *"
                multiple
                small-chips
                :error-messages="errors"
                :items="officeOptions"
                :loading="isLoadingOffices"
              />
            </validation-provider>
            <validation-provider tag="div">
              <z-select
                v-model="form.officeGroupIds"
                label="事業所グループ"
                multiple
                small-chips
                :items="officeGroupOptions"
                :loading="isLoadingOfficeGroups"
              />
            </validation-provider>
            <validation-provider v-slot="{ errors }" data-role-ids tag="div" vid="roleIds" :rules="rules.roleIds">
              <z-select
                v-model="form.roleIds"
                label="ロール *"
                multiple
                small-chips
                :error-messages="errors"
                :items="roleOptions"
                :loading="isLoadingRoles"
              />
            </validation-provider>
          </validation-observer>
        </v-card-text>
        <v-card-actions class="pb-4 pt-0 px-4">
          <v-spacer />
          <v-btn data-cancel text :disabled="progress" @click.stop="close">キャンセル</v-btn>
          <v-btn color="primary" data-ok depressed type="submit" :disabled="progress" :loading="progress">招待</v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
  </v-dialog>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { useOfficeGroups } from '~/composables/use-office-groups'
import { useOffices } from '~/composables/use-offices'
import { useRoles } from '~/composables/use-roles'
import { useSyncedProp } from '~/composables/use-synced-prop'
import { InvitationsApi } from '~/services/api/invitations-api'
import { email, required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<InvitationsApi.Form> & Readonly<{
  dialog: boolean
}>

export default defineComponent<Props>({
  name: 'ZInvitationForm',
  props: {
    ...getFormPropsOptions(),
    dialog: { type: Boolean, required: true }
  },
  setup (props, context) {
    const useDialog = () => {
      const isActive = useSyncedProp('dialog', props, context)
      const close = () => {
        isActive.value = false
      }
      return { isActive, close }
    }
    const rules = validationRules({
      email: { required, email, max: 255 },
      officeIds: { required },
      roleIds: { required }
    })
    const emailsError = (errors: Record<string, string[]>) => {
      return Object.entries(errors).filter(([key]) => key.startsWith('emails')).flatMap(([_, value]) => value)
    }
    return {
      emailsError,
      ...useDialog(),
      ...useFormBindings(props, context, {
        resetValidatorOnReset: true
      }),
      ...useOffices({ permission: Permission.createStaffs, internal: true }),
      ...useOfficeGroups({ permission: Permission.createStaffs }),
      ...useRoles({ permission: Permission.createStaffs }),
      rules
    }
  }
})
</script>
