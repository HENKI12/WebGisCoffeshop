<template>
  <main class="dashboard-shell">
    <aside class="dashboard-sidebar">
      <a class="dashboard-brand" href="/"><span class="dashboard-brand-mark">☕</span><span><strong>Kopi<span>Pontianak</span></strong><small>COFFEE SHOP WEBGIS</small></span></a>
      <p class="dashboard-nav-label">WORKSPACE</p>
      <button v-for="item in menu" :key="item.id" class="dashboard-nav-item" :class="{ active: page === item.id }" @click="setPage(item.id)"><span class="dashboard-nav-icon">{{ item.icon }}</span><span>{{ item.label }}</span><i v-if="page === item.id"></i></button>
      <div class="dashboard-sidebar-spacer"></div>
      <a class="dashboard-public-link" href="/publik" target="_blank"><span class="dashboard-nav-icon">↗</span><span>Lihat peta publik</span></a>
      <div class="dashboard-profile"><span class="dashboard-avatar">{{ initials }}</span><span><strong>{{ userName }}</strong><small>Operator</small></span><form method="POST" action="/logout"><input type="hidden" name="_token" :value="csrf"><button title="Keluar">⇥</button></form></div>
    </aside>

    <section class="dashboard-main">
      <header class="dashboard-topbar"><div><span>Dashboard</span><b>/</b><strong>{{ pageTitle }}</strong></div><div><span class="dashboard-date">Kota Pontianak</span><span class="dashboard-top-avatar">{{ initials }}</span></div></header>
      <div class="dashboard-content">
        <div v-if="error" class="manager-error">{{ error }} <button @click="load">Coba lagi</button></div>

        <template v-if="page === 'overview'">
          <div class="dashboard-page-heading"><div><span class="dashboard-eyebrow">RINGKASAN PENGELOLAAN</span><h1>Selamat datang, {{ firstName }}</h1><p>Pantau data kafe dan warung kopi di Kota Pontianak.</p></div><button class="dashboard-primary" @click="openCreate">＋ Tambah tempat</button></div>
          <div class="dashboard-stat-grid"><article class="dashboard-stat"><span class="dashboard-stat-icon coffee-icon">☕</span><small>Total tempat</small><strong>{{ shops.length }}</strong><em>data usaha terdaftar</em></article><article class="dashboard-stat"><span class="dashboard-stat-icon map-icon">⌖</span><small>Lokasi terpetakan</small><strong>{{ mappedCount }}</strong><em>{{ shops.length ? Math.round(mappedCount / shops.length * 100) : 0 }}% memiliki titik peta</em></article><article class="dashboard-stat"><span class="dashboard-stat-icon verify-icon">!</span><small>Perlu verifikasi titik</small><strong>{{ unverifiedCount }}</strong><em>perkiraan kelurahan / contoh</em></article><article class="dashboard-stat"><span class="dashboard-stat-icon source-icon">▤</span><small>Kelurahan</small><strong>{{ wardCount }}</strong><em>wilayah dalam data</em></article></div>
          <div class="dashboard-overview-grid"><section class="dashboard-card"><header><div><h2>Tempat terbaru</h2><p>Usaha yang ditambahkan atau diperbarui.</p></div><button @click="setPage('businesses')">Lihat semua →</button></header><div class="dashboard-recent-list"><button v-for="shop in shops.slice(0, 6)" :key="shop.id" @click="openEdit(shop)"><span class="recent-coffee-icon">☕</span><span><strong>{{ shop.name }}</strong><small>{{ shop.ward }} · {{ shop.business_type }}</small></span><span class="recent-status" :class="{ verified: shop.location_accuracy === 'Terverifikasi' }">{{ shop.location_accuracy === 'Terverifikasi' ? 'Terverifikasi' : 'Perlu cek' }}</span></button><p v-if="!shops.length" class="dashboard-empty">{{ loading ? 'Memuat data…' : 'Belum ada data tempat.' }}</p></div></section><section class="dashboard-card dashboard-source-card"><span class="dashboard-eyebrow">SUMBER DATA</span><h2>Registrasi usaha Pontianak</h2><p>Daftar awal diambil dari data izin kafe dan warung kopi tahun 2021.</p><div class="dashboard-source-meta"><span>117</span><small>usaha diimpor</small></div><div class="dashboard-source-note">Titik awal ditaksir dari kelurahan. Periksa dan perbarui pin usaha sebelum digunakan sebagai lokasi navigasi.</div><button @click="setPage('settings')">Detail sumber data →</button></section></div>
        </template>

        <template v-else-if="page === 'map'">
          <div class="dashboard-page-heading"><div><span class="dashboard-eyebrow">PETA OPERASIONAL</span><h1>Peta kedai kopi</h1><p>Klik pin untuk melihat usaha. Gunakan mode pilih titik untuk mengoreksi koordinat.</p></div><button class="dashboard-secondary" @click="resetExtent">⌖ Tampilkan semua</button></div>
          <section class="coffee-map-card dashboard-map-card"><div class="coffee-map-heading"><div><strong>Lokasi usaha</strong><small>{{ mappedCount }} dari {{ shops.length }} tempat memiliki koordinat</small></div><span v-if="pickLocation" class="pick-status">Pilih titik pada peta… <button @click="cancelPick">Batal</button></span></div><div id="manager-map" class="coffee-manager-map dashboard-map"></div><div v-if="selectedShop && !pickLocation" class="manager-map-card-popup"><strong>{{ selectedShop.name }}</strong><span>{{ selectedShop.ward }} · {{ selectedShop.business_type }}</span><button @click="openEdit(selectedShop)">Ubah data</button></div><div v-if="pickLocation" class="dashboard-map-hint">Klik posisi usaha pada peta untuk mengisi latitude dan longitude.</div></section>
        </template>

        <template v-else-if="page === 'businesses'">
          <div class="dashboard-page-heading"><div><span class="dashboard-eyebrow">DATABASE USAHA</span><h1>Data tempat</h1><p>Cari, perbarui, dan ekspor informasi kafe atau warung kopi.</p></div><div class="dashboard-heading-actions"><button class="dashboard-secondary" @click="exportCsv">⇩ Ekspor CSV</button><button class="dashboard-primary" @click="openCreate">＋ Tambah tempat</button></div></div>
          <section class="manager-table dashboard-business-table"><header><div><strong>Daftar tempat</strong><small>{{ filtered.length }} dari {{ shops.length }} usaha</small></div><input v-model="search" placeholder="Cari nama, alamat, kelurahan…" /></header><div class="manager-scroll"><table><thead><tr><th>NAMA USAHA</th><th>ALAMAT</th><th>KELURAHAN</th><th>JENIS</th><th>AKURASI TITIK</th><th></th></tr></thead><tbody><tr v-for="shop in filtered" :key="shop.id"><td><strong>{{ shop.name }}</strong><small class="coffee-license">NIB {{ shop.license_number || '—' }}</small></td><td>{{ shop.address || '—' }}</td><td>{{ shop.ward || '—' }}</td><td>{{ shop.business_type || '—' }}</td><td><span class="accuracy-badge" :class="{ verified: shop.location_accuracy === 'Terverifikasi' }">{{ shop.location_accuracy }}</span></td><td><button class="manager-edit" @click="openEdit(shop)">Ubah</button><button class="manager-delete" @click="remove(shop)">Hapus</button></td></tr><tr v-if="!filtered.length"><td colspan="6" class="manager-empty">{{ loading ? 'Memuat data…' : 'Tidak ada kedai yang cocok.' }}</td></tr></tbody></table></div></section>
        </template>

        <template v-else-if="page === 'verification'">
          <div class="dashboard-page-heading"><div><span class="dashboard-eyebrow">KUALITAS DATA</span><h1>Verifikasi lokasi</h1><p>Tempat berikut memiliki titik perkiraan dan perlu diperiksa operator.</p></div><button class="dashboard-secondary" @click="setPage('map')">Buka peta →</button></div>
          <section class="manager-table dashboard-business-table"><header><div><strong>Lokasi yang perlu diperiksa</strong><small>{{ unverified.length }} tempat</small></div><input v-model="search" placeholder="Cari nama atau kelurahan…" /></header><div class="manager-scroll"><table><thead><tr><th>NAMA USAHA</th><th>ALAMAT</th><th>KELURAHAN</th><th>KOORDINAT SAAT INI</th><th>AKURASI</th><th></th></tr></thead><tbody><tr v-for="shop in unverified.filter(matchesSearch)" :key="shop.id"><td><strong>{{ shop.name }}</strong></td><td>{{ shop.address || '—' }}</td><td>{{ shop.ward || '—' }}</td><td>{{ shop.latitude ?? '—' }}, {{ shop.longitude ?? '—' }}</td><td><span class="accuracy-badge">{{ shop.location_accuracy }}</span></td><td><button class="manager-edit" @click="editLocation(shop)">Periksa titik</button></td></tr><tr v-if="!unverified.filter(matchesSearch).length"><td colspan="6" class="manager-empty">Semua lokasi sudah ditandai terverifikasi.</td></tr></tbody></table></div></section>
        </template>

        <template v-else>
          <div class="dashboard-page-heading"><div><span class="dashboard-eyebrow">KONFIGURASI</span><h1>Sumber & informasi</h1><p>Catatan kualitas dan asal data direktori.</p></div></div>
          <section class="dashboard-settings-grid"><article class="dashboard-card"><span class="dashboard-eyebrow">DATA AWAL</span><h2>Data Cafe dan Warung Kopi Terdaftar</h2><p>Daftar usaha terdaftar di PTSP Kota Pontianak, berisi NIB, nama usaha, alamat, kelurahan, dan jenis usaha.</p><dl><div><dt>Tahun sumber</dt><dd>2021</dd></div><div><dt>Jumlah baris</dt><dd>{{ shops.length }}</dd></div><div><dt>Koordinat di file sumber</dt><dd>Tidak tersedia</dd></div><div><dt>Sumber titik awal</dt><dd>Perkiraan kelurahan</dd></div></dl></article><article class="dashboard-card"><span class="dashboard-eyebrow">PANDUAN KOREKSI</span><h2>Perbarui lokasi usaha</h2><p>Pilih usaha di halaman Data Tempat, buka Ubah, lalu tekan Pilih titik di peta. Klik posisi usaha yang benar untuk memperbarui latitude dan longitude.</p><button class="dashboard-primary" @click="setPage('businesses')">Buka data tempat →</button></article></section>
          <p class="manager-note">Data sumber berlisensi Creative Commons Attribution. Koordinat yang belum dikonfirmasi tidak boleh dianggap sebagai alamat tepat.</p>
        </template>
      </div>
    </section>

    <div v-if="modal" class="manager-backdrop" @click.self="closeModal"><form class="manager-modal coffee-modal" @submit.prevent="save"><h2>{{ form.id ? 'Ubah tempat' : 'Tambah tempat' }}</h2><p>Perbarui informasi usaha dan posisi peta.</p><div class="manager-fields"><label>Nama usaha<input v-model="form.name" required /></label><label>NIB / nomor izin<input v-model="form.license_number" /></label><label>Alamat<input v-model="form.address" /></label><label>Kelurahan<input v-model="form.ward" /></label><label>Jenis usaha<select v-model="form.business_code" @change="form.business_type = form.business_code === '56304' ? 'Kedai Minuman' : 'Rumah Minum/Kafe'"><option value="56303">Rumah Minum / Kafe</option><option value="56304">Kedai Minuman</option><option value="lainnya">Lainnya</option></select></label><label>Jenis usaha (label)<input v-model="form.business_type" /></label><label>Jam buka<input v-model="form.opening_hours" placeholder="Belum tersedia" /></label><label>Akurasi lokasi<select v-model="form.location_accuracy"><option>Perkiraan kelurahan</option><option>Contoh operator</option><option>Alamat tergeocode</option><option>Terverifikasi</option></select></label><label>Latitude<input v-model.number="form.latitude" type="number" step="any" min="-90" max="90" /></label><label>Longitude<input v-model.number="form.longitude" type="number" step="any" min="-180" max="180" /></label><label class="manager-full">Sumber data<input v-model="form.source" /></label></div><p v-if="formError" class="manager-error">{{ formError }}</p><footer><button type="button" @click="startPick">Pilih titik di peta</button><span></span><button type="button" @click="closeModal">Batal</button><button class="manager-primary" :disabled="saving">{{ saving ? 'Menyimpan…' : 'Simpan' }}</button></footer></form></div>
  </main>
