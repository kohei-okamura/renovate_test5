<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-form-card title="一括登録（アップロード）">
        <z-form-card-item-set no-icon>
          <p>勤務シフトを記入したエクセルファイルを選択し《アップロード》ボタンを押してください。</p>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.xlsx">
          <z-form-card-item v-slot="{ errors }" data-file vid="file" :rules="rules.file">
            <z-file-input
              v-model="form.file"
              accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
              label="ファイル *"
              placeholder="ファイルを選択してください"
              show-size
              :clearable="!progress"
              :disabled="progress"
              :error-messages="errors"
              :prepend-icon="null"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-action-button text="アップロード" :disabled="progress" :icon="$icons.upload" :loading="progress" />
      </z-form-card>
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { defineComponent, toRefs, watch } from '@nuxtjs/composition-api'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { ShiftsApi } from '~/services/api/shifts-api'
import { required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<ShiftsApi.ImportForm>

export default defineComponent<Props>({
  name: 'ZShiftUploadForm',
  props: {
    ...getFormPropsOptions()
  },
  setup (props: Props, context) {
    const { form, observer, submit } = useFormBindings(props, context)
    const rules = validationRules({
      file: { required }
    })

    const propRefs = toRefs(props)
    const suppressValidationOnReset = (file: File | undefined) => {
      file === undefined && observer.value?.reset()
    }
    const update = (file: File | undefined) => {
      form.file = file
      suppressValidationOnReset(file)
    }
    watch(() => form.file, file => context.emit('input', { file }))
    watch(propRefs.value, value => update(value.file))

    return {
      form,
      observer,
      rules,
      submit
    }
  }
})
</script>
