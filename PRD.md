# Spesifikasi Teknis & Dokumentasi Sistem HelpdeskHub

## 1. Deskripsi Aplikasi

HelpdeskHub adalah sistem tiket pelayanan dan dukungan teknologi informasi (IT Helpdesk) terpusat berbasis web yang dirancang khusus untuk memenuhi kebutuhan manajemen insiden dan layanan internal perusahaan secara efisien, terstruktur, dan terukur.

Sistem ini memfasilitasi alur kerja pelaporan masalah IT dari pengguna akhir (karyawan), penerimaan serta penugasan tiket oleh manajemen IT, hingga penanganan teknis dan penyelesaian kendala oleh staf IT Support. Seluruh interaksi, riwayat penanganan, dokumen pendukung, dan komunikasi antarpengguna tercatat secara rinci guna menjamin akuntabilitas serta efisiensi operasional IT perusahan.

Dengan penerapan pembatasan hak akses berbasis peran (Role-Based Access Control / RBAC) yang ketat, HelpdeskHub menjamin keamanan data, kerahasiaan informasi internal, serta memberikan pengawasan (oversight) menyeluruh bagi manajerial dalam memantau kinerja operasional IT Support.

## 2. Arsitektur & Stack Teknologi

| Komponen | Teknologi / Framework | Keterangan / Versi |
| --- | --- | --- |
| Arsitektur Sistem | Monolith MVC | Model-View-Controller terstruktur |
| Backend Framework | CodeIgniter 4 | PHP Framework performa tinggi |
| Bahasa Pemrograman | PHP | Versi 8.4 (Strict Typing & Performance Optimization) |
| Frontend Framework | Bootstrap 5, HTML5, CSS3, JS | Antarmuka responsif & modern |
| Database Engine | MySQL 8.0 | Relational Database Management System |
| Skema Otentikasi | Session-based Authentication | Manajemen sesi aman berbasis server |
| Web Server | Apache HTTP Server | Web server utama dengan mod_rewrite enabled |
| Kontainerisasi | Docker & Docker Compose | Standarisasi lingkungan deployment |

## 3. Peran & Hak Akses Pengguna (User Roles)

Aplikasi HelpdeskHub membagi pengguna ke dalam empat tingkatan peran (roles) utama dengan wewenang operasional sebagai berikut:

### 3.1 Employee (Pengguna Akhir)

Karyawan dari berbagai divisi perusahaan yang mengajukan tiket pengaduan, memberikan kelengkapan informasi keluhan, mengunggah lampiran pendukung, melakukan diskusi interaktif dengan staf IT, serta melacak status penyelesaian tiket milik pribadi.

### 3.2 IT Support (Staf Teknis)

Tim teknis yang bertanggung jawab langsung untuk memverifikasi, menangani, memperbarui status teknis, berinteraksi melalui komentar tiket, serta menyelesaikan kendala IT yang ditugaskan kepada mereka.

### 3.3 IT Manager (Supervisi Operasional)

Manajerial IT yang memegang kendali penuh atas distribusi tiket (assignment/reassignment), pengawasan beban kerja staf IT Support, pemantauan SLA, serta penarikan laporan operasional keseluruhan.

### 3.4 Administrator (Pengelola Sistem)

Pengelola teknis aplikasi yang bertanggung jawab mengurus manajemen akun pengguna, penetapan peran, aktivasi/deaktivasi akun, manajemen kata sandi, serta pemeliharaan master data kategori tiket.

## 4. Spesifikasi Modul & Fitur Aplikasi

### 4.1 Authentication

Login menggunakan email dan password.

Logout.

Change password.

View dan edit profil pribadi.

Tidak terdapat fitur registrasi publik. Seluruh akun dibuat oleh Administrator.

## 4.2 Modul Dashboard & Analitik

Antarmuka dashboard menyajikan ringkasan statistik dan metrik operasional yang disesuaikan secara dinamis berdasarkan konteks peran pengguna logged-in:

| Informasi Metrik | Employee | IT Support | IT Manager | Admin |
| --- | --- | --- | --- | --- |
| Total Tiket | Sesuai Pembuat | Sesuai Assignment | Seluruh Tiket | — |
| Status Break-down (Open/In-Progress/Resolved/Closed) | ✓ | ✓ | ✓ | — |
| Daftar Tiket Terbaru (Recent Tickets) | ✓ | ✓ | ✓ | — |
| Beban Kerja Staf IT (Support Workload Distribution) | — | — | ✓ | — |
| Statistik Akun Pengguna & Status Kategori | — | — | — | ✓ |

