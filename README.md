# JARA — Advanced To-Do List

Aplikasi web untuk mengelola tugas pribadi maupun tim secara efisien. JARA memungkinkan pengguna untuk membuat, mengelompokkan, dan mengatur tugas ke dalam berbagai proyek, menetapkan prioritas dan tenggat waktu, berkolaborasi dengan pengguna lain, serta memantau progres penyelesaian tugas secara real-time. Sistem ini juga dilengkapi dengan manajemen akun berbasis peran (Admin dan Pengguna).

## User Story

Sebagai pengguna (individu maupun tim), saya ingin mengelola tugas dan berkolaborasi dalam daftar proyek secara interaktif, sehingga saya dapat memantau progres pekerjaan, menetapkan prioritas, dan menyelesaikan tugas tepat waktu bersama tim.

## Daftar SRS

| Kode | Deskripsi | Acceptance Criteria |
|------|-----------|---------------------|
| *SRS-001* | Manajemen & Otentikasi Pengguna oleh Admin | - Admin dapat menambah akun pengguna baru dengan input nama, email, dan password.<br> <br>- Admin dapat menghapus akun pengguna dari sistem.<br><br>- Pengguna yang telah ditambahkan dapat melakukan login.<br><br>- Akun yang dihapus oleh Admin langsung tidak bisa lagi melakukan login ke sistem. |
| *SRS-002* | Pengelolaan Proyek / Daftar Tugas (List/Project Management) | - Pengguna dapat membuat daftar tugas (list/project) baru dan bertindak sebagai List Owner.<br><br>- Owner dapat mengubah nama/deskripsi daftar serta menghapus daftar beserta seluruh isinya.<br><br>- Halaman daftar menampilkan daftar anggota/kolaborator yang terhubung. |
| *SRS-003* | Kolaborasi & Penambahan Anggota ke Dalam Daftar (Collaborator Invite) | - Owner daftar dapat menambahkan pengguna lain ke dalam daftar miliknya berdasarkan nama/email.<br><br>- Pengguna yang ditambahkan (Collaborator) dapat melihat dan mengakses daftar tersebut di dashboard mereka.<br><br>- Owner dapat menghapus akses Collaborator dari daftar tugas kapan saja. |
| *SRS-004* | Pembuatan & Pengelolaan Item Tugas (Task Item) | - Owner dan Collaborator dapat membuat item tugas baru di dalam daftar.<br><br>- Setiap tugas wajib memiliki judul dan deskripsi opsional.<br><br>- Tugas dapat diperbarui detailnya atau dihapus oleh Owner dan Collaborator. |
| *SRS-005* | Pengaturan Prioritas & Tenggat Waktu (Priority & Due Date) | - Pengguna dapat menentukan prioritas tugas (opsi: Tinggi / High, Sedang / Medium, Rendah / Low).<br><br>- Pengguna dapat menentukan tenggat waktu penyelesaian (Due Date & Time).<br><br>- Tugas yang mendekati atau melewati tenggat waktu menampilkan penanda khusus (indicator highlight). |
| *SRS-006* | Penyelesaian Tugas & Penyaringan (Task Completion & Filter) | - Pengguna dapat menandai tugas sebagai "Selesai" (Completed) atau mengembalikannya ke "Belum Selesai".<br><br>- Tugas yang selesai diberi penanda visual (misal: dicoret atau centang).<br><br>- Tersedia fitur penyaringan (filter) untuk menampilkan tugas berdasarkan status (Semua/Selesai/Belum), Prioritas, dan Tenggat Waktu. |
| *SRS-007* | Pemantauan Progres Penyelesaian Tugas (Progress Tracking) | - Setiap daftar menampilkan indikator progres (misal: progress bar atau persentase % real-time).<br><br>- Persentase dihitung secara otomatis: (Jumlah Tugas Selesai / Total Tugas) * 100%.<br><br>- Grafik/persentase progres langsung diperbarui begitu ada tugas yang ditandai selesai atau ditambahkan. |

## Menjalankan Proyek
## Menjalankan Proyek

Pastikan telah menginstal **PHP** (>= 8.2), **Composer**, **Node.js**, dan server database **MySQL/XAMPP** di komputer Anda.

```bash
# 1. Clone repository
git clone [https://github.com/Feliscantik/Praktikum-PPK-1-STUDI-KASUS-Kelompok-2.git](https://github.com/Feliscantik/Praktikum-PPK-1-STUDI-KASUS-Kelompok-2.git)
cd Praktikum-PPK-1-STUDI-KASUS-Kelompok-2

# 2. Install dependensi PHP & Node.js
composer install
npm install

# 3. Salin file lingkungan (.env) & generate app key
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi database di file .env
# DB_DATABASE=jara_db
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Jalankan migrasi database & seeder
php artisan migrate --seed

# 6. Jalankan server lokal
php artisan serve
npm run dev

jara-app/
├── app/
│   ├── Http/Controllers/   # Logika bisnis (ProjectController, TaskController)
│   └── Models/             # Model Eloquent (User, Project, Task)
├── database/
│   ├── migrations/         # Skema tabel database
│   └── seeders/            # Data awal/dummy database
├── public/                 # Asset publik terkompilasi
├── resources/
│   └── views/              # Tampilan UI (Blade templates)
├── routes/
│   └── web.php             # Deklarasi route aplikasi
├── .env.example            # Template konfigurasi environment
├── .gitignore              # Daftar file yang diabaikan oleh Git
├── composer.json           # Dependensi PHP/Laravel
├── package.json            # Dependensi JavaScript/NPM
└── README.md               # Dokumentasi proyek

