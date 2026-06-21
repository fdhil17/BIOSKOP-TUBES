// ========== API BASE URLs ==========
const API = {
    member: 'http://localhost:8001/api',
    movie:  'http://localhost:8002/api',
    ticket: 'http://localhost:8003/api',
};

// ========== CACHE ==========
let cachedMovies = [];
let cachedMembers = [];

// ========== NAVBAR ==========
const navbar = document.getElementById('navbar');
const navToggle = document.getElementById('nav-toggle');
const navLinks = document.getElementById('nav-links');

window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 40);
});

navToggle.addEventListener('click', () => {
    navLinks.classList.toggle('open');
});

// Active nav link on scroll
const sections = document.querySelectorAll('section[id]');
const navLinkEls = document.querySelectorAll('.nav-link[data-section]');

window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(s => {
        if (window.scrollY >= s.offsetTop - 200) current = s.getAttribute('id');
    });
    navLinkEls.forEach(link => {
        link.classList.toggle('active', link.dataset.section === current);
    });
});

// Close mobile nav on link click
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => navLinks.classList.remove('open'));
});

// ========== TOAST ==========
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    const icons = { success: '✅', error: '❌', info: 'ℹ️' };
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<span>${icons[type] || ''}</span><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(40px)';
        toast.style.transition = '0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// ========== MODAL ==========
function openModal(id) {
    document.getElementById(id).classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    document.body.style.overflow = '';
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal(overlay.id);
    });
});

// ========== HELPER: Fetch with error handling ==========
async function apiFetch(url, options = {}) {
    try {
        const res = await fetch(url, {
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            ...options,
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || `HTTP ${res.status}`);
        return data;
    } catch (err) {
        if (err.message.includes('Failed to fetch') || err.message.includes('NetworkError')) {
            throw new Error('Tidak dapat terhubung ke server. Pastikan service berjalan.');
        }
        throw err;
    }
}

// ========== FORMAT ==========
function formatRupiah(num) {
    return 'Rp ' + Number(num).toLocaleString('id-ID');
}

function getGenreEmoji(genre) {
    const map = {
        action: '💥', drama: '🎭', comedy: '😂', horror: '👻', romance: '💕',
        thriller: '🔪', adventure: '🗺️', animation: '🎨', 'sci-fi': '🚀',
        fantasy: '🧙', mystery: '🔍', documentary: '📹',
    };
    const g = (genre || '').toLowerCase();
    for (const [key, emoji] of Object.entries(map)) {
        if (g.includes(key)) return emoji;
    }
    return '🎬';
}

// ========== MOVIES ==========
async function loadMovies() {
    const grid = document.getElementById('movies-grid');
    const emptyEl = document.getElementById('movies-empty');
    grid.innerHTML = '<div class="loading-skeleton"><div class="skeleton-card"></div><div class="skeleton-card"></div><div class="skeleton-card"></div></div>';
    emptyEl.style.display = 'none';

    try {
        const res = await apiFetch(`${API.movie}/movies`);
        cachedMovies = res.data || [];
        renderMovies(cachedMovies);
        document.getElementById('stat-movies').textContent = cachedMovies.length;
    } catch (err) {
        grid.innerHTML = '';
        emptyEl.style.display = 'block';
        emptyEl.querySelector('h3').textContent = 'Gagal Memuat Film';
        emptyEl.querySelector('p').textContent = err.message;
        showToast(err.message, 'error');
    }
}

function renderMovies(movies) {
    const grid = document.getElementById('movies-grid');
    const emptyEl = document.getElementById('movies-empty');

    if (!movies.length) {
        grid.innerHTML = '';
        emptyEl.style.display = 'block';
        return;
    }
    emptyEl.style.display = 'none';

    grid.innerHTML = movies.map(m => `
        <div class="movie-card" id="movie-card-${m.id}">
            <div class="movie-card-banner">
                <span class="movie-card-emoji">${getGenreEmoji(m.genre)}</span>
            </div>
            <div class="movie-card-body">
                <h3 class="movie-card-title">${escHtml(m.title)}</h3>
                <div class="movie-card-meta">
                    <div class="movie-meta-item">
                        <span class="movie-meta-icon">🎭</span>
                        <span>${escHtml(m.genre)}</span>
                    </div>
                    <div class="movie-meta-item">
                        <span class="movie-meta-icon">⏱️</span>
                        <span>${m.duration} menit</span>
                    </div>
                    <div class="movie-meta-item">
                        <span class="movie-meta-icon">🕐</span>
                        <span>${m.jam_tayang || '-'}</span>
                    </div>
                </div>
                <div class="movie-card-footer">
                    <span class="movie-price">${formatRupiah(m.price)}</span>
                    <span class="movie-seats">🪑 <span>${m.seat_available}</span> kursi</span>
                </div>
            </div>
        </div>
    `).join('');
}

function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function openMovieModal() {
    document.getElementById('movie-modal-title').textContent = 'Tambah Film Baru';
    document.getElementById('form-movie').reset();
    openModal('modal-movie');
}

async function submitMovie(e) {
    e.preventDefault();
    const form = e.target;
    const payload = {
        title: form.title.value,
        genre: form.genre.value,
        duration: parseInt(form.duration.value),
        jam_tayang: form.jam_tayang.value,
        seat_available: parseInt(form.seat_available.value),
        price: parseFloat(form.price.value),
    };

    try {
        await apiFetch(`${API.movie}/movies`, {
            method: 'POST',
            body: JSON.stringify(payload),
        });
        showToast('Film berhasil ditambahkan!', 'success');
        closeModal('modal-movie');
        loadMovies();
    } catch (err) {
        showToast('Gagal: ' + err.message, 'error');
    }
}

// ========== MEMBERS ==========
async function loadMembers() {
    const tbody = document.getElementById('members-tbody');
    const emptyEl = document.getElementById('members-empty');
    const tableWrapper = document.getElementById('members-table-wrapper');
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted);">Memuat data...</td></tr>';
    emptyEl.style.display = 'none';
    tableWrapper.style.display = '';

    try {
        const res = await apiFetch(`${API.member}/members`);
        cachedMembers = res.data || [];
        renderMembers(cachedMembers);
        document.getElementById('stat-members').textContent = cachedMembers.length;
    } catch (err) {
        tbody.innerHTML = '';
        tableWrapper.style.display = 'none';
        emptyEl.style.display = 'block';
        emptyEl.querySelector('h3').textContent = 'Gagal Memuat Member';
        emptyEl.querySelector('p').textContent = err.message;
        showToast(err.message, 'error');
    }
}

