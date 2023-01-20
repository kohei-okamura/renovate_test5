<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-main>
    <v-container class="pa-0" fill-height fluid>
      <v-layout id="map" fill-height fluid />
    </v-container>
    <v-navigation-drawer app fixed permanent right width="200">
      <v-list dense>
        <v-subheader>スタッフ</v-subheader>
        <v-list-item v-for="(staff, i) in staffs" :key="i" @click="onClickWorker(staff)">
          <v-list-item-icon>
            <v-icon>{{ $icons.staff }}</v-icon>
          </v-list-item-icon>
          <v-list-item-content>
            <v-list-item-title>{{ staff.name.familyName }} {{ staff.name.givenName }}</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>
  </v-main>
</template>

<script lang="ts">
import MarkerClusterer from '@google/markerclusterer'
import { defineComponent, onMounted } from '@nuxtjs/composition-api'
import { pick } from '@zinger/helpers'
import MaterialPersonIconPath from 'material-design-icons-svg/paths/account.json'
import colors from 'vuetify/es5/util/colors'
import { useStaffsStore } from '~/composables/stores/use-staffs-store'
import { useBackgroundLoader } from '~/composables/use-background-loader'
import { useElement } from '~/composables/use-element'
import { usePlugins } from '~/composables/use-plugins'
import { Staff } from '~/models/staff'

const getMapOptions = (): google.maps.MapOptions => ({
  center: {
    lat: 35.6968376,
    lng: 139.6842756
  },
  fullscreenControl: false,
  mapTypeControl: false,
  scaleControl: false,
  streetViewControl: false,
  zoom: 16,
  styles: [
    {
      featureType: 'administrative',
      elementType: 'labels.text.fill',
      stylers: [{ color: '#6195a0' }]
    },
    {
      featureType: 'administrative.province',
      elementType: 'geometry.stroke',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'landscape',
      elementType: 'geometry',
      stylers: [{ lightness: 0 }, { saturation: 0 }, { color: '#f5f5f2' }, { gamma: 1 }]
    },
    {
      featureType: 'landscape.man_made',
      elementType: 'all',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'landscape.natural.terrain',
      elementType: 'all',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'poi',
      elementType: 'all',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'poi.park',
      elementType: 'geometry.fill',
      stylers: [{ color: '#bae5ce' }, { visibility: 'on' }]
    },
    {
      featureType: 'road',
      elementType: 'all',
      stylers: [{ saturation: -100 }, { lightness: 45 }, { visibility: 'simplified' }]
    },
    {
      featureType: 'road.highway',
      elementType: 'all',
      stylers: [{ visibility: 'simplified' }]
    },
    {
      featureType: 'road.highway',
      elementType: 'geometry.fill',
      stylers: [{ color: '#fac9a9' }, { visibility: 'simplified' }]
    },
    {
      featureType: 'road.highway',
      elementType: 'labels.text',
      stylers: [{ color: '#4e4e4e' }]
    },
    {
      featureType: 'road.arterial',
      elementType: 'labels.text.fill',
      stylers: [{ color: '#787878' }]
    },
    {
      featureType: 'road.arterial',
      elementType: 'labels.icon',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'transit',
      elementType: 'all',
      stylers: [{ visibility: 'simplified' }]
    },
    {
      featureType: 'transit.station.airport',
      elementType: 'labels.icon',
      stylers: [{ hue: '#0a00ff' }, { saturation: -77 }, { gamma: 0.57 }, { lightness: 0 }]
    },
    {
      featureType: 'transit.station.rail',
      elementType: 'labels.text.fill',
      stylers: [{ color: '#43321e' }]
    },
    {
      featureType: 'transit.station.rail',
      elementType: 'labels.icon',
      stylers: [{ hue: '#ff6c00' }, { lightness: 4 }, { gamma: 0.75 }, { saturation: -68 }]
    },
    {
      featureType: 'water',
      elementType: 'all',
      stylers: [{ color: '#eaf6f8' }, { visibility: 'on' }]
    },
    {
      featureType: 'water',
      elementType: 'geometry.fill',
      stylers: [{ color: '#c7eced' }]
    },
    {
      featureType: 'water',
      elementType: 'labels.text.fill',
      stylers: [{ lightness: -49 }, { saturation: -53 }, { gamma: 0.79 }]
    }
  ]
})

export default defineComponent({
  name: 'MapPage',
  setup () {
    const { $google } = usePlugins()
    const $element = useElement()
    const onClickWorker = (staff: Staff) => window.console.log(staff)
    const useStaffsState = () => {
      const store = useStaffsStore()
      useBackgroundLoader(() => store.getIndex({ all: true }))
      return pick(store.state, ['staffs'])
    }
    const { staffs } = useStaffsState()
    onMounted(async () => {
      const google = await $google()
      const mapOptions = getMapOptions()
      const createMarkers = (icon: IconDefinition): google.maps.Marker[] => {
        return staffs.value.map(x => new google.maps.Marker({
          icon,
          label: x.name.displayName,
          position: {
            lat: x.location.lat,
            lng: x.location.lng
          }
        }))
      }
      const createStaffIcon = (): IconDefinition => ({
        anchor: new google.maps.Point(11, 11),
        path: MaterialPersonIconPath,
        fillColor: colors.blue.base,
        fillOpacity: 1,
        scale: 1.454545454545,
        strokeWeight: 0
      })
      try {
        const map = new google.maps.Map($element().querySelector('#map')!, mapOptions)
        const markers = createMarkers(createStaffIcon())
        const clustererOptions: MarkerClustererOptions = {
          imagePath: 'https://googlemaps.github.io/js-marker-clusterer/images/m',
          maxZoom: 14
        }
        return new MarkerClusterer(map, markers, clustererOptions)
      } catch (error) {
        // TODO: エラー時の処理を改善する
        window.console.log(error)
      }
    })
    return {
      staffs,
      onClickWorker
    }
  }
})
</script>
