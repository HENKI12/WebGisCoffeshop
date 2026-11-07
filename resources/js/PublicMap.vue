<template>
  <main class="viewer-shell coffee-viewer">
    <header class="viewer-header">
      <a class="viewer-brand" href="/" aria-label="KopiPontianak"><span class="viewer-mark">☕</span><span><strong>Kopi<span>Pontianak</span></strong><small>PETA KEDAI KOPI & KAFE</small></span></a>
      <form class="viewer-search" @submit.prevent="searchMap"><span class="viewer-search-icon">⌕</span><input v-model="query" placeholder="Cari nama kedai, alamat, atau kelurahan..." aria-label="Cari kedai kopi" /><button type="submit">Cari</button></form>
      <a class="viewer-login" href="/login"><span>Masuk</span><b>Kelola tempat</b></a>
    </header>
    <section class="viewer-body">
      <aside class="viewer-panel">
        <div class="viewer-panel-heading"><div><span class="viewer-eyebrow">PETA KULINER</span><h1>Kedai kopi Pontianak</h1></div><span class="viewer-total">{{ filteredShops.length }}</span></div>
        <p class="viewer-description">Temukan kafe dan warung kopi di sekitar Kota Pontianak.</p>
        <label class="viewer-filter"><span>⌕</span><input v-model="query" placeholder="Nama atau kelurahan" /></label>
        <div class="viewer-section-title"><strong>Tempat terdaftar</strong><span>{{ filteredShops.length }} tempat</span></div>
        <div class="viewer-results">
          <button v-for="shop in filteredShops" :key="shop.id" class="viewer-result" :class="{ active: String(selectedId) === String(shop.id) }" @click="focusShop(shop)">
            <span class="coffee-result-icon">☕</span><span class="result-copy"><strong>{{ shop.properties.name }}</strong><small>{{ shop.properties.ward }} · {{ shop.properties.business_type }}</small></span><span class="result-arrow">›</span>
          </button>
          <div v-if="!filteredShops.length" class="viewer-empty"><span>⌕</span><strong>{{ shops.length ? 'Kedai tidak ditemukan' : 'Belum ada kedai' }}</strong><small>Coba kata kunci yang berbeda.</small></div>
        </div>
        <div class="viewer-panel-footer"><span class="coffee-legend"></span><span>Kafe / warung kopi</span><button class="layer-toggle" :class="{ off: !shopsVisible }" @click="toggleShops" :aria-label="shopsVisible ? 'Sembunyikan kedai' : 'Tampilkan kedai'"><i></i></button></div>
      </aside>
      <div class="viewer-map-wrap"><div id="public-map" class="viewer-map"></div>
        <div class="map-search-hint" v-if="!shops.length && !loading"><strong>Data kedai belum tersedia</strong><span>Tambahkan tempat melalui halaman pengelola.</span></div>
        <article v-if="selected" class="viewer-popup coffee-popup">
          <div class="popup-location"><span>☕ <b>Pontianak</b></span><small>{{ coordinateText }}</small><button class="viewer-popup-close" @click="closePopup" aria-label="Tutup">×</button></div>
          <div class="popup-title"><h2>{{ selected.properties.name }}</h2><div><button @click="expanded = !expanded" :aria-label="expanded ? 'Ciutkan informasi' : 'Perluas informasi'">{{ expanded ? '⌃' : '⌄' }}</button><button @click="shareShop" aria-label="Bagikan lokasi">♧</button></div></div>
          <div v-if="expanded" class="popup-details">
            <div><span>JENIS TEMPAT</span><b>{{ selected.properties.business_type || 'Kafe / kedai kopi' }}</b></div>
            <div><span>ALAMAT</span><b>{{ selected.properties.address || 'Alamat belum tersedia' }}</b></div>
            <div><span>KELURAHAN</span><b>{{ selected.properties.ward || '—' }}</b></div>
            <div><span>NIB USAHA</span><b>{{ selected.properties.license_number || '—' }}</b></div>
            <div><span>JAM BUKA</span><b>{{ selected.properties.opening_hours || 'Belum tersedia' }}</b></div>
            <div><span>AKURASI TITIK</span><b>{{ selected.properties.location_accuracy }}</b></div>
          </div>
          <a class="popup-analysis" :href="mapsUrl" target="_blank" rel="noopener">Petunjuk arah di Google Maps</a>
          <div class="popup-actions"><a :href="mapsUrl" target="_blank" rel="noopener">↗ Google Maps</a><button @click="feedbackMessage = 'Koreksi data dapat dikirim kepada pengelola.'">⚑ Koreksi data</button><button :class="{ favorited }" @click="favorited = !favorited">{{ favorited ? '★ Favorit' : '☆ Favorit' }}</button></div>
          <p v-if="feedbackMessage" class="popup-feedback">{{ feedbackMessage }}</p>
        </article>
        <div class="viewer-map-tools"><button @click="zoomIn" aria-label="Perbesar">+</button><button @click="zoomOut" aria-label="Perkecil">−</button><button @click="resetExtent" aria-label="Tampilkan seluruh kedai">⌖</button></div>
        <div class="viewer-attribution">© OpenStreetMap contributors</div><div v-if="loading" class="viewer-loading">Memuat kedai kopi…</div>
      </div>
    </section>
    <footer class="viewer-footer"><span>Sumber usaha: Satu Data Kota Pontianak (2021).</span><span>Pin awal diperkirakan dari kelurahan; lokasi tepat dapat diperbarui pengelola.</span><a href="/login">Kelola data <b>↗</b></a></footer>
  </main>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue'
