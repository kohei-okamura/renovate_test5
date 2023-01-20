<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-form-dialog v-model="isActive" :progress="progress" :title="title" @submit="submit">
    <v-card-text>
      <validation-observer ref="observer" tag="div">
        <div v-if="hasParentGroup" data-parent-group>
          <z-text-field disabled label="親事業所グループ" readonly :value="parentGroup.name" />
        </div>
        <validation-provider v-slot="{ errors }" data-name tag="div" vid="name" :rules="rules.name">
          <z-text-field v-model.trim="form.name" label="事業所グループ名 *" :error-messages="errors" />
        </validation-provider>
      </validation-observer>
    </v-card-text>
    <v-card-actions class="pb-4 pt-0 px-4">
      <v-spacer />
      <v-btn data-cancel text :disabled="progress" @click.stop="close">キャンセル</v-btn>
      <v-btn color="primary" data-ok depressed type="submit" :disabled="progress" :loading="progress">
        <span>{{ buttonText }}</span>
      </v-btn>
    </v-card-actions>
  </z-form-dialog>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { nonEmpty } from '@zinger/helpers'
import { useAsync } from '~/composables/use-async'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { usePlugins } from '~/composables/use-plugins'
import { useSyncedProp } from '~/composables/use-synced-prop'
import { OfficeGroupsApi } from '~/services/api/office-groups-api'
import { required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<OfficeGroupsApi.Form> & Readonly<{
  buttonText: string
  title: string
  dialog: boolean
}>

export default defineComponent<Props>({
  name: 'ZOfficeGroupForm',
  props: {
    ...getFormPropsOptions(),
    buttonText: { type: String, required: true },
    title: { type: String, required: true },
    dialog: { type: Boolean, required: true }
  },
  setup (props, context) {
    const { $api } = usePlugins()
    const parentGroup = useAsync(async () => {
      const id = props.value.parentOfficeGroupId
      if (id) {
        const { officeGroup } = await $api.officeGroups.get({ id })
        return officeGroup
      }
    })
    const useDialog = () => {
      const isActive = useSyncedProp('dialog', props, context)
      const close = () => {
        isActive.value = false
      }
      return { isActive, close }
    }
    return {
      ...useDialog(),
      ...useFormBindings(props, context),
      hasParentGroup: computed(() => nonEmpty(parentGroup.resolvedValue.value)),
      parentGroup: parentGroup.resolvedValue,
      rules: validationRules({
        name: { required, max: 100 }
      })
    }
  }
})
</script>
