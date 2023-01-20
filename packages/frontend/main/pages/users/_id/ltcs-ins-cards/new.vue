<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-ltcs-ins-card-form
      v-if="isResolved"
      button-text="登録"
      :errors="errors"
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
import { useLtcsInsCardStore } from '~/composables/stores/use-ltcs-ins-card-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useAsync } from '~/composables/use-async'
import { useCreateUserDependant } from '~/composables/use-create-user-dependant'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { createLtcsInsCardFormValue } from '~/pages/users/_id/ltcs-ins-cards/-createLtcsInsCardFormValue'
import { LtcsInsCardsApi } from '~/services/api/ltcs-ins-cards-api'

type Form = Partial<LtcsInsCardsApi.Form>

export default defineComponent({
  name: 'LtcsInsCardsNewPage',
  middleware: [auth(Permission.createLtcsInsCards)],
  setup () {
    const { $api, $route } = usePlugins()
    const { user } = useInjected(userStateKey)
    const store = useLtcsInsCardStore()
    const value = computed(() => {
      const ltcsInsCard = store.state.ltcsInsCard.value
      return isEmpty(ltcsInsCard)
        ? {}
        : {
          ...createLtcsInsCardFormValue(ltcsInsCard),
          effectivatedOn: undefined,
          issuedOn: undefined,
          certificatedOn: undefined,
          activatedOn: undefined,
          deactivatedOn: undefined,
          copayActivatedOn: undefined,
          copayDeactivatedOn: undefined
        }
    })
    const { createUserDependant, errors, progress } = useCreateUserDependant()
    return {
      ...useAsync(() => {
        return $route.params.id && $route.query.ltcsInsCardId
          ? store.get({
            id: +$route.query.ltcsInsCardId,
            userId: +$route.params.id
          })
          : Promise.resolve()
      }),
      ...useBreadcrumbs('users.ltcsInsCards.new', user),
      errors,
      progress,
      user,
      value,
      submit: (form: Form) => {
        const userId = user.value!.id
        return createUserDependant({
          dependant: '介護保険被保険者証',
          userId,
          callback: () => $api.ltcsInsCards.create({ form, userId }),
          hash: 'ltcs'
        })
      }
    }
  },
  head: () => ({
    title: '利用者介護保険被保険者証を登録'
  })
})
</script>
