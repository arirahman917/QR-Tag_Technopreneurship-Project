/**
 * TagQR Admin Dashboard JavaScript
 * Handles CRUD operations, tab switching, QR generation
 */

// ==================== STATE ====================
let currentCategory = 'pets';
let currentData = [];
let deleteTargetId = null;
let currentQRToken = '';

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

const categoryConfig = {
    pets: {
        title: 'Data Hewan Peliharaan',
        headers: ['Foto', 'Nama', 'Jenis & Ras', 'Kondisi', 'Kontak', 'Aksi'],
        requiredFields: [
            { name: 'name', label: 'Nama Hewan', type: 'text', placeholder: 'Contoh: Luna' },
            { name: 'photo', label: 'Foto Hewan', type: 'file' },
            { name: 'species', label: 'Jenis Hewan', type: 'text', placeholder: 'Contoh: Kucing' },
            { name: 'breed', label: 'Ras', type: 'text', placeholder: 'Contoh: Anggora' },
            { name: 'condition', label: 'Kondisi Penting', type: 'text', placeholder: 'Contoh: Sudah divaksin 2 kali / cacingan / flu kucing' },
            { name: 'emergency_message', label: 'Pesan Bantuan', type: 'textarea', placeholder: 'Contoh: Kucing saya mungkin hilang, mohon hubungi pemilik' },
            { name: 'contact_phone', label: 'Nomor Kontak (WhatsApp)', type: 'text', placeholder: 'Contoh: 08123456789' },
        ],
        optionalFields: [
            { name: 'color', label: 'Warna', type: 'text', placeholder: 'Contoh: Putih Abu' },
            { name: 'distinctive_mark', label: 'Ciri Khas', type: 'text', placeholder: 'Contoh: Bercak hitam di telinga kiri' },
            { name: 'age', label: 'Usia Perkiraan', type: 'text', placeholder: 'Contoh: 2 tahun' },
            { name: 'nickname', label: 'Nama Panggilan yang Dikenali', type: 'text', placeholder: 'Contoh: Unyil' },
            { name: 'behavior_notes', label: 'Catatan Perilaku', type: 'textarea', placeholder: 'Contoh: Jinak, suka dielus' },
            { name: 'owner_area', label: 'Lokasi Pemilik (Area Umum)', type: 'text', placeholder: 'Contoh: Bogor Utara' },
        ]
    },
    humans: {
        title: 'Data Manusia',
        headers: ['Foto', 'Nama', 'Kondisi', 'Pesan', 'Kontak', 'Aksi'],
        requiredFields: [
            { name: 'name', label: 'Nama Panggilan', type: 'text', placeholder: 'Contoh: Kakek Budi' },
            { name: 'photo', label: 'Foto Jelas', type: 'file' },
            { name: 'condition', label: 'Kondisi', type: 'text', placeholder: 'Contoh: Demensia / Autisme' },
            { name: 'emergency_message', label: 'Pesan Bantuan', type: 'textarea', placeholder: 'Contoh: Mohon bantu antar saya ke alamat rumah' },
            { name: 'contact_phone', label: 'Nomor Kontak Keluarga (WhatsApp)', type: 'text', placeholder: 'Contoh: 08123456789' },
        ],
        optionalFields: [
            { name: 'age', label: 'Usia Perkiraan', type: 'text', placeholder: 'Contoh: 78 tahun' },
            { name: 'physical_description', label: 'Ciri Fisik Singkat', type: 'text', placeholder: 'Contoh: Tinggi sedang, rambut putih' },
            { name: 'languages', label: 'Bahasa yang Dipahami', type: 'text', placeholder: 'Contoh: Bahasa Indonesia, Sunda / Isyarat' },
            { name: 'general_area', label: 'Asal Lokasi (Area Umum)', type: 'text', placeholder: 'Contoh: Bogor Utara' },
            { name: 'notes', label: 'Catatan Bantu', type: 'textarea', placeholder: 'Contoh: Sering lupa jalan pulang' },
        ]
    },
    items: {
        title: 'Data Barang Pribadi',
        headers: ['Foto', 'Nama Barang', 'Deskripsi', 'Pesan', 'Kontak', 'Aksi'],
        requiredFields: [
            { name: 'item_name', label: 'Nama Barang', type: 'text', placeholder: 'Contoh: Tas Ransel Hitam' },
            { name: 'photo', label: 'Foto Barang', type: 'file' },
            { name: 'description', label: 'Deskripsi Singkat', type: 'text', placeholder: 'Contoh: Tas ransel merk Eiger warna hitam' },
            { name: 'emergency_message', label: 'Pesan', type: 'textarea', placeholder: 'Contoh: Jika menemukan, mohon segera hubungi pemilik' },
            { name: 'contact_phone', label: 'Nomor Kontak Pemilik (WhatsApp)', type: 'text', placeholder: 'Contoh: 08123456789' },
        ],
        optionalFields: [
            { name: 'distinctive_mark', label: 'Ciri Unik', type: 'text', placeholder: 'Contoh: Goresan di ritsleting kanan' },
            { name: 'owner_area', label: 'Lokasi Pemilik (Area Umum)', type: 'text', placeholder: 'Contoh: Bogor Barat' },
            { name: 'reward', label: 'Reward', type: 'text', placeholder: 'Contoh: Nanti dikasih imbalan' },
            { name: 'important_contents', label: 'Catatan Isi Penting', type: 'text', placeholder: 'Contoh: Berisi dokumen penting' },
        ]
    }
};

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', () => {
    loadData();
    document.getElementById('data-form').addEventListener('submit', handleFormSubmit);
});

