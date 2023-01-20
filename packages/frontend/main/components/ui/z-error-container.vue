<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { defineDivComponent } from '~/components/tools/define-div-component'

export default defineDivComponent({
  name: 'ZErrorContainer',
  classMap: { 'z-error-container py-1 px-4 rounded': true },
  useCssModule: true
})
</script>

<style lang="scss" module>
.root {
  border: 1px solid #e64a19;
}
</style>
