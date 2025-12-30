# 🏢 Facilities Management System (WO Facilities)

Sistem manajemen Work Order (WO) untuk Divisi Facility. Aplikasi ini menangani pembuatan tiket pelaporan kerusakan, penugasan teknisi, update status pekerjaan, hingga reporting.

## 🛠 Teknologi Utama

-   **Backend:** Laravel 10+
-   **Frontend:** Blade Templates + Alpine.js (v3)
-   **Styling:** Tailwind CSS
-   **Bundler:** Vite
-   **Database:** MySQL / PostgreSQL

---

## 🏗️ Arsitektur Frontend (PENTING UNTUK DIBACA)

Project ini menggunakan arsitektur **Modular & Event-Driven** dengan Alpine.js. Ini berbeda dengan pendekatan *monolithic* (satu file besar).

### 1. Konsep Modular
Setiap fitur (Create, Edit, Detail, Tabel) memiliki file JavaScript dan State-nya sendiri agar tidak terjadi konflik variabel.

-   **Tidak ada Global Scope:** Variabel di Modal Edit tidak bisa diakses langsung oleh Tabel, dan sebaliknya.
-   **Registrasi Komponen:** Semua modul didaftarkan secara manual di `resources/js/app.js`.

### 2. Struktur File (`resources/js/Components/Facilities/Modules/`)

| File | Nama Komponen Alpine | Fungsi & Tanggung Jawab |
| :--- | :--- | :--- |
| `create.js` | `facilityCreate` | Handle form input, validasi, dan auto-fetch NIK pelapor. |
| `edit.js` | `facilityEdit` | Handle update status, assign teknisi, dan datepicker. |
| `detail.js` | `facilityDetail` | Menampilkan data tiket secara *readonly*. |
| `toolbar.js` | `facilityTable` | Handle logika tabel utama (Checkbox, Jam Digital, Export Excel). |

> **Catatan Khusus:** Di file Blade, kita memanggil `x-data="facilityTable"`, tetapi logika aslinya ada di file `toolbar.js`. Ini diatur via *aliasing* di `app.js`.

---

## 📡 Komunikasi Antar Komponen (Event-Driven)

Karena komponen terpisah scope, **Tabel** tidak bisa memanggil fungsi di **Modal** secara langsung. Komunikasi wajib menggunakan **Alpine Custom Events (`$dispatch`)**.

### Cara Kerja:
1.  **Tombol di Tabel** mengirim sinyal (`$dispatch`).
2.  **Modal** mendengar sinyal (`@window`) dan menjalankan fungsinya.

### Contoh Implementasi yang Benar:

**❌ JANGAN LAKUKAN INI (Cara Lama/Error):**
```html
<button @click="openEditModal(ticket)">Edit</button>