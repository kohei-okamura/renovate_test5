<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { VTextarea } from 'vuetify/lib'
import { colors } from '~/colors'
import { extendVuetifyComponent } from '~/components/tools/extend-vuetify-component'

export default extendVuetifyComponent(VTextarea, 'ZTextarea', {
  autoGrow: { default: true },
  // フォームの背景色を変更、枠線についてはスタイルシート変数(variables.scss)を参照
  backgroundColor: { default: colors.textField.background },
  outlined: { default: true },
  rows: { default: 3 }
})
</script>
