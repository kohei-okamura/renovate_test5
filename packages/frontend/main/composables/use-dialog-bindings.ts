/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive, Ref } from '@nuxtjs/composition-api'
import { usePlugins } from '~/composables/use-plugins'

type DialogBindings = {
  dialog: Ref<boolean>
  disableRouterBack: () => void
  openDialog: () => void
  closeDialog: () => void
  toggleDialog: (value: boolean) => void
}

export function useDialogBindings (): DialogBindings {
  const { $router } = usePlugins()
  const data = reactive({
    dialog: false,
    isRouterBackEnabled: true,
    routeLeaving: false
  })
  const dialog = computed({
    get: () => data.dialog,
    set: (value: boolean) => {
      if (!value && !data.routeLeaving && data.isRouterBackEnabled) {
        $router.back()
      }
      data.dialog = value
    }
  })
  const disableRouterBack = () => {
    data.isRouterBackEnabled = false
  }
  const toggleDialog = (value: boolean) => {
    dialog.value = value
  }
  const openDialog = () => toggleDialog(true)
  const closeDialog = () => toggleDialog(false)
  // TODO: このやり方だと動かないので別の方法を考える……
  // beforeRouteLeave((_to, _from, next) => {
  //   state.routeLeaving = true
  //   closeDialog()
  //   setTimeout(next, 300)
  // })
  return {
    dialog,
    disableRouterBack,
    openDialog,
    closeDialog,
    toggleDialog
  }
}
