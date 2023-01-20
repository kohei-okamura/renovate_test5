<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { DatetimeComponentPropDefs, setupDatetimeComponent } from '~/components/tools/setup-datetime-component'
import { ISO_DATE_FORMAT, READABLE_DATE_FORMAT } from '~/models/date'

export default defineComponent({
  name: 'ZDate',
  props: DatetimeComponentPropDefs,
  setup: setupDatetimeComponent({
    format: ISO_DATE_FORMAT,
    displayFormat: READABLE_DATE_FORMAT
  })
})
</script>
