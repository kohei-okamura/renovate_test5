<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-dws-certification-form
      v-if="isResolved"
      button-text="登録"
      :errors="errors"
      :permission="permission"
      :progress="progress"
      :user="user"
      :value="value"
      @submit="submit"
    />
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { isEmpty } from '@zinger/helpers'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { useDwsCertificationStore } from '~/composables/stores/use-dws-certification-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useAsync } from '~/composables/use-async'
import { useCreateUserDependant } from '~/composables/use-create-user-dependant'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { createCertificationFormValue } from '~/pages/users/_id/dws-certifications/-createCertificationFormValue'
import { DwsCertificationsApi } from '~/services/api/dws-certifications-api'

type Form = Partial<DwsCertificationsApi.Form>

export default defineComponent({
  name: 'DwsCertificationsNewPage',
  middleware: [auth(Permission.createDwsCertifications)],
  setup () {
    const { $api, $route } = usePlugins()
    const { user } = useInjected(userStateKey)
    const store = useDwsCertificationStore()
    const value = computed(() => {
      const dwsCertification = store.state.dwsCertification.value
      return isEmpty(dwsCertification)
        ? {}
        : {
          ...createCertificationFormValue(dwsCertification),
          effectivatedOn: undefined,
          issuedOn: undefined,
          activatedOn: undefined,
          deactivatedOn: undefined,
          copayActivatedOn: undefined,
          copayDeactivatedOn: undefined,
          grants: dwsCertification.grants.map(grant => ({
            ...grant,
            activatedOn: undefined,
            deactivatedOn: undefined
          }))
        }
    })
    const { createUserDependant, errors, progress } = useCreateUserDependant()
    return {
      ...useAsync(() => {
        return $route.params.id && $route.query.certificationId
          ? store.get({
            id: +$route.query.certificationId,
            userId: +$route.params.id
          })
          : Promise.resolve()
      }),
      ...useBreadcrumbs('users.dwsCertifications.new', user),
      errors,
      permission: Permission.createDwsCertifications,
      progress,
      user,
      value,
      submit: (form: Form) => {
        const userId = user.value!.id
        return createUserDependant({
          dependant: '障害福祉サービス受給者証',
          userId,
          callback: () => $api.dwsCertifications.create({ form, userId }),
          hash: 'dws'
        })
      }
    }
  },
  head: () => ({
    title: '利用者障害福祉サービス受給者証を登録'
  })
})
</script>