// ==================== TAB SWITCHING ====================
function switchTab(category) {
    currentCategory = category;
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('tab-active');
        btn.classList.add('bg-white', 'text-slate-600', 'card-shadow');
    });
    const activeBtn = document.getElementById('tab-' + category);
    activeBtn.classList.add('tab-active');
    activeBtn.classList.remove('bg-white', 'text-slate-600', 'card-shadow');

    document.getElementById('section-title').textContent = categoryConfig[category].title;
    loadData();
}

// ==================== DATA LOADING ====================
async function loadData() {
    const tableHead = document.getElementById('table-head');
    const tableBody = document.getElementById('table-body');
    const emptyState = document.getElementById('empty-state');
    const loadingState = document.getElementById('loading-state');

    // Show loading
    tableHead.innerHTML = '';
    tableBody.innerHTML = '';
    emptyState.classList.add('hidden');
    loadingState.classList.remove('hidden');

    try {
        const response = await fetch(`/admin/api/${currentCategory}`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        currentData = data;

        loadingState.classList.add('hidden');

        if (data.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        }

        // Build table header
        const config = categoryConfig[currentCategory];
        tableHead.innerHTML = `<tr class="bg-slate-50 border-b border-slate-100">${config.headers.map(h => `<th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">${h}</th>`).join('')}</tr>`;

        // Build table rows
        tableBody.innerHTML = data.map(record => buildTableRow(record)).join('');

    } catch (error) {
        loadingState.classList.add('hidden');
        showToast('Gagal memuat data: ' + error.message, 'error');
    }
}

function buildTableRow(record) {
    // Build photo URL from GridFS photo_id
    const photoId = record.photo_id || '';
    const photoUrl = photoId ? `/file/${photoId}` : '';
    const photoImg = photoUrl
        ? `<img src="${photoUrl}" class="w-10 h-10 rounded-lg object-cover" alt="foto" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 40 40%22><rect fill=%22%23e2e8f0%22 width=%2240%22 height=%2240%22/><text x=%2220%22 y=%2224%22 text-anchor=%22middle%22 fill=%22%2394a3b8%22 font-size=%2212%22>?</text></svg>'">`
        : `<div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 text-xs">N/A</div>`;

    let cols = '';
    if (currentCategory === 'pets') {
        cols = `
            <td class="px-4 py-3">${photoImg}</td>
            <td class="px-4 py-3 font-medium text-slate-800 text-sm">${esc(record.name || '')}</td>
            <td class="px-4 py-3 text-sm text-slate-600">${esc(record.species || '')} ${esc(record.breed || '')}</td>
            <td class="px-4 py-3 text-sm text-slate-600">${esc(record.condition || '')}</td>
            <td class="px-4 py-3 text-sm text-slate-600">${esc(record.contact_phone || '')}</td>
        `;
    } else if (currentCategory === 'humans') {
        cols = `
            <td class="px-4 py-3">${photoImg}</td>
            <td class="px-4 py-3 font-medium text-slate-800 text-sm">${esc(record.name || '')}</td>
            <td class="px-4 py-3 text-sm text-slate-600">${esc(record.condition || '')}</td>
            <td class="px-4 py-3 text-sm text-slate-600 max-w-[200px] truncate">${esc(record.emergency_message || '')}</td>
            <td class="px-4 py-3 text-sm text-slate-600">${esc(record.contact_phone || '')}</td>
        `;
    } else {
        cols = `
            <td class="px-4 py-3">${photoImg}</td>
            <td class="px-4 py-3 font-medium text-slate-800 text-sm">${esc(record.item_name || '')}</td>
            <td class="px-4 py-3 text-sm text-slate-600 max-w-[200px] truncate">${esc(record.description || '')}</td>
            <td class="px-4 py-3 text-sm text-slate-600 max-w-[200px] truncate">${esc(record.emergency_message || '')}</td>
            <td class="px-4 py-3 text-sm text-slate-600">${esc(record.contact_phone || '')}</td>
        `;
    }

    return `<tr class="table-row border-b border-slate-50 transition-colors">
        ${cols}
        <td class="px-4 py-3">
            <div class="flex items-center gap-1.5">
                <button onclick="openEditModal('${record._id}')" class="p-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button onclick="openDeleteModal('${record._id}')" class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
                <button onclick="generateQR('${record._id}')" class="p-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors" title="Generate QR">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </button>
            </div>
        </td>
    </tr>`;
}

// ==================== MODAL: ADD ====================
function openAddModal() {
    document.getElementById('edit-id').value = '';
    document.getElementById('modal-title').textContent = 'Tambah Data';
    document.getElementById('submit-text').textContent = 'Simpan';
    buildFormFields();
    showModal('form-modal');
}

// ==================== MODAL: EDIT ====================
function openEditModal(id) {
    const record = currentData.find(r => r._id === id);
    if (!record) return;

    document.getElementById('edit-id').value = id;
    document.getElementById('modal-title').textContent = 'Edit Data';
    document.getElementById('submit-text').textContent = 'Perbarui';
    buildFormFields(record);
    showModal('form-modal');
}

// ==================== BUILD FORM FIELDS ====================
function buildFormFields(record = null) {
    const config = categoryConfig[currentCategory];
    const allFields = [...config.requiredFields, ...config.optionalFields];
    const container = document.getElementById('form-fields');

    let html = '';

    // Separator for optional fields
    const requiredCount = config.requiredFields.length;

    allFields.forEach((field, index) => {
        if (index === requiredCount) {
            html += `<div class="border-t border-slate-100 pt-4 mt-4"><p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Data Opsional</p></div>`;
        }

        const value = record ? (record[field.name] || '') : '';
        const required = index < requiredCount && field.type !== 'file' ? 'required' : '';
        const requiredBadge = index < requiredCount ? '<span class="text-red-400 ml-1">*</span>' : '';

        if (field.type === 'file') {
            html += `<div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">${field.label}${requiredBadge}</label>
                ${record && record.photo ? `<p class="text-xs text-slate-400 mb-1">File saat ini: ${record.photo}</p>` : ''}
                <input type="file" name="${field.name}" accept="image/*" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition-all">
            </div>`;
        } else if (field.type === 'textarea') {
            html += `<div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">${field.label}${requiredBadge}</label>
                <textarea name="${field.name}" ${required} rows="3" placeholder="${field.placeholder || ''}" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition-all resize-none">${esc(value)}</textarea>
            </div>`;
        } else {
            html += `<div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">${field.label}${requiredBadge}</label>
                <input type="${field.type}" name="${field.name}" value="${esc(value)}" ${required} placeholder="${field.placeholder || ''}" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition-all">
            </div>`;
        }
    });

    container.innerHTML = html;
}

// ==================== FORM SUBMIT ====================
async function handleFormSubmit(e) {
    e.preventDefault();

    const editId = document.getElementById('edit-id').value;
    const isEdit = !!editId;
    const formData = new FormData(e.target);

    const url = isEdit
        ? `/admin/api/${currentCategory}/${editId}`
        : `/admin/api/${currentCategory}`;

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            closeModal();
            loadData();
        } else {
            showToast(result.error || 'Terjadi kesalahan', 'error');
        }
    } catch (error) {
        showToast('Gagal menyimpan: ' + error.message, 'error');
    }
}

