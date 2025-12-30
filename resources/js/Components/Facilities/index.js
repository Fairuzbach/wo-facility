// resources/js/Components/Facilities/index.js

import { createState } from './state';
import { FacilitiesAPI } from './Services/api';

// Import existing modules
import createModule from './Modules/create';
import editModule from './Modules/edit';
import detailModule from './Modules/detail';
import toolbarModule from './Modules/toolbar';

export default () => {
    // Initialize state
    const state = createState();
    
    // Initialize API service
    const api = new FacilitiesAPI(state.apiToken);

    return {
        // Spread all state
        ...state,

        // Inject API service
        api,

        // Spread all modules (fungsi dari setiap module akan merge jadi satu)
        ...createModule(),
        ...editModule(),
        ...detailModule(),
        ...toolbarModule(),

        // Main init lifecycle
        init() {
            // Auto-open ticket from notification
            if (this.ticket) {
                this.openDetailModal(this.ticket);

                const url = new URL(window.location);
                url.searchParams.delete('open_ticket_id');
                window.history.replaceState({}, '', url);
            }

            // Watch plant_id changes
            this.$watch('form.plant_id', () => {
                this.form.machine_id = '';
                this.machine_origin_plant_id = '';
            });

            // Debounced NIK check
            let debounceTimer;
            this.$watch('currentNikInput', (value) => {
                clearTimeout(debounceTimer);

                if (value && value.length >= 3) {
                    this.isCheckingNik = true;
                    debounceTimer = setTimeout(() => {
                        this.checkNik(value);
                    }, 500);
                } else {
                    this.currentNameInput = '';
                    this.currentDivInput = '';
                    this.isCheckingNik = false;
                }
            });

            // Watch edit modal
            this.$watch('showEditModal', (value) => {
                if (!value) {
                    setTimeout(() => this.resetEditForm(), 300);
                }
            });
        }
    };
};