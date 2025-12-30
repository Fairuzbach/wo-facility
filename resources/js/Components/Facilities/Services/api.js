// resources/js/Components/Facilities/Services/api.js

export class FacilitiesAPI {
    constructor(token) {
        this.token = token;
        this.baseUrl = window.facilitiesConfig?.apiUrl || '/api/facility-wo';
    }

    async request(url, options = {}) {
        const defaultHeaders = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        };

        if (this.token) {
            defaultHeaders['Authorization'] = `Bearer ${this.token}`;
        }

        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    ...defaultHeaders,
                    ...options.headers
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw {
                    status: response.status,
                    message: data.message,
                    errors: data.errors
                };
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    async checkEmployee(nik) {
        return await this.request(`/check-employee/${nik}`);
    }

    async createTicket(formData) {
        const url = window.facilitiesConfig?.createUrl || '/fh/store';
        return await this.request(url, {
            method: 'POST',
            body: formData
        });
    }

    async updateTicketStatus(id, payload) {
        return await this.request(`${this.baseUrl}/${id}/update-status`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
    }

    async exportTickets(selectedIds = []) {
        let url = window.routes?.export || '/api/facility-wo/export';
        url += '?export=true';
        
        if (selectedIds.length > 0) {
            url += '&selected_ids=' + selectedIds.join(',');
        }

        const response = await fetch(url, {
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) throw new Error('Export failed');
        
        return await response.blob();
    }
}