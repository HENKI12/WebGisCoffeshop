# KopiPontianak WebGIS

WebGIS publik untuk menemukan kafe dan warung kopi di Kota Pontianak, dengan halaman pengelola untuk memperbarui informasi usaha dan posisi peta.

## Fitur

- Peta publik, pencarian nama/alamat/kelurahan, daftar tempat, dan detail lokasi.
- Penanda lokasi pada peta OpenStreetMap/OpenLayers serta tautan petunjuk arah.
- Login operator, CRUD usaha, pemilihan titik dengan klik peta, dan ekspor CSV.
- API publik mengirim fitur GeoJSON Point dalam EPSG:4326.

## Sumber dan kualitas data

Daftar nama, nomor izin/NIB, alamat, kelurahan, dan kategori berasal dari [Data Cafe dan Warung Kopi Terdaftar — Satu Data Kota Pontianak](https://ckan.pontianak.go.id/dataset/cafe-dan-warung-kopi-terdaftar), snapshot tahun 2021 (117 baris pada berkas sumber). Data ini adalah daftar registrasi lama, bukan jaminan bahwa setiap usaha masih beroperasi.

Berkas sumber tidak memiliki koordinat. Pin awal dibuat sebagai perkiraan di sekitar kelurahan agar peta bisa didemonstrasikan; label akurasi menjelaskannya. Operator perlu memindahkan pin ke alamat usaha sebenarnya sebelum dipakai sebagai direktori akurat. Jam buka, rating, nomor kontak, dan fasilitas tidak dikarang.

## Menjalankan lokal

1. Siapkan `.env` dan koneksi database.
2. Pasang dependensi dan buat skema/data:

   ```bash
   composer install
   php artisan key:generate
   npm install
   php artisan migrate --seed
   ```

3. Jalankan Laravel dan Vite:

   ```bash
   php artisan serve
   npm run dev
   ```

4. Buka `/` untuk peta publik, atau `/login` untuk pengelola.

### Akun demo

- Email: `admin@ruangtanah.test`
- Kata sandi: `Sekadau123!`

Data tiles peta membutuhkan koneksi internet. Dataset dikelola dengan atribusi ke Pemerintah Kota Pontianak dan menggunakan lisensi yang tercantum di portal sumber.
