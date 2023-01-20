<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div data-z-notification class="px-3 py-2" :class="$style.root">
    <div class="d-inline-flex">
      <div class="mr-3">
        <v-progress-circular
          v-if="inProgress"
          color="blue-grey"
          data-in-progress-icon
          indeterminate
          :size="26"
          :width="3"
        />
        <v-icon v-else-if="success" color="success" data-success-icon>{{ $icons.completed }}</v-icon>
        <v-icon v-else-if="failure" color="error" data-failure-icon>{{ $icons.alert }}</v-icon>
      </div>
      <div>
        <span class="font-weight-bold">{{ headingText }}</span>
        <span>-</span>
        <span class="body-1">{{ message }}</span>
      </div>
      <v-btn v-if="completion" color="transparent" data-close-button icon small :height="24" @click="clickClose">
        <v-icon color="primary">{{ $icons.close }}</v-icon>
      </v-btn>
    </div>
    <div
      v-if="success && linkToOnSuccess"
      class="d-inline-flex justify-end"
      :class="$style.container"
      data-detail-button-container-on-success
    >
      <v-btn color="primary" data-detail-button-on-success text @click="clickDetail(linkToOnSuccess)">詳細を確認</v-btn>
    </div>
    <div
      v-else-if="failure && linkToOnFailure"
      class="d-inline-flex justify-end"
      :class="$style.container"
      data-detail-button-container-on-failure
    >
      <v-btn color="primary" data-detail-button-on-failure text @click="clickDetail(linkToOnFailure)">詳細を確認</v-btn>
    </div>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { ZNotification as ZNotificationType } from '~/composables/stores/use-notification-store'
import { usePlugins } from '~/composables/use-plugins'

type Props = ZNotificationType

export default defineComponent<Props>({
  name: 'ZNotification',
  props: {
    id: { type: [String, Number], required: true },
    status: {
      type: Number,
      default: JobStatus.waiting,
      validator: value => JobStatus.validate(value)
    },
    featureName: { type: String, default: undefined },
    linkToOnFailure: { type: String, default: undefined },
    linkToOnSuccess: { type: String, default: undefined },
    text: { type: String, default: undefined }
  },
  setup (props, context) {
    const { $router } = usePlugins()
    const waiting = computed(() => props.status === JobStatus.waiting)
    const inProgress = computed(() => props.status === JobStatus.inProgress)
    const success = computed(() => props.status === JobStatus.success)
    const failure = computed(() => props.status === JobStatus.failure)
    const headingText = computed(() => waiting.value ? '待機中' : inProgress.value ? '処理中' : '完了')
    const message = computed(() => {
      if (props.text) {
        return props.text
      }
      if (inProgress.value) {
        return `${props.featureName ?? 'タスク'}を処理しています`
      }
      if (success.value) {
        return `${props.featureName ?? 'タスク'}の処理が正常に終了しました`
      }
      if (failure.value) {
        return `${props.featureName ?? 'タスク'}の処理にエラーがありました`
      }
      return '待機中'
    })
    return {
      headingText,
      message,
      inProgress,
      success,
      failure,
      completion: computed(() => success.value || failure.value),
      clickClose: () => {
        context.emit('click', props.id)
      },
      clickDetail: (linkTo: string) => {
        $router.push(linkTo)
        context.emit('click', props.id)
      }
    }
  }
})
</script>

<style lang="scss" module>
.root {
  background-color: inherit;
  min-height: 60px;
  width: 100%;
}

.container {
  width: 100%;
}
</style>