function renderMembers(members) {
    const tbody = document.getElementById('members-tbody');
    const emptyEl = document.getElementById('members-empty');
    const tableWrapper = document.getElementById('members-table-wrapper');

    if (!members.length) {
        tableWrapper.style.display = 'none';
        emptyEl.style.display = 'block';
        return;
    }
    emptyEl.style.display = 'none';
    tableWrapper.style.display = '';

    tbody.innerHTML = members.map(m => `
        <tr id="member-row-${m.id}">
            <td><strong>#${m.id}</strong></td>
            <td>${escHtml(m.name)}</td>
            <td>${escHtml(m.email)}</td>
            <td>${escHtml(m.phone || '-')}</td>
            <td>
                <div style="display:flex;gap:8px;">
                    <button class="btn btn-glass btn-sm" onclick="editMember(${m.id})" title="Edit">✏️ Edit</button>
                    <button class="btn btn-success btn-sm" onclick="viewMemberTickets(${m.id})" title="Lihat Tiket">🎫 Tiket</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function openMemberModal() {
    document.getElementById('member-modal-title').textContent = 'Tambah Member Baru';
    document.getElementById('form-member').reset();
    document.getElementById('member-edit-id').value = '';
    openModal('modal-member');
}

function editMember(id) {
    const member = cachedMembers.find(m => m.id === id);
    if (!member) return;
    document.getElementById('member-modal-title').textContent = 'Edit Member';
    document.getElementById('member-edit-id').value = id;
    document.getElementById('member-name').value = member.name;
    document.getElementById('member-email').value = member.email;
    document.getElementById('member-phone').value = member.phone || '';
    document.getElementById('member-address').value = member.address || '';
    openModal('modal-member');
}

async function submitMember(e) {
    e.preventDefault();
    const form = e.target;
    const editId = document.getElementById('member-edit-id').value;
    const payload = {
        name: form.name.value,
        email: form.email.value,
        phone: form.phone.value,
        address: form.address.value,
    };

    try {
        if (editId) {
            await apiFetch(`${API.member}/members/${editId}`, {
                method: 'PUT',
                body: JSON.stringify(payload),
            });
            showToast('Member berhasil diupdate!', 'success');
        } else {
            await apiFetch(`${API.member}/members`, {
                method: 'POST',
                body: JSON.stringify(payload),
            });
            showToast('Member berhasil ditambahkan!', 'success');
        }
        closeModal('modal-member');
        loadMembers();
    } catch (err) {
        showToast('Gagal: ' + err.message, 'error');
    }
}

async function viewMemberTickets(memberId) {
    try {
        const res = await apiFetch(`${API.member}/members/${memberId}/tickets`);
        const data = res.data;
        const tickets = data?.tickets?.data || [];
        if (!tickets.length) {
            showToast('Member ini belum memiliki tiket', 'info');
            return;
        }
        // Scroll to tickets section and show info
        document.getElementById('tickets').scrollIntoView({ behavior: 'smooth' });
        showToast(`Ditemukan ${tickets.length} tiket untuk member #${memberId}`, 'success');
    } catch (err) {
        showToast('Gagal: ' + err.message, 'error');
    }
}

