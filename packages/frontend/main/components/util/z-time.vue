<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { DatetimeComponentPropDefs, setupDatetimeComponent } from '~/components/tools/setup-datetime-component'
import { ISO_TIME_FORMAT, READABLE_TIME_FORMAT } from '~/models/date'

export default defineComponent({
  name: 'ZTime',
  props: DatetimeComponentPropDefs,
  setup: setupDatetimeComponent({
    format: ISO_TIME_FORMAT,
    displayFormat: READABLE_TIME_FORMAT
  })
})
</script>
