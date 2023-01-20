<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { defineComponent, useCssModule } from '@nuxtjs/composition-api'
import { renderDataCard } from '~/components/ui/z-data-card-utils'

export default defineComponent({
  name: 'ZDataCard',
  props: {
    title: { type: String, default: '' }
  },
  setup: (props, context) => {
    const { dataCard } = useCssModule()
    return () => renderDataCard(dataCard, context, props)
  }
})
</script>

<style lang="scss" module>
.dataCard {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  margin-top: 16px;
}
</style>
