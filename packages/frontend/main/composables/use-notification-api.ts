/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, ref } from '@nuxtjs/composition-api'
import { match } from 'ts-pattern'
import { usePlugins } from '~/composables/use-plugins'

export const useNotificationApi = () => {
  const { $snackbar } = usePlugins()
  const notificationRef = ref<Notification>()
  const notificationEnabled = 'Notification' in window
  const currentPermission = ref<NotificationPermission>(notificationEnabled ? Notification.permission : 'default')

  const postProcess = (permission: NotificationPermission) => {
    currentPermission.value = permission
    if (permission === 'denied') {
      $snackbar.warning('デスクトップ通知を無効にしました')
    } else if (permission === 'granted') {
      $snackbar.info('デスクトップ通知を有効にしました')
    }
  }

  const askPermission = async () => {
    if (notificationEnabled) {
      // Safari が Promise に対応していないのでコールバックで処理する
      const permission = await Notification.requestPermission()
      postProcess(permission)
    } else {
      $snackbar.error('お使いのブラウザはデスクトップ通知に対応していません。')
    }
  }

  const isDenied = computed(() => currentPermission.value === 'denied')
  const isGranted = computed(() => currentPermission.value === 'granted')
  const isAlreadyConfirmed = computed(() => isDenied.value || isGranted.value)
  const statusText = computed(() => {
    return match(currentPermission.value)
      .with('default', () => 'デスクトップ通知を有効にする')
      .with('denied', () => 'デスクトップ通知は無効です')
      .with('granted', () => 'デスクトップ通知は有効です')
      .exhaustive()
  })

  const sendNotification = (options?: NotificationOptions & { title?: string }) => {
    // 画面が表示されている時は何もしない（）
    if (document.visibilityState === 'visible') {
      return
    }
    const { protocol, hostname } = window.location
    const { title, ...opts } = {
      title: 'careid',
      icon: `${protocol}//${hostname}/icon.png`,
      ...options
    }
    notificationRef.value = new Notification(title, opts)
  }

  const closeNotification = () => notificationRef.value?.close()

  // 画面が表示されたら通知を消す
  document.addEventListener('visibilitychange', () => {
    document.visibilityState === 'visible' && closeNotification()
  })

  return {
    askPermission,
    closeNotification,
    isAlreadyConfirmed,
    isDenied,
    isGranted,
    statusText,
    sendNotification
  }
}