// ==================== DELETE ====================
function openDeleteModal(id) {
    deleteTargetId = id;
    showModal('delete-modal');
}

function closeDeleteModal() {
    deleteTargetId = null;
    hideModal('delete-modal');
}

async function confirmDelete() {
    if (!deleteTargetId) return;

    try {
        const response = await fetch(`/admin/api/${currentCategory}/${deleteTargetId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        });

        const result = await response.json();
        if (result.success) {
            showToast(result.message, 'success');
            loadData();
        } else {
            showToast(result.error || 'Gagal menghapus', 'error');
        }
    } catch (error) {
        showToast('Gagal menghapus: ' + error.message, 'error');
    }

    closeDeleteModal();
}

// ==================== QR CODE ====================
let currentQRId = null;
let currentQRColor = 'black';

async function generateQR(id) {
    currentQRId = id;
    showModal('qr-modal');
    updateColorButtons();
    
    document.getElementById('qr-loading').classList.remove('hidden');
    document.getElementById('qr-content').classList.add('hidden');

    try {
        const response = await fetch(`/admin/api/${currentCategory}/${id}/qr?color=${currentQRColor}`, {
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        if (result.success) {
            document.getElementById('qr-image').src = result.qr_image;
            document.getElementById('qr-url').textContent = result.url;
            currentQRToken = result.qr_token;
            
            document.getElementById('qr-loading').classList.add('hidden');
            document.getElementById('qr-content').classList.remove('hidden');
            const footer = document.getElementById('qr-footer');
            if(footer) footer.classList.remove('hidden');
        } else {
            showToast(result.error || 'Gagal generate QR Code', 'error');
            closeQRModal();
        }
    } catch (error) {
        showToast('Gagal generate QR Code: ' + error.message, 'error');
        closeQRModal();
    }
}

function switchQRColor(color) {
    if (color === currentQRColor) return;
    currentQRColor = color;
    if (currentQRId) {
        generateQR(currentQRId);
    }
}

function updateColorButtons() {
    const btnBlack = document.getElementById('btn-color-black');
    const btnWhite = document.getElementById('btn-color-white');
    if (!btnBlack || !btnWhite) return;

    if (currentQRColor === 'black') {
        btnBlack.className = 'px-6 py-2 rounded-lg text-sm font-bold bg-white text-slate-800 shadow-sm transition-all';
        btnWhite.className = 'px-6 py-2 rounded-lg text-sm font-bold text-slate-500 hover:text-slate-800 transition-all';
    } else {
        btnWhite.className = 'px-6 py-2 rounded-lg text-sm font-bold bg-white text-slate-800 shadow-sm transition-all';
        btnBlack.className = 'px-6 py-2 rounded-lg text-sm font-bold text-slate-500 hover:text-slate-800 transition-all';
    }
}

function downloadQR() {
    const img = document.getElementById('qr-image');
    downloadImageAsJPG(img, currentQRToken + '_' + currentQRColor.toUpperCase() + '_QR.jpg');
}

function downloadImageAsJPG(img, filename) {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    const tempImg = new Image();
    tempImg.crossOrigin = 'anonymous';
    tempImg.onload = function() {
        canvas.width = tempImg.naturalWidth;
        canvas.height = tempImg.naturalHeight;
        // White background for JPG
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(tempImg, 0, 0);

        canvas.toBlob(function(blob) {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }, 'image/jpeg', 0.95);
    };
    tempImg.src = img.src;
}

function closeQRModal() {
    hideModal('qr-modal');
}

// ==================== MODAL HELPERS ====================
function showModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function hideModal(id) {
    const modal = document.getElementById(id);
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function closeModal() {
    hideModal('form-modal');
    document.getElementById('data-form').reset();
}

// ==================== TOAST NOTIFICATIONS ====================
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    const colors = {
        success: 'bg-emerald-500',
        error: 'bg-red-500',
        info: 'bg-indigo-500'
    };
    const icons = {
        success: '✓',
        error: '✕',
        info: 'ℹ'
    };

    const toast = document.createElement('div');
    toast.className = `toast flex items-center gap-3 px-4 py-3 rounded-xl text-white text-sm font-medium shadow-lg ${colors[type]}`;
    toast.innerHTML = `<span class="text-lg">${icons[type]}</span> ${esc(message)}`;
    container.appendChild(toast);

    setTimeout(() => toast.remove(), 3000);
}

// ==================== UTILITY ====================
function esc(str) {
    if (str === null || str === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
}
