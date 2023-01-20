<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div class="z-data-table-footer d-sm-flex justify-sm-end pt-sm-4">
    <div v-if="pagination.pages" :class="$style.wrapper">
      <v-pagination
        color="primary"
        :length="pagination.pages || 0"
        :total-visible="$vuetify.breakpoint.smAndUp ? 7 : 5"
        :value="+pagination.page || 1"
        @input="onInputPage"
      />
    </div>
    <z-select-items-per-page
      class="d-none d-sm-flex px-6"
      :current-value="pagination.itemsPerPage"
      :option-values="itemsPerPageOptionValues"
      @change="onChangeItemsPerPage"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { ItemsPerPage } from '~/models/items-per-page'
import { Pagination } from '~/models/pagination'

type Props = {
  pagination: Pagination
  itemsPerPageOptionValues?: ItemsPerPage[]
}

export default defineComponent<Props>({
  name: 'ZDataTableFooter',
  props: {
    pagination: { type: Object, required: true },
    itemsPerPageOptionValues: { type: Array, default: undefined }
  },
  setup (_props, context) {
    const onInputPage = (page: number) => context.emit('update:page', page)
    const onChangeItemsPerPage = (itemsPerPage: number) => context.emit('update:items-per-page', itemsPerPage)
    return {
      onInputPage,
      onChangeItemsPerPage
    }
  }
})
</script>

<style lang="scss" module>
@import '~vuetify/src/styles/styles.sass';

.wrapper {
  min-width: 100%;
}

@media #{map-get($display-breakpoints, 'sm-and-up')} {
  .wrapper {
    min-width: 408px;

    :global {
      .v-pagination {
        justify-content: flex-end;
      }
    }
  }
}

;
</style>
