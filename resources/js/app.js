import './bootstrap';

import Alpine from 'alpinejs';

// --- 1. IMPORT MODULES ---
// Pastikan path ini sesuai dengan struktur folder Anda
import facilityCreate  from './Components/Facilities/Modules/create';
import facilityEdit    from './Components/Facilities/Modules/edit';
import facilityDetail  from './Components/Facilities/Modules/detail';
import facilityToolbar from './Components/Facilities/Modules/toolbar'; 
import facilityDashboard from './Components/Facilities/Modules/dashboard';

window.Alpine = Alpine;

// --- 2. REGISTER COMPONENTS (DAFTARKAN SATU PER SATU) ---

Alpine.data('facilityCreate', facilityCreate);
Alpine.data('facilityEdit', facilityEdit);
Alpine.data('facilityDetail', facilityDetail);
Alpine.data('facilityTable', facilityToolbar);
Alpine.data('facilityToolbar', facilityToolbar);
Alpine.data('facilityDashboard', facilityDashboard);

// --- 4. JALANKAN ALPINE ---
Alpine.start();