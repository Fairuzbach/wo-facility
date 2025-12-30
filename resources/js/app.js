import './bootstrap';
import './dashboard'; // Jika file ini ada

import Alpine from 'alpinejs';

// --- 1. IMPORT MODULES ---
// Pastikan path ini sesuai dengan struktur folder Anda
import facilityCreate  from './Components/Facilities/Modules/create';
import facilityEdit    from './Components/Facilities/Modules/edit';
import facilityDetail  from './Components/Facilities/Modules/detail';
import facilityToolbar from './Components/Facilities/Modules/toolbar'; 

window.Alpine = Alpine;

// --- 2. REGISTER COMPONENTS (DAFTARKAN SATU PER SATU) ---

// Mendaftarkan komponen untuk Modal Create
Alpine.data('facilityCreate', facilityCreate);

// Mendaftarkan komponen untuk Modal Edit
Alpine.data('facilityEdit', facilityEdit);

// Mendaftarkan komponen untuk Modal Detail
Alpine.data('facilityDetail', facilityDetail);

// --- 3. ALIASING UNTUK TABEL UTAMA ---
// Karena di index.blade.php Anda memanggil x-data="facilityTable",
// tapi logika tabelnya ada di file 'toolbar.js', kita hubungkan keduanya di sini.
Alpine.data('facilityTable', facilityToolbar);

// (Opsional) Jika ada yang memanggil facilityToolbar langsung
Alpine.data('facilityToolbar', facilityToolbar);

// --- 4. JALANKAN ALPINE ---
Alpine.start();