</template>

<script setup>
import { computed, nextTick, onMounted, onBeforeUnmount, reactive, ref, watch } from 'vue'
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

const menu = [
  { id: 'overview', label: 'Ringkasan', icon: '▦' },
  { id: 'map', label: 'Peta kedai', icon: '⌖' },
  { id: 'businesses', label: 'Data tempat', icon: '☕' },
  { id: 'verification', label: 'Verifikasi lokasi', icon: '✓' },
  { id: 'settings', label: 'Sumber data', icon: '▤' },
]
const page = ref('overview'), shops = ref([]), loading = ref(false), search = ref(''), modal = ref(false), saving = ref(false), error = ref(''), formError = ref(''), pickLocation = ref(false), selectedShop = ref(null)
const csrf = document.querySelector('meta[name="csrf-token"]')?.content || ''
const userName = document.querySelector('meta[name="app-user"]')?.content || 'Administrator'
const initials = userName.split(/\s+/).slice(0, 2).map(part => part[0]?.toUpperCase()).join('')
const firstName = userName.split(/\s+/)[0]
const blank = () => ({ id: null, source_key: '', license_number: '', name: '', address: '', ward: '', business_code: '56303', business_type: 'Rumah Minum/Kafe', latitude: null, longitude: null, location_accuracy: 'Perkiraan kelurahan', opening_hours: '', source: 'Data operator' })
const form = reactive(blank())
const pageTitle = computed(() => menu.find(item => item.id === page.value)?.label || 'Ringkasan')
const filtered = computed(() => shops.value.filter(shop => matchesSearch(shop)))
const matchesSearch = shop => `${shop.name} ${shop.address || ''} ${shop.ward || ''}`.toLocaleLowerCase('id-ID').includes(search.value.toLocaleLowerCase('id-ID'))
const unverified = computed(() => shops.value.filter(shop => shop.location_accuracy !== 'Terverifikasi'))
const mappedCount = computed(() => shops.value.filter(shop => shop.latitude !== null && shop.longitude !== null).length)
const unverifiedCount = computed(() => unverified.value.length)
const wardCount = computed(() => new Set(shops.value.map(shop => shop.ward).filter(Boolean)).size)
let map, pointSource, pointLayer, captureDraft
const markerStyle = new Style({ image: new CircleStyle({ radius: 6, fill: new Fill({ color: '#79513a' }), stroke: new Stroke({ color: '#fff', width: 2 }) }) })
const activeStyle = new Style({ image: new CircleStyle({ radius: 9, fill: new Fill({ color: '#d5743b' }), stroke: new Stroke({ color: '#fff', width: 3 }) }) })
const setPage = async next => { page.value = next; selectedShop.value = null; if (next === 'map') { await nextTick(); initMap(); map?.updateSize() } }
const renderPoints = () => {
  if (!pointSource) return
  pointSource.clear()
  const geojson = { type: 'FeatureCollection', features: shops.value.filter(shop => shop.latitude !== null && shop.longitude !== null).map(shop => ({ type: 'Feature', id: shop.id, geometry: { type: 'Point', coordinates: [Number(shop.longitude), Number(shop.latitude)] }, properties: { name: shop.name } })) }
  pointSource.addFeatures(new GeoJSON().readFeatures(geojson, { dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857' }))
  pointLayer?.changed()
}
const load = async () => {
  loading.value = true; error.value = ''
  try { const response = await fetch('/api/coffee-shops', { headers: { Accept: 'application/json' } }); if (!response.ok) throw new Error('Data kedai gagal dimuat.'); const payload = await response.json(); shops.value = payload.data || payload; renderPoints() }
  catch (e) { error.value = e.message }
  finally { loading.value = false }
}
const initMap = () => {
  if (map || !document.querySelector('#manager-map')) return
  pointSource = new VectorSource()
  pointLayer = new VectorLayer({ source: pointSource, style: feature => feature.get('selected') ? activeStyle : markerStyle })
  map = new MapView({ target: 'manager-map', layers: [new TileLayer({ source: new OSM() }), pointLayer], view: new View({ center: fromLonLat([109.34, -0.026]), zoom: 12 }), controls: defaultControls({ attribution: false }) })
  map.on('singleclick', event => {
    if (pickLocation.value) {
      const [longitude, latitude] = toLonLat(event.coordinate)
      Object.assign(form, captureDraft || blank(), { longitude: Number(longitude.toFixed(7)), latitude: Number(latitude.toFixed(7)), location_accuracy: 'Contoh operator' })
      captureDraft = null; pickLocation.value = false; formError.value = ''; modal.value = true; return
    }
    const feature = map.forEachFeatureAtPixel(event.pixel, item => item) || null
    selectedShop.value = feature ? shops.value.find(shop => String(shop.id) === String(feature.getId())) || null : null
    pointSource?.getFeatures().forEach(item => item.set('selected', item === feature)); pointLayer?.changed()
  })
  renderPoints()
  if (pointSource.getFeatures().length) map.getView().fit(pointSource.getExtent(), { padding: [40, 40, 40, 40], maxZoom: 13 })
  setTimeout(() => map?.updateSize(), 0)
}
const focusShop = async shop => {
  selectedShop.value = shop
  if (page.value !== 'map') await setPage('map')
  if (shop.latitude === null || shop.longitude === null) return
  map?.getView().animate({ center: fromLonLat([Number(shop.longitude), Number(shop.latitude)]), zoom: Math.max(map.getView().getZoom(), 16), duration: 350 })
  const feature = pointSource?.getFeatureById(shop.id)
  if (feature) { pointSource.getFeatures().forEach(item => item.set('selected', item === feature)); pointLayer.changed() }
}
const openCreate = () => { Object.assign(form, blank()); formError.value = ''; selectedShop.value = null; modal.value = true }
const openEdit = shop => { Object.assign(form, { ...blank(), ...shop, business_code: shop.business_code || '56303' }); formError.value = ''; modal.value = true }
const editLocation = async shop => { openEdit(shop); await focusShop(shop) }
const closeModal = () => { modal.value = false; pickLocation.value = false; captureDraft = null }
const startPick = async () => { captureDraft = { ...form }; modal.value = false; pickLocation.value = true; await setPage('map') }
const cancelPick = () => { pickLocation.value = false; captureDraft = null }
const save = async () => {
  saving.value = true; formError.value = ''
  try {
    const payload = { ...form, source_key: form.source_key || `operator-${Date.now()}`, latitude: form.latitude === '' ? null : form.latitude, longitude: form.longitude === '' ? null : form.longitude }
    const response = await fetch(form.id ? `/api/coffee-shops/${form.id}` : '/api/coffee-shops', { method: form.id ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(payload) })
    const result = await response.json()
    if (!response.ok) throw new Error(result.message || Object.values(result.errors || {}).flat()[0] || 'Data gagal disimpan.')
    modal.value = false; await load()
  } catch (e) { formError.value = e.message }
  finally { saving.value = false }
}
const remove = async shop => {
  if (!window.confirm(`Hapus ${shop.name}?`)) return
  const response = await fetch(`/api/coffee-shops/${shop.id}`, { method: 'DELETE', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } })
  if (response.ok) { selectedShop.value = null; await load() } else error.value = 'Tempat gagal dihapus.'
}
const exportCsv = () => {
  const columns = ['name', 'license_number', 'address', 'ward', 'business_type', 'latitude', 'longitude', 'location_accuracy']
  const quote = value => `"${String(value ?? '').replaceAll('"', '""')}"`
  const csv = [columns.join(','), ...shops.value.map(shop => columns.map(column => quote(shop[column])).join(','))].join('\r\n')
  const link = document.createElement('a'); link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' })); link.download = 'kedai-kopi-pontianak.csv'; link.click(); URL.revokeObjectURL(link.href)
}
const resetExtent = () => { if (pointSource?.getFeatures().length) map.getView().fit(pointSource.getExtent(), { padding: [40, 40, 40, 40], maxZoom: 13, duration: 300 }) }
watch(search, () => { if (page.value === 'map' && filtered.value[0]) selectedShop.value = null })
onMounted(() => { load() })
onBeforeUnmount(() => { map?.setTarget(undefined); map = null })
</script>
