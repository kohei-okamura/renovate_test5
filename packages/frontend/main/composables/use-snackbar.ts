/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive, toRefs, watch } from '@nuxtjs/composition-api'
import { usePlugins } from '~/composables/use-plugins'

export const useSnackbar = () => {
  const { $snackbar } = usePlugins()
  const data = reactive({
    snackbar: false
  })
  watch($snackbar.config, config => {
    data.snackbar = config.text !== ''
  })
  return {
    ...toRefs(data),
    config: $snackbar.config
  }
}
