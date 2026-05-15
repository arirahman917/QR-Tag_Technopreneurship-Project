<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Admin - TagMe</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-gradient { background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); }
        .main-bg { background: #f1f5f9; }
        .card-shadow { box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.1); }
        .tab-active { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; box-shadow: 0 4px 15px rgba(99,102,241,0.3); }
        .modal-overlay { background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
        .btn-primary { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
        .btn-primary:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); }
        .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .btn-success { background: linear-gradient(135deg, #10b981, #059669); }
        .btn-amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .table-row:hover { background: #f8fafc; }
        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .toast { animation: slideIn 0.3s ease-out, fadeOut 0.3s ease-in 2.7s forwards; }
        @keyframes slideIn { from { transform:translateX(100%); opacity:0; } to { transform:translateX(0); opacity:1; } }
        @keyframes fadeOut { to { opacity:0; transform:translateX(100%); } }
        .skeleton { background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; }
        @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
    </style>
</head>
<body class="main-bg min-h-screen">
    <!-- Top Navbar -->
    <nav class="bg-white border-b border-slate-200 px-4 md:px-6 py-3 flex items-center justify-between sticky top-0 z-40 card-shadow">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-500 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-800">TagMe Admin</h1>
                <p class="text-xs text-slate-400 hidden sm:block">Kelola Identitas & Generate QR</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-slate-600 hidden sm:inline">
                Halo, <strong>{{ session('admin_name', 'Admin') }}</strong>
            </span>
            <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 md:px-6 py-6">
        <!-- Category Tabs -->
        <div class="flex flex-wrap gap-2 mb-6">
            <button onclick="switchTab('pets')" id="tab-pets" class="tab-btn tab-active px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 flex items-center gap-2">
                <span>🐾</span> Hewan Peliharaan
            </button>
            <button onclick="switchTab('humans')" id="tab-humans" class="tab-btn px-5 py-2.5 rounded-xl text-sm font-semibold bg-white text-slate-600 hover:bg-slate-50 transition-all duration-300 flex items-center gap-2 card-shadow">
                <span>👤</span> Manusia
            </button>
            <button onclick="switchTab('items')" id="tab-items" class="tab-btn px-5 py-2.5 rounded-xl text-sm font-semibold bg-white text-slate-600 hover:bg-slate-50 transition-all duration-300 flex items-center gap-2 card-shadow">
                <span>🎒</span> Barang Pribadi
            </button>
        </div>

        <!-- Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <h2 id="section-title" class="text-xl font-bold text-slate-800">Data Hewan Peliharaan</h2>
            <button onclick="openAddModal()" class="btn-primary px-5 py-2.5 rounded-xl text-white text-sm font-semibold hover:shadow-lg transition-all duration-300 flex items-center gap-2 w-fit">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Data
            </button>
        </div>

        <!-- Data Table Container -->
        <div class="bg-white rounded-2xl card-shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full" id="data-table">
                    <thead id="table-head">
                        <!-- Dynamic header -->
                    </thead>
                    <tbody id="table-body">
                        <!-- Dynamic content -->
                    </tbody>
                </table>
            </div>
            <!-- Empty State -->
            <div id="empty-state" class="hidden p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
                <p class="text-slate-500 font-medium">Belum ada data</p>
                <p class="text-sm text-slate-400">Klik "Tambah Data" untuk menambahkan data baru.</p>
            </div>
            <!-- Loading State -->
            <div id="loading-state" class="p-6 space-y-3">
                <div class="skeleton h-12 rounded-lg"></div>
                <div class="skeleton h-12 rounded-lg"></div>
                <div class="skeleton h-12 rounded-lg"></div>
            </div>
        </div>
    </div>

    <!-- ==================== -->
    <!-- ADD / EDIT MODAL     -->
    <!-- ==================== -->
    <div id="form-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay">
        <div class="bg-white rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto animate-fade-in">
            <div class="flex items-center justify-between p-5 border-b border-slate-100">
                <h3 id="modal-title" class="text-lg font-bold text-slate-800">Tambah Data</h3>
                <button onclick="closeModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="data-form" class="p-5 space-y-4" enctype="multipart/form-data">
                <input type="hidden" id="edit-id" value="">
                <div id="form-fields">
                    <!-- Dynamic form fields -->
                </div>
                <div class="flex gap-3 pt-3">
                    <button type="button" onclick="closeModal()" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 btn-primary py-2.5 rounded-xl text-white text-sm font-semibold hover:shadow-lg transition-all duration-300">
                        <span id="submit-text">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== -->
    <!-- DELETE CONFIRM MODAL -->
    <!-- ==================== -->
    <div id="delete-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay">
        <div class="bg-white rounded-2xl w-full max-w-sm animate-fade-in p-6 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Konfirmasi Hapus</h3>
            <p class="text-sm text-slate-500 mb-6">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button onclick="confirmDelete()" class="flex-1 btn-danger py-2.5 rounded-xl text-white text-sm font-semibold hover:shadow-lg transition-all duration-300">
                    Hapus
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== -->
    <!-- QR CODE MODAL        -->
    <!-- ==================== -->
    <div id="qr-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay">
        <div class="bg-white rounded-2xl w-full max-w-md animate-fade-in shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-white shrink-0">
                <div class="text-left">
                    <h3 class="text-xl font-bold text-slate-800">Label Identitas</h3>
                    <p class="text-xs text-slate-500 mt-1">Generate QR Code (TagMe)</p>
                </div>
                <button onclick="closeQRModal()" class="p-2 rounded-lg bg-slate-50 hover:bg-red-50 hover:text-red-600 text-slate-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Loading State -->
            <div id="qr-loading" class="py-12 text-center shrink-0">
                <div class="w-10 h-10 mx-auto border-4 border-indigo-100 border-t-indigo-500 rounded-full animate-spin"></div>
                <p class="text-sm font-medium text-slate-500 mt-4">Memproses kode...</p>
            </div>

            <!-- Modal Content -->
            <div id="qr-content" class="hidden overflow-y-auto p-6">
                <!-- Color Selector -->
                <div class="mb-6 flex justify-center">
                    <div class="bg-slate-100 p-1 rounded-xl inline-flex shadow-inner">
                        <button onclick="switchQRColor('black')" id="btn-color-black" class="px-6 py-2 rounded-lg text-sm font-bold bg-white text-slate-800 shadow-sm transition-all">Hitam</button>
                        <button onclick="switchQRColor('white')" id="btn-color-white" class="px-6 py-2 rounded-lg text-sm font-bold text-slate-500 hover:text-slate-800 transition-all">Putih</button>
                    </div>
                </div>

                <div class="flex flex-col items-center justify-center gap-6">
                    <!-- QR Code Card -->
                    <div class="w-full bg-slate-50 rounded-xl p-5 border border-slate-200 flex flex-col items-center">
                        <div class="w-64 h-64 bg-slate-200 p-3 rounded-2xl border border-slate-300 shadow-inner flex items-center justify-center mb-5 overflow-hidden relative">
                            <img id="qr-image" src="" alt="QR Code" class="w-full h-full object-contain rounded-xl shadow-sm">
                        </div>
                        <h4 class="text-base font-bold text-slate-800 w-full text-center">QR Code</h4>
                        <p class="text-xs text-slate-500 mt-1 mb-6 break-all text-center px-2 line-clamp-2" id="qr-url"></p>
                        
                        <button onclick="downloadQR()" class="mt-auto w-full btn-success py-3 rounded-xl text-white text-sm font-bold hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Unduh QR
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 shrink-0 mt-auto hidden" id="qr-footer">
                <button onclick="closeQRModal()" class="w-full py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 text-sm font-bold hover:bg-slate-100 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-20 right-4 z-50 space-y-2"></div>

    @vite(['resources/js/app.js'])
    <script src="{{ asset('js/admin.js') }}"></script>
</body>
</html>
