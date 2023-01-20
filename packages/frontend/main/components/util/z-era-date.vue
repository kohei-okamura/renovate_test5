<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import {
  DatetimeComponentPropDefs,
  DatetimeComponentProps,
  setupDatetimeComponent
} from '~/components/tools/setup-datetime-component'
import { eraDate } from '~/composables/era-date'
import { ISO_DATE_FORMAT } from '~/models/date'

type Props = DatetimeComponentProps & {
  short: boolean
}

export default defineComponent({
  name: 'ZEraDate',
  props: {
    ...DatetimeComponentPropDefs,
    short: { type: Boolean, default: false }
  },
  setup: setupDatetimeComponent<Props>({
    format: ISO_DATE_FORMAT,
    displayFormat: (value, props) => eraDate(value, props.short ? 'short' : 'long')
  })
})
</script>