Employee melihat statistik tiket pribadi, IT Support melihat statistik tiket yang ditugaskan kepadanya, dan IT Manager melihat statistik seluruh tiket.

Administrator memiliki dashboard sederhana berisi jumlah pengguna aktif dan nonaktif berdasarkan role.

### 4.3 Modul Manajemen Pengguna (User Management)

Khusus Administrator.

| Field | Description |
| --- | --- |
| Full name | Nama lengkap |
| Email | Email pengguna |
| Password | Password pengguna |
| Department | Departemen |
| Role | Role pengguna |
| Status | Active / Inactive |

Fitur: list user, create user, detail user, edit user, reset password, perubahan role, serta aktivasi dan deaktivasi akun.

### 4.4 Modul Kategori Tiket (Ticket Category Master Data)

Khusus Administrator.

Master data kategori tiket:

Hardware

Software

Network

Email

Account & Access

Other

Administrator dapat menambah, mengubah, dan menonaktifkan kategori. Kategori yang sudah digunakan pada tiket tetap dipertahankan dalam riwayat.

### 4.5 Modul Manajemen Tiket (Ticket Lifecycle)

Informasi yang terdapat pada setiap tiket:

| Field | Description |
| --- | --- |
| Ticket number | Nomor tiket otomatis |
| Subject | Judul permasalahan |
| Description | Deskripsi permasalahan |
| Category | Kategori permasalahan |
| Priority | Low, Medium, High |
| Status | Open, In Progress, Resolved, Closed |
| Created by | Pembuat tiket |
| Assigned to | IT Support |
| Created at | Waktu pembuatan |
| Updated at | Waktu perubahan |

Employee dapat membuat, melihat, mengubah, dan menghapus tiket pribadi sesuai business rules.

IT Support dapat melihat dan menangani tiket yang ditugaskan kepadanya.

IT Manager dapat melihat seluruh tiket.

### 4.6 Modul Penugasan Tiket (Ticket Assignment & Distribution)

IT Manager dapat memilih IT Support yang akan menangani suatu tiket.

Fitur yang tersedia:

Assign ticket.

Reassign ticket.

View assigned IT Support.

View assignment history.

Assignment hanya dapat diberikan kepada pengguna aktif dengan role IT Support.

### 4.7 Modul Komunikasi & Komentar Tiket

Pengguna dapat berkomunikasi melalui komentar pada halaman detail tiket.

Informasi komentar meliputi nama pengguna, isi komentar, waktu pembuatan, dan waktu perubahan.

Pengguna dapat menambahkan, melihat, mengubah, serta menghapus komentar sesuai hak aksesnya.

### 4.8 Modul Lampiran Dokumen (Ticket Attachments)

Pengguna dapat menambahkan dokumen pendukung pada tiket.

| Requirement | Value |
| --- | --- |
| Allowed file | JPG, PNG, PDF |
| Maximum size | 2 MB per file |
| Maximum attachment | 3 file per ticket |
| Features | Upload, list, download, delete |

Contoh attachment adalah screenshot error, foto perangkat, dan dokumen pendukung.

### 4.9 Modul Audit Trail & Riwayat Tiket (Ticket History)

Aplikasi mencatat aktivitas yang terjadi pada tiket, meliputi:

Ticket creation.

Ticket update.

Ticket assignment dan reassignment.

Status changes.

Comment activity.

Attachment activity.

Setiap history berisi informasi aktivitas, pengguna yang melakukan aktivitas, dan waktu kejadian.

History bersifat read-only.

### 4.10 Modul Laporan & Ekspor Data (Reporting)

Menampilkan daftar tiket dengan fitur pencarian, pagination, dan filter berdasarkan status, kategori, priority, serta tanggal.

Pengguna dapat melakukan export laporan dalam format CSV sesuai hak aksesnya.

## 5. Aturan Bisnis & Alur Kerja (Business Workflow)

### Business rules

