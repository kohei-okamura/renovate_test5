<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <transition name="fade">
    <div v-if="hasItems" data-z-notifications :class="$style.root">
      <div :class="$style.content">
        <div class="d-inline-flex justify-end" :class="$style.header">
          <v-btn
            color="error"
            data-delete-all-button
            text
            @click="$emit('click:delete-all', $event)"
          >
            すべて消す
          </v-btn>
        </div>
        <z-notification
          v-for="item in items"
          :key="item.id"
          data-notification
          v-bind="item"
          @click="$emit('click:delete', $event)"
        />
      </div>
    </div>
  </transition>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { ZNotification as ZNotificationType } from '~/composables/stores/use-notification-store'

type Props = {
  items: ZNotificationType[]
}

export default defineComponent<Props>({
  name: 'ZNotifications',
  props: {
    items: {
      type: Array,
      default: () => []
    }
  },
  setup (props) {
    return {
      hasItems: computed(() => props && props.items.length >= 1)
    }
  }
})
</script>

<style lang="scss" module>
@import '~vuetify/src/styles/styles';

.root {
  background-color: transparent;
  max-height: max(40vh, 430px);
  position: fixed;
  right: 2px;
  top: 50px;
  width: clamp(200px, 80%, 320px);
  z-index: 10;

  &::before {
    border-bottom: 8px solid #fff;
    border-left: 12px solid transparent;
    border-right: 12px solid transparent;
    content: "";
    right: 161px;
    position: absolute;
    top: -7px;
    z-index: 2;
  }

  &::after {
    border-bottom: 10px solid lightgray;
    border-left: 14px solid transparent;
    border-right: 14px solid transparent;
    content: "";
    right: 159px;
    position: absolute;
    top: -9px;
    z-index: 1;
  }
}

.content {
  background-color: white;
  border-radius: 4px;
  border: solid 1px lightgray;
  overflow-y: auto;
  position: absolute;
  top: 0;
  width: 100%;

  > *:not(.header) {
    border-bottom: solid 1px rgba(211, 211, 211, 0.6);
  }
}

.header {
  width: 100%;
}

@media #{map-get($display-breakpoints, 'sm-and-down')} {
  .root {
    width: 50vw;
    right: 0;
    top: 42px;

    &::before {
      border-bottom: 6px solid #fff;
      border-left: 10px solid transparent;
      border-right: 10px solid transparent;
      right: 92px;
      top: 0px;
    }

    &::after {
      border-bottom: 8px solid lightgray;
      border-left: 12px solid transparent;
      border-right: 12px solid transparent;
      right: 90px;
      top: -2px;
    }
  }
  .content {
    border-top: none;
    border-radius: 0;
    border-right: none;
    top: 13px;
  }
}

@media #{map-get($display-breakpoints, 'xs-only')} {
  .root {
    // 100vh - z-notifications-root.top
    height: calc(100vh - 42px);
    max-height: none;
    width: 100vw;
  }
  .content {
    border: none;
    // 100% - z-notifications-content.top
    height: calc(100% - 13px);
  }
}
</style>
