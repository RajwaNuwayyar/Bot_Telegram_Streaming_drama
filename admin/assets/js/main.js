/**
 * Main Interactive Script - Admin Dashboard
 */

// Toggle Mobile Sidebar
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar && overlay) {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }
}

// Quick Toast Notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bg = type === 'success' ? 'bg-emerald-600' : (type === 'error' ? 'bg-rose-600' : 'bg-cyan-600');
    const icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
    
    toast.className = `fixed bottom-5 right-5 z-50 flex items-center gap-3 px-5 py-3 rounded-xl text-white shadow-2xl ${bg} animate-slide-up text-sm font-medium`;
    toast.innerHTML = `<i class="fas ${icon}"></i> <span>${message}</span>`;
    
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// Client Side Real-time Table Filter
function setupTableSearch(inputId, tableBodyId) {
    const input = document.getElementById(inputId);
    const tbody = document.getElementById(tableBodyId);
    if (!input || !tbody) return;

    input.addEventListener('keyup', function () {
        const filter = input.value.toLowerCase();
        const rows = tbody.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const rowText = rows[i].textContent.toLowerCase();
            if (rowText.indexOf(filter) > -1) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    });
}

// Modal Helper
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Setup search if elements exist
    setupTableSearch('userSearchInput', 'userTableBody');
    setupTableSearch('txSearchInput', 'txTableBody');
    setupTableSearch('dramaSearchInput', 'dramaTableBody');
});

// ==========================================
// NOTIFIKASI DROPDOWN TOGGLE & CLICK OUTSIDE
// ==========================================
function toggleNotifDropdown(event) {
    if (event) event.stopPropagation();
    const dropdown = document.getElementById('notifDropdown');
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

// Tutup dropdown notifikasi saat klik di luar area atau tekan tombol ESC
document.addEventListener('click', function (e) {
    const notifContainer = document.getElementById('notifContainer');
    const notifDropdown = document.getElementById('notifDropdown');
    if (notifDropdown && !notifDropdown.classList.contains('hidden')) {
        if (notifContainer && !notifContainer.contains(e.target)) {
            notifDropdown.classList.add('hidden');
        }
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const notifDropdown = document.getElementById('notifDropdown');
        if (notifDropdown && !notifDropdown.classList.contains('hidden')) {
            notifDropdown.classList.add('hidden');
        }
    }
});

