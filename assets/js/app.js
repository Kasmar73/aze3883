// SMM Panel JavaScript
class SMMPanel {
    constructor() {
        this.currentUser = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.authenticateUser();
        this.loadDashboard();
    }

    setupEventListeners() {
        // Sidebar navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const section = e.target.getAttribute('onclick').match(/'([^']+)'/)[1];
                this.showSection(section);
            });
        });

        // Profile form
        document.getElementById('profile-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.updateProfile();
        });

        // Add balance form
        document.getElementById('add-balance-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.addBalance();
        });
    }

    async authenticateUser() {
        try {
            // Telegram WebApp init data-nı al
            const initData = window.Telegram?.WebApp?.initData || '';
            
            if (!initData) {
                this.showError('Telegram WebApp məlumatları tapılmadı');
                return;
            }

            const response = await fetch('api/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ init_data: initData })
            });

            const result = await response.json();
            
            if (result.success) {
                this.currentUser = result;
                this.loadUserData();
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Authentication error:', error);
            this.showError('Giriş xətası');
        }
    }

    async loadUserData() {
        try {
            // User stats
            const statsResponse = await fetch('api/user.php?action=stats');
            const stats = await statsResponse.json();
            
            if (stats.success) {
                this.updateDashboard(stats.stats);
            }

            // User profile
            const profileResponse = await fetch('api/user.php?action=profile');
            const profile = await profileResponse.json();
            
            if (profile.success) {
                this.updateProfileForm(profile.user);
            }
        } catch (error) {
            console.error('Load user data error:', error);
        }
    }

    updateDashboard(stats) {
        document.getElementById('total-orders').textContent = stats.total_orders;
        document.getElementById('active-orders').textContent = stats.active_orders;
        document.getElementById('total-services').textContent = stats.total_services;
        document.getElementById('user-balance').textContent = stats.balance + ' AZN';
        document.getElementById('current-balance').textContent = stats.balance + ' AZN';
    }

    updateProfileForm(user) {
        document.getElementById('username').value = user.username || '';
        document.getElementById('telegram_id').value = user.telegram_id;
        document.getElementById('email').value = user.email || '';
    }

    showSection(sectionName) {
        // Hide all sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = 'none';
        });

        // Show selected section
        const targetSection = document.getElementById(sectionName + '-section');
        if (targetSection) {
            targetSection.style.display = 'block';
            targetSection.classList.add('fade-in');
        }

        // Update page title
        const titles = {
            'dashboard': 'Dashboard',
            'services': 'Xidmətlər',
            'orders': 'Sifarişlər',
            'balance': 'Balans',
            'profile': 'Profil'
        };
        document.getElementById('page-title').textContent = titles[sectionName] || 'Dashboard';

        // Update active nav link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        event.target.classList.add('active');

        // Load section data
        this.loadSectionData(sectionName);
    }

    async loadSectionData(sectionName) {
        switch (sectionName) {
            case 'services':
                await this.loadServices();
                break;
            case 'orders':
                await this.loadOrders();
                break;
        }
    }

    async loadServices() {
        try {
            this.showLoading('services-list');
            
            const response = await fetch('api/services.php?action=categories');
            const result = await response.json();
            
            if (result.success) {
                this.displayCategories(result.categories);
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Load services error:', error);
            this.showError('Xidmətlər yüklənə bilmədi');
        }
    }

    displayCategories(categories) {
        const container = document.getElementById('services-list');
        container.innerHTML = '';

        categories.forEach(category => {
            const categoryCard = document.createElement('div');
            categoryCard.className = 'col-md-6 col-lg-4 mb-4';
            categoryCard.innerHTML = `
                <div class="card service-card" onclick="smmPanel.loadCategoryServices(${category.id})">
                    <div class="card-body text-center">
                        <i class="${category.icon} fa-3x mb-3 text-primary"></i>
                        <h5 class="card-title">${category.name}</h5>
                        <p class="card-text text-muted">${category.description}</p>
                        <button class="btn btn-primary">Xidmətləri Gör</button>
                    </div>
                </div>
            `;
            container.appendChild(categoryCard);
        });
    }

    async loadCategoryServices(categoryId) {
        try {
            this.showLoading('services-list');
            
            const response = await fetch(`api/services.php?action=services&category_id=${categoryId}`);
            const result = await response.json();
            
            if (result.success) {
                this.displayServices(result.services);
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Load category services error:', error);
            this.showError('Xidmətlər yüklənə bilmədi');
        }
    }

    displayServices(services) {
        const container = document.getElementById('services-list');
        container.innerHTML = '';

        if (services.length === 0) {
            container.innerHTML = '<div class="col-12"><div class="alert alert-info">Bu kateqoriyada xidmət yoxdur</div></div>';
            return;
        }

        services.forEach(service => {
            const serviceCard = document.createElement('div');
            serviceCard.className = 'col-md-6 col-lg-4 mb-4';
            serviceCard.innerHTML = `
                <div class="card service-card">
                    <div class="card-body">
                        <h5 class="card-title">${service.name}</h5>
                        <p class="card-text">${service.description}</p>
                        <div class="service-price">${service.price} AZN</div>
                        <div class="service-quantity">${service.min_quantity} - ${service.max_quantity}</div>
                        <button class="btn btn-order w-100 mt-3" onclick="smmPanel.showOrderModal(${service.id})">
                            Sifariş Ver
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(serviceCard);
        });
    }

    async loadOrders() {
        try {
            this.showLoading('orders-tbody');
            
            const response = await fetch('api/orders.php');
            const result = await response.json();
            
            if (result.success) {
                this.displayOrders(result.orders);
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Load orders error:', error);
            this.showError('Sifarişlər yüklənə bilmədi');
        }
    }

    displayOrders(orders) {
        const tbody = document.getElementById('orders-tbody');
        tbody.innerHTML = '';

        if (orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">Sifariş yoxdur</td></tr>';
            return;
        }

        orders.forEach(order => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>#${order.id}</td>
                <td>${order.service_name}</td>
                <td><a href="${order.link}" target="_blank" class="text-truncate d-inline-block" style="max-width: 100px;">${order.link}</a></td>
                <td>${order.quantity}</td>
                <td>${order.price} AZN</td>
                <td><span class="order-status status-${order.status}">${this.getStatusText(order.status)}</span></td>
                <td>${new Date(order.created_at).toLocaleDateString('az-AZ')}</td>
            `;
            tbody.appendChild(row);
        });
    }

    getStatusText(status) {
        const statusTexts = {
            'pending': 'Gözləyir',
            'in_progress': 'İşlənir',
            'completed': 'Tamamlandı',
            'cancelled': 'Ləğv edildi',
            'refunded': 'Geri qaytarıldı'
        };
        return statusTexts[status] || status;
    }

    showOrderModal(serviceId) {
        // Bu hissə sonra əlavə ediləcək
        alert('Sifariş modalı - Service ID: ' + serviceId);
    }

    showAddBalanceModal() {
        const modal = new bootstrap.Modal(document.getElementById('addBalanceModal'));
        modal.show();
    }

    async updateProfile() {
        try {
            const formData = new FormData(document.getElementById('profile-form'));
            formData.append('action', 'update_profile');

            const response = await fetch('api/user.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(result.message);
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Update profile error:', error);
            this.showError('Profil yenilənə bilmədi');
        }
    }

    async addBalance() {
        try {
            const formData = new FormData(document.getElementById('add-balance-form'));
            formData.append('action', 'add_balance');

            const response = await fetch('api/user.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(result.message);
                document.getElementById('add-balance-form').reset();
                bootstrap.Modal.getInstance(document.getElementById('addBalanceModal')).hide();
                this.loadUserData(); // Refresh user data
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Add balance error:', error);
            this.showError('Balans artırıla bilmədi');
        }
    }

    showLoading(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = '<div class="loading show"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Yüklənir...</span></div></div>';
        }
    }

    showError(message) {
        this.showAlert(message, 'danger');
    }

    showSuccess(message) {
        this.showAlert(message, 'success');
    }

    showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('main .container-fluid');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    loadDashboard() {
        this.showSection('dashboard');
    }
}

// Global functions for onclick handlers
function showSection(sectionName) {
    smmPanel.showSection(sectionName);
}

function showAddBalanceModal() {
    smmPanel.showAddBalanceModal();
}

// Initialize the app
let smmPanel;
document.addEventListener('DOMContentLoaded', function() {
    smmPanel = new SMMPanel();
});