# 🚀 MikroTik AI

Aplikasi berbasis **Laravel 11**, **Python (RouterOS API)**, dan **Google Gemini AI** yang dirancang untuk mendiagnosis masalah jaringan pada router MikroTik secara otomatis serta mengeksekusi perbaikan (_auto-remediation_) secara _real-time_.
routing

---

## 🌟 Fitur Utama

- **Dual-Mode Input Konfigurasi:**
    - **Upload File `.rsc`:** Parser otomatis (`RscParserService`) menyaring baris-baris penting (`/ip address`, `/ip route`, `/ip firewall`, `/ip dhcp`, dll) dan mengabaikan baris sensitif/non-esensial.
    - **Direct RouterOS API:** Login langsung ke router MikroTik via port API (`8728`) untuk mengambil data konfigurasi secara _live_.
- **Diagnosis Jaringan Berbasis AI:** Mengirimkan data konfigurasi beserta deskripsi kendala pengguna ke Google Gemini AI untuk analisis masalah (IP bentrok, NAT mati, salah gateway, dll).
- **Auto-Remediation (Eksekusi Otomatis):** Gemini AI menghasilkan output JSON terstruktur yang dapat diterapkan langsung ke router MikroTik secara _real-time_ hanya dengan satu kali klik.

---

## 🛠️ Tech Stack

- **Backend Framework:** Laravel 11 (PHP 8.2+)
- **Frontend Layer:** Blade Templating + Tailwind CSS
- **API Helper Engine:** Python 3 (Library `routeros-api`)
- **AI Diagnostic Engine:** Google Gemini API (`gemini-3.6-flash`)

---

## 📋 Prasyarat System

Pastikan perangkat/server kamu telah terinstall:

1. **PHP >= 8.2** & **Composer**
2. **Python >= 3.9** & **Pip**
3. Akses ke router MikroTik dengan service `api` yang aktif (Port 8728)

---

## ⚙️ Cara Instalasi & Konfigurasi

### 1. Clone Repositori & Install Dependency PHP

```bash
git clone https://github.com/knardd/AI-Mikrotik.git
cd AI-Mikrotik
composer install
```

### 2. Install Library Python

Jalankan perintah berikut untuk menginstall library RouterOS API Python:

```bash
pip install routeros-api
```

### 3. Konfigurasi Environment (`.env`)

Salin file `.env.example` ke `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

Buka file `.env` dan tambahkan kredensial Google Gemini API:

```ini
GEMINI_API_KEY=AIzaSy...IsiApiKeyKamu
GEMINI_API_URL=https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent
```

### 4. Buat Folder & Helper Python

Pastikan struktur folder `python_helpers/` dan skrip pendukung sudah tersedia di root project:

- `python_helpers/fetch_mikrotik.py` (Untuk fetch data dari MikroTik)
- `python_helpers/apply_mikrotik.py` (Untuk apply perintah perbaikan ke MikroTik)

---

## 📂 Struktur Project Utama

```text
AI-Mikrotik/
├── app/
│   ├── Http/Controllers/
│   │   └── RouterAnalysisController.php   # Handlers untuk file/API analysis & auto-fix
│   └── Services/
│       ├── GeminiService.php             # Integrasi HTTP Client ke Gemini API
│       └── RscParserService.php           # Sanitasi & filter baris penting file .rsc
├── python_helpers/
│   ├── fetch_mikrotik.py                 # Fetch live data via RouterOS API (Socket)
│   └── apply_mikrotik.py                 # Eksekusi JSON perbaikan ke MikroTik
├── resources/views/
│   └── dashboard.blade.php               # UI multi-tab (Upload vs Direct API Login)
└── routes/
    └── web.php                           # Application routes
```

---

## 🚀 Cara Menjalankan Aplikasi

1. Jalankan Server Laravel Development:
    ```bash
    php artisan serve
    ```
2. Buka browser dan akses `http://localhost:8000`.
3. Pilih metode analisis:
    - **Upload File `.rsc`:** Masukkan file export router beserta keluhan masalah.
    - **Direct RouterOS API:** Masukkan IP, Username, Password, dan Port API router (8728).
4. Klik **Analisa**, lalu tinjau diagnosis dari AI.
5. Klik **⚡ Terapkan Perbaikan Otomatis ke Router** untuk mengeksekusi solusi perbaikan ke MikroTik secara langsung.

---

## 🛡️ Keamanan & Catatan MikroTik

- Pastikan service API di MikroTik sudah aktif melalui Winbox: `IP -> Services -> api` (Port 8728 enabled).
- Disarankan menggunakan akun MikroTik khusus dengan hak akses (_group permission_) `read` & `write` sesuai kebutuhan.
- Penggunaan `disabled=yes` pada baris konfigurasi diperhatikan secara khusus oleh AI engine untuk mendeteksi rule non-aktif.

---

## 📄 Lisensi

Project ini dibuat untuk tujuan pembelajaran dan pengembangan open-source di bawah lisensi **MIT License**.