// ========== TICKETS ==========
async function loadTickets() {
    const tbody = document.getElementById('tickets-tbody');
    const emptyEl = document.getElementById('tickets-empty');
    const tableWrapper = document.getElementById('tickets-table-wrapper');
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">Memuat data...</td></tr>';
    emptyEl.style.display = 'none';
    tableWrapper.style.display = '';

    try {
        const res = await apiFetch(`${API.ticket}/tickets`);
        const tickets = res.data || [];
        renderTickets(tickets);
        document.getElementById('stat-tickets').textContent = tickets.filter(t => t.status === 'booked').length;
    } catch (err) {
        tbody.innerHTML = '';
        tableWrapper.style.display = 'none';
        emptyEl.style.display = 'block';
        emptyEl.querySelector('h3').textContent = 'Gagal Memuat Tiket';
        emptyEl.querySelector('p').textContent = err.message;
        showToast(err.message, 'error');
    }
}

function renderTickets(tickets) {
    const tbody = document.getElementById('tickets-tbody');
    const emptyEl = document.getElementById('tickets-empty');
    const tableWrapper = document.getElementById('tickets-table-wrapper');

    if (!tickets.length) {
        tableWrapper.style.display = 'none';
        emptyEl.style.display = 'block';
        return;
    }
    emptyEl.style.display = 'none';
    tableWrapper.style.display = '';

    tbody.innerHTML = tickets.map(t => `
        <tr id="ticket-row-${t.id}">
            <td><strong>#${t.id}</strong></td>
            <td>#${t.member_id}</td>
            <td>#${t.movie_id}</td>
            <td>${t.quantity}</td>
            <td>${formatRupiah(t.total_price)}</td>
            <td>
                <span class="status-badge status-${t.status}">
                    ${t.status === 'booked' ? '✅ Booked' : '❌ Cancelled'}
                </span>
            </td>
            <td>
                ${t.status === 'booked' ? `<button class="btn btn-danger btn-sm" onclick="cancelTicket(${t.id})">Batalkan</button>` : '<span style="color:var(--text-muted);">—</span>'}
            </td>
        </tr>
    `).join('');
}

function openTicketModal() {
    document.getElementById('form-ticket').reset();
    document.getElementById('ticket-price-preview').style.display = 'none';

    // Populate member select
    const memberSelect = document.getElementById('ticket-member');
    memberSelect.innerHTML = '<option value="">-- Pilih Member --</option>';
    cachedMembers.forEach(m => {
        memberSelect.innerHTML += `<option value="${m.id}">${m.name} (#${m.id})</option>`;
    });

    // Populate movie select
    const movieSelect = document.getElementById('ticket-movie');
    movieSelect.innerHTML = '<option value="">-- Pilih Film --</option>';
    cachedMovies.forEach(m => {
        movieSelect.innerHTML += `<option value="${m.id}" data-price="${m.price}">${m.title} (${m.seat_available} kursi)</option>`;
    });

    openModal('modal-ticket');
}

// Price preview
document.getElementById('ticket-movie')?.addEventListener('change', updatePricePreview);
document.getElementById('ticket-qty')?.addEventListener('input', updatePricePreview);

function updatePricePreview() {
    const movieSelect = document.getElementById('ticket-movie');
    const qty = parseInt(document.getElementById('ticket-qty').value) || 0;
    const selected = movieSelect.options[movieSelect.selectedIndex];
    const previewEl = document.getElementById('ticket-price-preview');

    if (selected && selected.dataset.price && qty > 0) {
        const unitPrice = parseFloat(selected.dataset.price);
        document.getElementById('preview-unit-price').textContent = formatRupiah(unitPrice);
        document.getElementById('preview-total-price').textContent = formatRupiah(unitPrice * qty);
        previewEl.style.display = 'block';
    } else {
        previewEl.style.display = 'none';
    }
}

async function submitTicket(e) {
    e.preventDefault();
    const form = e.target;
    const payload = {
        member_id: parseInt(form.member_id.value),
        movie_id: parseInt(form.movie_id.value),
        quantity: parseInt(form.quantity.value),
    };

    try {
        await apiFetch(`${API.ticket}/tickets`, {
            method: 'POST',
            body: JSON.stringify(payload),
        });
        showToast('Tiket berhasil dipesan! 🎉', 'success');
        closeModal('modal-ticket');
        loadTickets();
        loadMovies(); // refresh seat count
    } catch (err) {
        showToast('Gagal: ' + err.message, 'error');
    }
}

async function cancelTicket(id) {
    if (!confirm('Yakin ingin membatalkan tiket ini?')) return;
    try {
        await apiFetch(`${API.ticket}/tickets/${id}/cancel`, { method: 'PATCH' });
        showToast('Tiket berhasil dibatalkan', 'success');
        loadTickets();
        loadMovies();
    } catch (err) {
        showToast('Gagal: ' + err.message, 'error');
    }
}

// ========== ANIMATED COUNTER ==========
function animateCounter(el, target) {
    let current = 0;
    const duration = 1500;
    const step = Math.max(1, Math.floor(target / (duration / 30)));
    const timer = setInterval(() => {
        current += step;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        el.textContent = current;
    }, 30);
}

// ========== INIT ==========
document.addEventListener('DOMContentLoaded', () => {
    loadMovies();
    loadMembers();
    loadTickets();
});
