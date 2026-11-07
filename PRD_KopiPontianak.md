# PRD KopiPontianak WebGIS

## Tujuan

Menyediakan peta publik untuk mencari kafe dan warung kopi di Kota Pontianak, serta ruang operator untuk mengelola profil usaha dan memperbaiki koordinatnya.

## Pengguna

- **Pengunjung:** melihat peta, mencari nama/alamat/kelurahan, membaca detail usaha, dan membuka petunjuk arah.
- **Operator:** menambah, mengubah, dan menghapus usaha; memilih posisi tepat dengan klik peta; mengekspor daftar ke CSV.

## Data

Data awal mengacu pada dataset registrasi kafe dan warung kopi Kota Pontianak tahun 2021 dari Satu Data Kota Pontianak. Dataset sumber memuat nomor usaha, nama, alamat, kelurahan, dan klasifikasi usaha, tetapi tidak memuat koordinat. Pin awal karena itu hanya perkiraan berdasarkan kelurahan dan harus diperiksa operator. Atribut tanpa sumber, seperti jam buka, rating, dan fasilitas, dibiarkan kosong.

## Teknologi

- Laravel 12 dan MySQL/SQLite untuk API, autentikasi, serta data usaha.
- Vue 3 dan Vite untuk antarmuka.
- OpenLayers 10 untuk peta dan pemilihan titik.
- GeoJSON Point dengan koordinat WGS 84 / EPSG:4326 untuk API publik.
- OpenStreetMap sebagai peta dasar.

## Batas penggunaan

Koordinat perkiraan bukan hasil geocoding alamat dan tidak boleh dianggap sebagai lokasi terverifikasi. Operator harus memperbaiki titik sebelum data dipakai sebagai direktori navigasi.
