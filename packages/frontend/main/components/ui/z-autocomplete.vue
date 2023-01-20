<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { VAutocomplete } from 'vuetify/lib'
import { colors } from '~/colors'
import { extendVuetifyComponent } from '~/components/tools/extend-vuetify-component'

export default extendVuetifyComponent(VAutocomplete, 'ZAutocomplete', {
  // フォームの背景色を変更、枠線についてはスタイルシート変数(variables.scss)を参照
  backgroundColor: { default: colors.textField.background },
  dense: { default: true },
  outlined: { default: true }
})
</script>
