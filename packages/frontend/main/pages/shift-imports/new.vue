<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-shift-upload-form v-model="uploadValue" :errors="errors" :progress="progress" @submit="upload" />
    <z-shift-download-form :errors="errors" :progress="progress" :value="downloadValue" @submit="download" />
    <v-card
      v-if="registrationErrors"
      ref="registrationErrorsElementRef"
      class="mt-6"
      :class="$style.errorCardWrapper"
      data-registration-errors
    >
      <v-card-title class="flex-nowrap">
        <v-icon color="error">{{ $icons.alert }}</v-icon>
        <span class="ml-1 text-subtitle-2 text-md-subtitle-1">アップロードしたファイルに下記のエラーがありました</span>
      </v-card-title>
      <v-card :elevation="0">
        <v-card-text
          v-for="(error, i) in registrationErrors"
          :key="i"
          class="py-3"
          data-error-text
        >
          {{ error }}
        </v-card-text>
      </v-card>
      <template v-if="!showAllRegistrationErrors">
        <div :class="$style.errorCardOverlay"></div>
        <v-btn
          :class="$style.errorSeeMore"
          color="#f1f6fa"
          data-see-more-button
          depressed
          @click="showMoreRegistrationErrors"
        >
          もっと見る
        </v-btn>
      </template>
    </v-card>
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent, onMounted, reactive, toRefs } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { shiftImportsStoreKey } from '~/composables/stores/use-shift-imports-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { useJobWithNotification } from '~/composables/use-job-with-notification'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { ShiftsApi } from '~/services/api/shifts-api'
import { componentRef } from '~/support/reactive'
import { ValidationObserverInstance } from '~/support/validation/types'

const INITIAL_DISPLAY_NUM_OF_ERRORS = 3

export default defineComponent({
  name: 'ShiftImportsNewPage',
  middleware: [auth(Permission.importShifts)],
  setup () {
    const { $api, $download, $form } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const { execute } = useJobWithNotification()

    const shiftImportsStore = useInjected(shiftImportsStoreKey)
    const shiftImportsState = shiftImportsStore.state

    const data = reactive({
      downloadValue: {
        officeId: undefined,
        range: {
          start: undefined,
          end: undefined
        },
        isCopy: false,
        source: {
          start: undefined,
          end: undefined
        }
      },
      uploadValue: {
        file: undefined
      },
      showAllRegistrationErrors: false
    })

    const registrationErrorsElementRef = componentRef()
    onMounted(() => {
      // 一括登録エラーが表示されている時は、遷移時にファーストビューに入るようにスクロールする
      if (registrationErrorsElementRef.value) {
        registrationErrorsElementRef.value?.$el.scrollIntoView()
      }
    })

    // FYI: 値の変更監視は子要素（正確には子要素で使用している useFormBindings）でやっている（分かりにくいので補足）
    $form.preventUnexpectedUnload()

    const download = (form: ShiftsApi.CreateTemplateForm, observer?: ValidationObserverInstance) => withAxios(() => {
      return $form.submit(() => execute({
        notificationProps: {
          linkToOnFailure: '/shift-imports/new',
          text: {
            progress: '勤務シフト一括登録用ファイルのダウンロードを準備中です...',
            success: '勤務シフト一括登録用ファイルのダウンロードを開始します',
            failure: '勤務シフト一括登録用ファイルのダウンロードに失敗しました'
          }
        },
        process: () => $api.shifts.createTemplate({ form }).then(res => {
          // ジョブが正常に開始されたら変更の監視をリセットする
          observer?.reset()
          return res
        }),
        success: job => {
          $download.uri(job.data.uri, job.data.filename)
        }
      }))
    })
    const upload = (form: ShiftsApi.ImportForm, observer?: ValidationObserverInstance) => withAxios(() => {
      return $form.submit(() => execute({
        notificationProps: {
          featureName: '勤務シフト一括登録',
          linkToOnFailure: '/shift-imports/new'
        },
        process: () => {
          shiftImportsStore.resetState()
          return $api.shifts.import({ form }).then(res => {
            // ジョブが正常に開始されたら変更の監視をリセットする
            observer?.reset()
            return res
          })
        },
        success: () => {
          data.uploadValue = { file: undefined }
        },
        failure: errors => {
          shiftImportsStore.updateErrors(errors)
          data.showAllRegistrationErrors = errors.length <= INITIAL_DISPLAY_NUM_OF_ERRORS
        }
      }))
    })

    return {
      ...toRefs(data),
      ...useBreadcrumbs('shifts.imports.new'),
      errors,
      progress,
      registrationErrorsElementRef,
      download,
      upload,
      registrationErrors: computed(() => {
        if (data.showAllRegistrationErrors) {
          return shiftImportsState.getErrors.value()
        }
        return shiftImportsState.getErrors.value(INITIAL_DISPLAY_NUM_OF_ERRORS)
      }),
      showMoreRegistrationErrors: () => {
        data.showAllRegistrationErrors = true
      }
    }
  },
  head: () => ({
    title: '勤務シフトを一括登録'
  })
})
</script>

<style lang="scss" module>
.errorCardWrapper {
  position: relative;
}

.errorCardOverlay {
  background: linear-gradient(rgba(255, 255, 255, 0) 0%, white 56%, white 100%);
  bottom: 0;
  height: 40px;
  position: absolute;
  width: 100%;
}

.errorSeeMore {
  bottom: -20px;
  color: rgba(0, 0, 0, 0.6);
  height: 40px;
  margin: 0 auto;
  left: 0;
  position: absolute;
  right: 0;
  background: linear-gradient(white 32%, rgba(241, 246, 250, 0));
}
</style>