import MapView from 'ol/Map.js'
import View from 'ol/View.js'
import TileLayer from 'ol/layer/Tile.js'
import VectorLayer from 'ol/layer/Vector.js'
import OSM from 'ol/source/OSM.js'
import VectorSource from 'ol/source/Vector.js'
import GeoJSON from 'ol/format/GeoJSON.js'
import { Fill, Stroke, Style, Circle as CircleStyle } from 'ol/style.js'
import { fromLonLat, toLonLat } from 'ol/proj.js'
import { defaults as defaultControls } from 'ol/control/defaults.js'
import 'ol/ol.css'

const shops = ref([]), selected = ref(null), selectedId = ref(null), selectedCoordinates = ref(null), loading = ref(true), query = ref(''), shopsVisible = ref(true), expanded = ref(true), favorited = ref(false), feedbackMessage = ref('')
const filteredShops = computed(() => {
  const term = query.value.trim().toLocaleLowerCase('id-ID')
  return shops.value.filter(shop => `${shop.properties.name} ${shop.properties.address || ''} ${shop.properties.ward || ''} ${shop.properties.business_type || ''}`.toLocaleLowerCase('id-ID').includes(term))
})
const coordinateText = computed(() => selectedCoordinates.value ? `${selectedCoordinates.value[1].toFixed(6)}, ${selectedCoordinates.value[0].toFixed(6)}` : 'Koordinat kedai')
const mapsUrl = computed(() => selectedCoordinates.value ? `https://www.google.com/maps/search/?api=1&query=${selectedCoordinates.value[1]},${selectedCoordinates.value[0]}` : '#')
let map, source, shopLayer
const normalStyle = new Style({ image: new CircleStyle({ radius: 7, fill: new Fill({ color: '#79513a' }), stroke: new Stroke({ color: '#fff', width: 2 }) }) })
const selectedStyle = new Style({ image: new CircleStyle({ radius: 10, fill: new Fill({ color: '#d5743b' }), stroke: new Stroke({ color: '#fff', width: 3 }) }) })
const focusShop = (shop, coordinate = null) => {
  const feature = source?.getFeatureById(shop.id)
  if (!feature) return
  selected.value = shop; selectedId.value = shop.id
  selectedCoordinates.value = coordinate ? toLonLat(coordinate) : feature.getGeometry().getCoordinates().slice()
  expanded.value = true; favorited.value = false; feedbackMessage.value = ''
  shopLayer?.changed()
  map.getView().animate({ center: feature.getGeometry().getCoordinates(), zoom: Math.max(15, map.getView().getZoom()), duration: 400 })
}
const searchMap = () => { if (filteredShops.value[0]) focusShop(filteredShops.value[0]) }
const toggleShops = () => { shopsVisible.value = !shopsVisible.value; shopLayer?.setVisible(shopsVisible.value); if (!shopsVisible.value) closePopup() }
const zoomIn = () => map?.getView().animate({ zoom: map.getView().getZoom() + 1, duration: 180 })
const zoomOut = () => map?.getView().animate({ zoom: map.getView().getZoom() - 1, duration: 180 })
const resetExtent = () => { if (source?.getFeatures().length) map.getView().fit(source.getExtent(), { padding: [45, 45, 45, 45], maxZoom: 14, duration: 300 }) }
const closePopup = () => { selected.value = null; selectedId.value = null; selectedCoordinates.value = null; shopLayer?.changed() }
const shareShop = async () => { try { await navigator.clipboard.writeText(mapsUrl.value); feedbackMessage.value = 'Tautan lokasi berhasil disalin.' } catch { feedbackMessage.value = mapsUrl.value } }
onMounted(async () => {
  source = new VectorSource()
  shopLayer = new VectorLayer({ source, style: feature => String(feature.getId()) === String(selectedId.value) ? selectedStyle : normalStyle })
  map = new MapView({ target: 'public-map', layers: [new TileLayer({ source: new OSM() }), shopLayer], view: new View({ center: fromLonLat([109.34, -0.026]), zoom: 12 }), controls: defaultControls({ attribution: false }) })
  map.on('singleclick', event => {
    const feature = map.forEachFeatureAtPixel(event.pixel, item => item) || null
    if (!feature) return closePopup()
    const shop = shops.value.find(item => String(item.id) === String(feature.getId()))
    if (shop) focusShop(shop, event.coordinate)
  })
  try {
    const response = await fetch('/api/public/coffee-shops', { headers: { Accept: 'application/geo+json, application/json' } })
    if (!response.ok) throw new Error('Data kedai gagal dimuat.')
    const collection = await response.json(); shops.value = collection.features || []
    source.addFeatures(new GeoJSON().readFeatures(collection, { dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857' }))
    resetExtent()
  } catch (error) { console.error(error) }
  finally { loading.value = false; setTimeout(() => map?.updateSize(), 0) }
})
onBeforeUnmount(() => { map?.setTarget(undefined); map = null })
</script>
