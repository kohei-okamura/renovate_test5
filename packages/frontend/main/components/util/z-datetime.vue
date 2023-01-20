<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { DatetimeComponentPropDefs, setupDatetimeComponent } from '~/components/tools/setup-datetime-component'
import { ISO_DATETIME_FORMAT, READABLE_DATETIME_FORMAT } from '~/models/date'

export default defineComponent({
  name: 'ZDatetime',
  props: DatetimeComponentPropDefs,
  setup: setupDatetimeComponent({
    format: ISO_DATETIME_FORMAT,
    displayFormat: READABLE_DATETIME_FORMAT
  })
})
</script>
