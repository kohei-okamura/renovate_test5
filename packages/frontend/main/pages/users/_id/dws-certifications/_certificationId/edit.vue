<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <v-card class="mt-4">
      <v-card-title class="subtitle-1">編集理由を選択してください。</v-card-title>
      <v-card-text class="pb-2">
        <v-radio-group v-model="reason" class="mt-0" :mandatory="false">
          <v-radio v-for="x in reasons" :key="x.reason" :label="x.text" :value="x.reason" v-bind="{ [x.data]: true }" />
        </v-radio-group>
      </v-card-text>
    </v-card>
    <v-fade-transition mode="out-in">
      <v-card v-if="reason === 'A' || reason === 'B'" key="guide" class="mt-4" data-message>
        <v-card-text data-card-text>
          <p>
            記載内容が変更されたり、新しい受給者証が発行された場合は、新しく登録を行ってください。<br>
            <span v-if="!hasPermissionOfCreate" class="error--text">
              受給者証を登録する権限が無いため新しく登録することができません。担当者にご確認ください。
            </span>
          </p>
        </v-card-text>
        <v-card-actions v-if="hasPermissionOfCreate" data-card-actions>
          <v-spacer />
          <v-btn
            color="primary"
            nuxt
            text
            :to="`/users/${user.id}/dws-certifications/new?certificationId=${dwsCertification.id}`"
          >
            <span>受給者証の登録へ</span>
          </v-btn>
        </v-card-actions>
      </v-card>
      <z-dws-certification-form
        v-else-if="reason === 'C'"
        key="form"
        button-text="保存"
        :errors="errors"
        :permission="permission"
        :progress="progress"
        :user="user"
        :value="value"
        data-form
        @submit="submit"
      />
    </v-fade-transition>
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent, ref } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { dwsCertificationStateKey } from '~/composables/stores/use-dws-certification-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useAuth } from '~/composables/use-auth'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { useUpdateUserDependant } from '~/composables/use-update-user-dependant'
import { auth } from '~/middleware/auth'
import { createCertificationFormValue } from '~/pages/users/_id/dws-certifications/-createCertificationFormValue'
import { DwsCertificationsApi } from '~/services/api/dws-certifications-api'

type Form = DeepPartial<DwsCertificationsApi.Form>

type Reason = '' | 'A' | 'B' | 'C'

type ReasonOption = {
  data: string
  reason: Reason
  text: string
}

export default defineComponent({
  name: 'DwsCertificationsEditPage',
  middleware: [auth(Permission.updateDwsCertifications)],
  setup () {
    const { isAuthorized, permissions } = useAuth()
    const { $api } = usePlugins()
    const { dwsCertification } = useInjected(dwsCertificationStateKey)
    const { user } = useInjected(userStateKey)
    const { errors, progress, updateUserDependant } = useUpdateUserDependant()
    const hasPermissionOfCreate = computed(() => isAuthorized.value([permissions.createDwsCertifications]))
    const useReasons = () => {
      const reason = ref<Reason>('')
      const reasons: ReasonOption[] = [
        { data: 'data-reason-a', reason: 'A', text: '受給者証の記載内容が変更されたので反映したい。' },
        { data: 'data-reason-b', reason: 'B', text: '新しい受給者証が発行されたので内容を書き換えたい。' },
        { data: 'data-reason-c', reason: 'C', text: '入力ミスなどの不正確な情報を訂正したい。' }
      ]
      return { reason, reasons }
    }
    return {
      ...useReasons(),
      ...useBreadcrumbs('users.dwsCertifications.edit', user, dwsCertification),
      dwsCertification,
      errors,
      hasPermissionOfCreate,
      permission: Permission.updateDwsCertifications,
      progress,
      user,
      value: createCertificationFormValue(dwsCertification.value!),
      submit: (form: Form) => {
        const id = dwsCertification.value!.id
        const userId = user.value!.id
        return updateUserDependant({
          dependant: '受給者証情報',
          userId,
          callback: () => $api.dwsCertifications.update({ form, id, userId }),
          hash: 'dws'
        })
      }
    }
  },
  head: () => ({
    title: '利用者障害福祉サービス受給者証を編集'
  })
})
</script>
