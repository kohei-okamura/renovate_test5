/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare type Google = {
  /* global google */
  maps: typeof google.maps
}

declare type IconDefinition = string | google.maps.Icon | google.maps.Symbol

declare module '@google/markerclusterer' {
  /* global MarkerClusterer */
  export = MarkerClusterer
}