| Rule | Description |
| --- | --- |
| BR-01 | Setiap tiket baru memiliki status Open. |
| BR-02 | Employee hanya dapat mengubah atau menghapus tiket miliknya yang masih Open. |
| BR-03 | IT Manager bertanggung jawab melakukan assignment. |
| BR-04 | IT Support hanya menangani tiket yang ditugaskan kepadanya. |
| BR-05 | IT Support dapat mengubah status Open menjadi In Progress. |
| BR-06 | IT Support dapat mengubah status In Progress menjadi Resolved. |
| BR-07 | Employee dapat menutup atau membuka kembali tiket miliknya yang berstatus Resolved. |
| BR-08 | Tiket yang sudah Closed tidak dapat dimodifikasi. |
| BR-09 | Pengguna hanya dapat mengubah dan menghapus komentar yang dibuatnya sendiri pada tiket yang masih dapat diakses. |
| BR-10 | Pengguna hanya dapat menghapus attachment yang diunggahnya sendiri pada tiket yang masih dapat diakses. |
| BR-11 | Administrator mengelola pengguna dan kategori, bukan isi tiket. |
| BR-12 | Pengguna nonaktif tidak dapat menggunakan aplikasi. |
| BR-13 | Assignment dan reassignment hanya dapat dilakukan pada tiket Open atau In Progress. |
| BR-14 | Reopen mengembalikan status tiket menjadi In Progress dengan IT Support yang sama. |
| BR-15 | Tiket Closed beserta komentar dan attachment tetap dapat dilihat sesuai hak akses. |

## 6. Matriks Otorisasi (Authorization Matrix)

Authorization matrix merupakan bagian dari functional requirement aplikasi.

| Feature | Employee | IT Support | IT Manager | Admin |
| --- | --- | --- | --- | --- |
| Login / Logout | ✓ | ✓ | ✓ | ✓ |
| Manage own profile | ✓ | ✓ | ✓ | ✓ |
| Dashboard | Own | Assigned | All | Users |
| Create ticket | ✓ | — | — | — |
| List ticket | Own | Assigned | All | — |
| Detail ticket | Own | Assigned | All | — |
| Edit ticket | Own* | — | — | — |
| Delete ticket | Own* | — | — | — |
| Assign ticket | — | — | ✓ | — |
| Update status | Limited | Assigned | — | — |
| View comment | Own ticket | Assigned | All | — |
| Add comment | Own ticket | Assigned | All | — |
| Edit comment | Own comment | Own comment | Own comment | — |
| Delete comment | Own comment | Own comment | Own comment | — |
| Upload attachment | Own ticket | Assigned | All | — |
| Download attachment | Own ticket | Assigned | All | — |
| Delete attachment | Own upload | Own upload | Own upload | — |
| View history | Own | Assigned | All | — |
| Export report | Own | Assigned | All | — |
| Manage users | — | — | — | ✓ |
| Manage categories | — | — | — | ✓ |

Keterangan:

Own: data milik pengguna tersebut.

Assigned: tiket yang ditugaskan kepada pengguna.

All: seluruh tiket.

Limited: mengikuti business workflow.

Own*: tiket pribadi dengan status Open.

Seluruh permission tetap mengikuti business rules, termasuk pembatasan perubahan pada tiket Closed.

## 7. Spesifikasi Data Awal (Data Seeder / Dummy Data)

AI harus menyediakan database seeder agar aplikasi langsung dapat digunakan untuk pengujian.

| Name | Email | Role |
| --- | --- | --- |
| Administrator | admin@example.test | Administrator |
| IT Manager | manager@example.test | IT Manager |
| IT Support 01 | support01@example.test | IT Support |
| IT Support 02 | support02@example.test | IT Support |
| Employee 01 | employee01@example.test | Employee |
| Employee 02 | employee02@example.test | Employee |
| Employee 03 | employee03@example.test | Employee |

Minimum dummy data:

7 user accounts.

6 ticket categories.

12 tickets.

24 comments.

12 sample attachments.

Ticket history.

Tiket dengan berbagai status dan priority.

Tiket dengan assignment kepada IT Support yang berbeda.

Credential dummy hanya digunakan pada environment training lokal dan tidak boleh digunakan pada production.

## Klarifikasi sebelum implementasi
- Dokumen sumber menyebut pemantauan SLA pada deskripsi IT Manager, tetapi tidak mendefinisikan metrik atau modul SLA. Minta keputusan instruktur; jangan mengarang requirement SLA.
- Metode penghapusan tiket, aturan perubahan role terhadap tiket yang sedang ditangani, serta detail reset password belum ditentukan. Ajukan keputusan desain dalam planning jika diperlukan.
- Semua fitur pada bagian 4 termasuk CSV dan support workload merupakan requirement; perubahan scope membutuhkan persetujuan instruktur.
- Pernyataan keamanan pada deskripsi adalah tujuan desain, bukan bukti hasil pengujian.
