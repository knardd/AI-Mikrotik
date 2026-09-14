<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI MikroTik</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-6">
    <div class="max-w-4xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="border-b border-slate-700 pb-4">
            <h1 class="text-2xl font-bold text-sky-400">AI MikroTik</h1>
            <p class="text-slate-400 text-sm">Analisis masalah konfigurasi MikroTik via Upload File .rsc atau Direct RouterOS API.</p>
        </div>

        <!-- Notification Flash Messages -->
        @if(session('error'))
            <div class="bg-rose-500/10 border border-rose-500 text-rose-400 p-3 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500 text-emerald-400 p-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Main Card with Tabs -->
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 space-y-5">
            
            <!-- Tab Buttons -->
            <div class="flex border-b border-slate-700 gap-4 text-sm font-medium">
                <button type="button" id="tab-file-btn" onclick="switchTab('file')" class="pb-2 text-sky-400 border-b-2 border-sky-400 focus:outline-none">
                    📁 Upload File .rsc
                </button>
                <button type="button" id="tab-api-btn" onclick="switchTab('api')" class="pb-2 text-slate-400 hover:text-slate-200 focus:outline-none">
                    🔌 Direct RouterOS API
                </button>
            </div>

            <!-- TAB 1: Upload File .rsc Form -->
            <div id="tab-file" class="block">
                <form action="{{ route('rsc.analyze') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">File Konfigurasi (.rsc):</label>
                        <input type="file" name="rsc_file" required class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-slate-700 file:text-slate-200 hover:file:bg-slate-600 transition cursor-pointer">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Apa kendala/masalahnya?</label>
                        <textarea name="problem_description" rows="3" placeholder="Contoh: Internet di PC LAN mati total, tetapi di Wi-Fi lancar..." class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-sm text-slate-200 focus:outline-none focus:border-sky-500 transition"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-sky-600 hover:bg-sky-500 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                        Analisa File .rsc
                    </button>
                </form>
            </div>

            <!-- TAB 2: RouterOS API Direct Login Form -->
            <div id="tab-api" class="hidden">
                <form action="{{ route('api.analyze') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">IP Address / Host Router:</label>
                            <input type="text" name="host" required placeholder="192.168.88.1" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-sm text-slate-200 focus:outline-none focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Username API:</label>
                            <input type="text" name="username" required placeholder="admin" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-sm text-slate-200 focus:outline-none focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Password:</label>
                            <input type="password" name="password" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-sm text-slate-200 focus:outline-none focus:border-sky-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Port API (Default: 8728):</label>
                        <input type="number" name="port" value="8728" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-sm text-slate-200 focus:outline-none focus:border-sky-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Apa kendala/masalahnya?</label>
                        <textarea name="problem_description" rows="3" placeholder="Contoh: Interface ether2 mati tidak bisa alokasi DHCP..." class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-sm text-slate-200 focus:outline-none focus:border-sky-500 transition"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                        Konek & Analisa Live Router
                    </button>
                </form>
            </div>

        </div>

        <!-- Hasil Analisis AI & Tombol Eksekusi -->
        @if(session('ai_result_data'))
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 space-y-4">
            <h2 class="text-lg font-semibold text-emerald-400">Hasil Analisis & Diagnosis AI</h2>
            
            <!-- Penjelasan AI -->
            <div class="bg-slate-900 p-4 rounded-lg text-sm whitespace-pre-wrap border border-slate-700 leading-relaxed text-slate-200">
                {{ session('ai_result_data')['analysis'] ?? 'Tidak ada penjelasan.' }}
            </div>

            <!-- Jika ada rekomendasi command perbaikan -->
            @if(!empty(session('ai_result_data')['api_commands']))
            <div class="border-t border-slate-700 pt-4 space-y-3">
                <h3 class="text-sm font-semibold text-sky-400">Rekomendasi Perintah Script MikroTik:</h3>
                
                <!-- Menampilkan Teks Skrip CLI MikroTik Murni -->
                <pre class="bg-slate-950 p-4 rounded-lg text-xs font-mono text-emerald-400 border border-slate-800 overflow-x-auto whitespace-pre-wrap">{{ session('ai_result_data')['cli_script'] }}</pre>

                <!-- Form Eksekusi Langsung (JSON Tetap Terkirim di Background) -->
                <form action="{{ route('api.apply') }}" method="POST">
                    @csrf
                    <input type="hidden" name="commands_json" value="{{ json_encode(session('ai_result_data')['api_commands']) }}">
                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menerapkan script ini ke MikroTik secara langsung?')" class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition flex items-center gap-2">
                        ⚡ Terapkan Perbaikan Otomatis ke Router
                    </button>
                </form>
            </div>
            @endif
        </div>
        @endif

    </div>

    <!-- Script Pembantu Switch Tab (Vanilla JS) -->
    <script>
        function switchTab(tabName) {
            const tabFile = document.getElementById('tab-file');
            const tabApi = document.getElementById('tab-api');
            const btnFile = document.getElementById('tab-file-btn');
            const btnApi = document.getElementById('tab-api-btn');

            if (tabName === 'file') {
                tabFile.classList.remove('hidden');
                tabApi.classList.add('hidden');
                btnFile.className = "pb-2 text-sky-400 border-b-2 border-sky-400 focus:outline-none";
                btnApi.className = "pb-2 text-slate-400 hover:text-slate-200 focus:outline-none";
            } else {
                tabFile.classList.add('hidden');
                tabApi.classList.remove('hidden');
                btnApi.className = "pb-2 text-sky-400 border-b-2 border-sky-400 focus:outline-none";
                btnFile.className = "pb-2 text-slate-400 hover:text-slate-200 focus:outline-none";
            }
        }
    </script>
</body>
</html>