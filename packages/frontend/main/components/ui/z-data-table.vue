<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div data-z-data-table :class="$style.root">
    <z-subheader v-if="title || $slots.title">
      <slot name="title">{{ title }}</slot>
    </z-subheader>
    <v-card v-if="isDesktop" class="py-4">
      <v-data-table
        v-model="syncedSelected"
        disable-pagination
        hide-default-footer
        loading-text="読み込み中..."
        data-desktop-table
        :class="tableClass"
        :dense="dense"
        :headers="options.headers"
        :item-class="itemClass"
        :items="items"
        :items-per-page="items.length"
        :loading="loading"
        :no-data-text="noDataText"
        :show-select="selectable"
        @click:row="onRowClicked"
      >
        <template v-if="hasForm" #top>
          <v-container>
            <slot name="form"></slot>
          </v-container>
        </template>
        <template v-for="name in itemSlots" #[name]="{ item }">
          <slot :item="item" :name="name"></slot>
        </template>
        <template v-if="isFooterLinkEnabled" #footer>
          <v-divider />
          <slot name="footer"></slot>
          <div v-if="hasFooterLink" class="text-right pa-2">
            <v-btn color="primary" nuxt text :to="options.footerLink">
              <span>{{ options.footerLinkText }}</span>
              <v-icon right>{{ $icons.forward }}</v-icon>
            </v-btn>
          </div>
        </template>
      </v-data-table>
      <template v-if="hasFab">
        <z-fab absolute bottom right :icon="fab.icon" :to="fab.to" />
      </template>
    </v-card>
    <template v-else>
      <v-expansion-panels v-if="hasForm" :class="$style.form">
        <v-expansion-panel>
          <v-expansion-panel-header>
            <slot name="form-name">
              <v-icon class="flex-grow-0 mr-1" dense>{{ $icons.filter }}</v-icon>
              検索条件を設定
            </slot>
          </v-expansion-panel-header>
          <v-expansion-panel-content>
            <slot name="form"></slot>
          </v-expansion-panel-content>
        </v-expansion-panel>
      </v-expansion-panels>
      <template v-if="itemsPerPageProps">
        <v-divider class="mx-1 mt-4" />
        <z-select-items-per-page
          class="offset-4 col-8 pt-1 pb-2"
          :current-value="itemsPerPageProps.currentValue"
          :option-values="itemsPerPageProps.optionValues"
          @change="itemsPerPageProps.onChange"
        />
      </template>
      <v-card v-for="(item, i) in items" :key="i" :class="$style.mobileRoot">
        <v-card-text :class="$style.text">
          <div v-for="(header, j) in options.headers" :key="j" :class="$style.mobileColumn">
            <div class="text-caption" :class="[header.class, $style.header]">
              {{ header.text }}
            </div>
            <div class="text-body-2 text--primary" :class="$style.value">
              <slot :item="item" :name="'item.' + header.value">{{ item[header.value] }}</slot>
            </div>
          </div>
        </v-card-text>
        <v-card-actions v-if="isItemLinkEnabled" :class="$style.actions" data-mobile-actions>
          <v-spacer />
          <v-btn color="primary" nuxt text :to="options.itemLink(item)">
            <span>{{ itemLinkText }}</span>
            <v-icon right>{{ $icons.forward }}</v-icon>
          </v-btn>
        </v-card-actions>
      </v-card>
      <div v-if="isFooterLinkEnabled" class="z-data-table__footer" :class="$style.footerMobile">
        <slot name="footer"></slot>
        <v-btn v-if="hasFooterLink" block color="primary" dark nuxt :to="options.footerLink">
          <span>{{ options.footerLinkText }}</span>
        </v-btn>
      </div>
      <z-fab v-if="hasFab" bottom fixed right :icon="fab.icon" :to="fab.to" />
    </template>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, useCssModule } from '@nuxtjs/composition-api'
import { nonEmpty } from '@zinger/helpers'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { useAuth } from '~/composables/use-auth'
import { usePlugins } from '~/composables/use-plugins'
import { useSyncedProp } from '~/composables/use-synced-prop'
import { ItemsPerPage } from '~/models/items-per-page'
import { ZDataTableOptions } from '~/models/z-data-table-options'
import { unref } from '~/support/reactive'

type Fab = {
  icon?: string
  to?: string
}

type Item = any

type ItemsPerPageProps = Readonly<{
  currentValue: ItemsPerPage
  optionValues?: ItemsPerPage[]
  onChange: (v: number) => void
}>

