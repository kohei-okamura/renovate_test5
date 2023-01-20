<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template functional>
  <div data-z-form-action-button :class="[$style.root, { [$style.fixed]: props.fixed }]">
    <v-btn
      color="accent"
      fab
      large
      light
      rounded
      type="submit"
      :icon="false"
      :text="false"
      v-bind="data.attrs"
    >
      <v-icon left>{{ props.icon }}</v-icon>
      <span>{{ props.text }}</span>
    </v-btn>
  </div>
</template>

<style lang="scss" module>
.root {
  margin: 24px auto 0;
  text-align: center;

  :global {
    .v-btn.v-btn--rounded.v-size--large {
      height: 48px;
      min-width: 160px;
      padding: 0 20px;
      width: auto;
    }

    .v-btn .v-icon {
      font-size: 24px;
      height: 24px;
      margin: 0 12px 0 0;
      width: 24px;
    }
  }

  :global(.z-app--mobile) &, &.fixed {
    margin: 0;

    :global(.v-btn) {
      bottom: 16px;
      left: 50%;
      position: fixed;
      transform: translate3d(-50%, 0, 0);
    }
  }
}
</style>
