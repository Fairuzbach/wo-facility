// resources/js/Components/Facilities/state.js
export const createState = () => ({
    // Auth
    isLoggedIn: window.facilitiesConfig?.isLoggedIn || false,
    apiToken: window.facilitiesConfig?.apiToken || '',

    // Modal States
    showCreateModal: false,
    showEditModal: false,
    showDetailModal: false,

    // Loading States
    isSubmitting: false,
    isCheckingNik: false,

    // Master Data
    machines: window.facilitiesConfig?.machines || [],
    technicians: window.facilitiesConfig?.technicians || [],
    pageIds: window.facilitiesConfig?.pageIds || [],

    // Current Data
    ticket: window.facilitiesConfig?.openTicket || null,

    // Create Form
    form: {
        requester_name: '',
        requester_email: '',
        plant_id: '',
        machine_id: '',
        category: '',
        description: '',
        location_details: '',
        target_completion_date: '',
        photo: null
    },

    // Edit Form
    editForm: {
        id: '',
        status: '',
        note: '',
        completion_note: '',
        start_date: '',
        actual_completion_date: '',
        selectedTechs: []
    },

    // Guest/Employee Inputs
    currentNikInput: '',
    currentNameInput: '',
    currentDivInput: '',
    addedEmployees: [],

    // Toolbar
    selectedTickets: [],

    // Additional
    machine_origin_plant_id: '',
    target_plant_id: '',
    new_machine_name: ''
});