type Props = Readonly<{
  clickable: boolean
  dense: boolean
  itemClass: string | ((x: Item) => string)
  items: Item[]
  loading: boolean
  options: ZDataTableOptions<Item>
  selectable: boolean
  selected: any[]
  itemsPerPageProps: ItemsPerPageProps
}>

export default defineComponent<Props>({
  name: 'ZDataTable',
  props: {
    clickable: { type: Boolean, default: false },
    dense: { type: Boolean, default: false },
    itemClass: { type: [String, Function], default: undefined },
    items: { type: Array, required: true },
    loading: { type: Boolean, default: false },
    options: { type: Object, required: true },
    selectable: { type: Boolean, default: false },
    selected: { type: Array, default: () => [] },
    itemsPerPageProps: { type: Object, default: undefined }
  },
  setup (props, context) {
    const { slots } = context
    const { isAuthorized } = useAuth()
    const { $router, $vuetify } = usePlugins()
    const fab = computed<Fab>(() => props.options.fab ?? {})
    const hasFab = computed(() => !!fab.value.icon && !!fab.value.to)
    const hasFooterLink = computed(() => nonEmpty(props.options.footerLink) && nonEmpty(props.options.footerLinkText))
    const hasFooterLinkPermissions = computed(() => isAuthorized.value(props.options.footerLinkPermissions))
    const hasFooter = computed(() => hasFooterLink.value || nonEmpty(slots.footer))
    const hasForm = computed(() => nonEmpty(slots.form))
    const hasItemLink = computed(() => nonEmpty(props.options.itemLink))
    const hasItemLinkPermissions = computed(() => isAuthorized.value(props.options.itemLinkPermissions))
    const itemLinkText = computed(() => props.options.itemLinkText ?? '詳細を見る')
    const itemSlots = computed(() => Object.keys(slots).filter(name => name.startsWith('item.')))
    const isDesktop = computed(() => $vuetify.breakpoint.smAndUp ?? true)
    const noDataText = computed(() => unref(props.options.noDataText) ?? `該当する${props.options.content}は登録されていません。`)
    const isFooterLinkEnabled = computed(() => hasFooter.value && hasFooterLinkPermissions.value)
    const isItemLinkEnabled = computed(() => hasItemLink.value && hasItemLinkPermissions.value)
    const syncedSelected = useSyncedProp('selected', props, context)
    const style = useCssModule()
    const tableClass = computed(() => ({
      [style.table]: true,
      [style.clickable]: isItemLinkEnabled.value || props.clickable,
      [style.dense]: props.dense
    }))
    const title = computed(() => props.options.title ?? '')
    const onRowClicked = async (item: any) => {
      if (isItemLinkEnabled.value) {
        await catchErrorStack(() => $router.push(props.options.itemLink!(item)))
      } else if (props.clickable) {
        context.emit('click:row', item)
      }
    }
    return {
      fab,
      hasFab,
      hasFooterLink,
      hasForm,
      isDesktop,
      itemLinkText,
      isFooterLinkEnabled,
      isItemLinkEnabled,
      itemSlots,
      noDataText,
      syncedSelected,
      tableClass,
      title,
      onRowClicked
    }
  }
})
</script>

<style lang="scss" module>
@import '~vuetify/src/styles/settings/colors';

.root {
  margin-top: 16px;

  .table {
    &.dense {
      td {
        padding-top: 8px;
        padding-bottom: 8px;
      }
    }

    &.clickable {
      tbody > tr {
        cursor: pointer;
      }
    }

    tbody > tr:hover {
      background-color: #e7eef5 !important; // primary color (#335c81) lighten 90%
    }
  }

  :global {
    .v-expansion-panel-header {
      padding: 12px 16px;

      &--active {
        margin-bottom: -12px;
      }
    }

    .v-expansion-panel-content__wrap {
      padding: 0 16px 12px;
    }

    tr.inactive {
      background-color: map-get($grey, 'lighten-4');
      color: rgba(map-get($shades, 'black'), 0.6);
    }
  }
}

.footerMobile {
  margin-top: 16px;
  padding: 0;
}

.mobileRoot {
  padding: 4px 0;

  .text {
    padding: 0;
  }

  .actions {
    padding-top: 0;
    padding-bottom: 4px;
  }

  & + & {
    margin-top: 8px;
  }

  .form + & {
    margin-top: 16px;
  }
}

.mobileColumn {
  padding: 12px 16px;

  .header {
    line-height: 1rem;
  }

  .value {
    margin-top: 2px;
  }
}
</style>
