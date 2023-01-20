<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { defineDivComponent } from '~/components/tools/define-div-component'

export default defineDivComponent({
  name: 'ZCheckboxContainer',
  classMap: { 'z-checkbox-container': true },
  useCssModule: true
})
</script>

<style lang="scss" module>
.root {
  flex-shrink: 1;
  margin: 0 0 0 8px;
}
</style>
