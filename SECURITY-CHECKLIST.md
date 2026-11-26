# General Secure Coding Checklist

Security checklist berikut bersifat technology-agnostic, sehingga dapat digunakan pada aplikasi monolith maupun microservices, menggunakan PHP, JavaScript, Go, Java, atau bahasa pemrograman lainnya.

Checklist ini merupakan baseline praktis untuk development, bukan pengganti seluruh standar OWASP ASVS.

## 1. Authentication

Memastikan pengguna adalah pihak yang benar-benar memiliki identitas tersebut.

- [ ] SEC-01-01 — Menggunakan secure password hashing.

- [ ] SEC-01-02 — Memvalidasi authentication pada setiap protected endpoint.

- [ ] SEC-01-03 — Mencegah authentication bypass.

- [ ] SEC-01-04 — Menerapkan rate limiting pada login.

- [ ] SEC-01-05 — Mencegah username enumeration.

- [ ] SEC-01-06 — Mengamankan mekanisme reset password.

- [ ] SEC-01-07 — Tidak menggunakan default credential pada production.

- [ ] SEC-01-08 — Memvalidasi signature JWT pada setiap protected endpoint yang menggunakan JWT authentication.

- [ ] SEC-01-09 — Menggunakan algoritma JWT signing yang aman dan secara eksplisit mengizinkan algoritma yang digunakan (algorithm allowlist).

- [ ] SEC-01-10 — Menolak JWT dengan algoritma yang tidak diizinkan, signature tidak valid, atau token yang dimanipulasi.

- [ ] SEC-01-11 — Memvalidasi JWT claims seperti issuer (iss), audience (aud), expiration (exp), dan not-before (nbf) sesuai kebutuhan.

- [ ] SEC-01-12 — Tidak mempercayai role, privilege, atau authorization claims dari JWT sebelum signature dan seluruh validasi token berhasil.

- [ ] SEC-01-13 — Memastikan JWT signing secret atau private key tidak menggunakan nilai default, hardcoded, atau credential yang diketahui publik.

- [ ] SEC-01-14 — Menyediakan mekanisme rotasi JWT signing secret atau signing key.

## 2. Authorization & Access Control

Memastikan pengguna hanya dapat melakukan aktivitas sesuai hak aksesnya.

- [ ] SEC-02-01 — Menerapkan server-side authorization.

- [ ] SEC-02-02 — Menerapkan prinsip deny by default.

- [ ] SEC-02-03 — Memvalidasi role dan permission.

- [ ] SEC-02-04 — Memvalidasi resource ownership.

- [ ] SEC-02-05 — Memvalidasi hubungan antarresource.

- [ ] SEC-02-06 — Menerapkan least privilege.

- [ ] SEC-02-07 — Mencegah horizontal privilege escalation.

- [ ] SEC-02-08 — Mencegah vertical privilege escalation.

- [ ] SEC-02-09 — Mencegah unauthorized access terhadap object, function, dan property.

- [ ] SEC-02-10 — Menerapkan authorization secara konsisten pada seluruh entry point.

- [ ] SEC-02-11 — Memvalidasi privilege dan role berdasarkan identitas serta authorization policy yang terpercaya, bukan hanya berdasarkan klaim JWT.

- [ ] SEC-02-12 — Mencegah privilege escalation melalui manipulasi JWT claims, termasuk perubahan role menjadi administrator.

- [ ] SEC-02-13 — Memastikan perubahan role dan permission berlaku sesuai authorization policy terbaru, termasuk penanganan token yang masih berlaku setelah perubahan privilege.

## 3. Session Management

- [ ] SEC-03-01 — Menggunakan session identifier yang aman.

- [ ] SEC-03-02 — Melakukan regenerasi session setelah login.

- [ ] SEC-03-03 — Menerapkan session expiration.

- [ ] SEC-03-04 — Melakukan invalidasi session setelah logout.

- [ ] SEC-03-05 — Mencegah session fixation.

- [ ] SEC-03-06 — Menggunakan HttpOnly, Secure, dan SameSite cookie sesuai environment.

- [ ] SEC-03-07 — Tidak mengekspos session identifier melalui URL atau log.

## 4. Input Validation

Seluruh data yang berasal dari luar aplikasi harus dianggap tidak terpercaya.

- [ ] SEC-04-01 — Melakukan validasi input pada server.

- [ ] SEC-04-02 — Memvalidasi header, path, query parameter, dan request body.

- [ ] SEC-04-03 — Memvalidasi tipe, format, panjang, dan range data.

- [ ] SEC-04-04 — Menggunakan allowlist validation jika memungkinkan.

- [ ] SEC-04-05 — Membatasi ukuran request.

- [ ] SEC-04-06 — Mencegah mass assignment.

- [ ] SEC-04-07 — Tidak mengandalkan client-side validation sebagai security control.

## 5. Injection Prevention

- [ ] SEC-05-01 — Mencegah SQL Injection.

- [ ] SEC-05-02 — Mencegah OS Command Injection.

- [ ] SEC-05-03 — Mencegah Cross-Site Scripting (XSS).

- [ ] SEC-05-04 — Mencegah Server-Side Template Injection.

- [ ] SEC-05-05 — Mencegah Path Traversal.

- [ ] SEC-05-06 — Mencegah insecure deserialization.

- [ ] SEC-05-07 — Menggunakan parameterized query.

- [ ] SEC-05-08 — Menggunakan context-aware output encoding.

- [ ] SEC-05-09 — Menghindari eksekusi kode atau command berdasarkan input pengguna.

## 6. Business Logic Security

- [ ] SEC-06-01 — Melakukan validasi business rules pada server.

- [ ] SEC-06-02 — Memvalidasi setiap perubahan status.

- [ ] SEC-06-03 — Mencegah bypass business workflow.

- [ ] SEC-06-04 — Tidak mempercayai hasil perhitungan dari client.

- [ ] SEC-06-05 — Mencegah manipulasi nilai negatif atau nilai di luar batas.

- [ ] SEC-06-06 — Mencegah duplicate transaction.

- [ ] SEC-06-07 — Mencegah race condition.

- [ ] SEC-06-08 — Menjaga konsistensi data dan transaksi.

## 7. File Handling Security

- [ ] SEC-07-01 — Memvalidasi tipe, extension, dan isi file.

- [ ] SEC-07-02 — Membatasi ukuran file.

- [ ] SEC-07-03 — Menggunakan nama file yang dihasilkan server.

- [ ] SEC-07-04 — Mencegah eksekusi file yang di-upload.

- [ ] SEC-07-05 — Menyimpan private file di luar public directory.

- [ ] SEC-07-06 — Memvalidasi hak akses terhadap file.

- [ ] SEC-07-07 — Mencegah manipulasi file path.

## 8. Data Protection

- [ ] SEC-08-01 — Tidak menyimpan password dalam plaintext.

- [ ] SEC-08-02 — Melindungi data sensitif saat disimpan dan dikirimkan.

- [ ] SEC-08-03 — Tidak mengekspos data yang tidak diperlukan.

- [ ] SEC-08-04 — Tidak menyimpan credential pada source code.

- [ ] SEC-08-05 — Tidak memasukkan secret ke Git repository.

- [ ] SEC-08-06 — Menggunakan database account dengan least privilege.

- [ ] SEC-08-07 — Mengamankan backup database.

## 9. Web Security

- [ ] SEC-09-01 — Menerapkan CSRF protection.

- [ ] SEC-09-02 — Menerapkan Content Security Policy sesuai kebutuhan.

- [ ] SEC-09-03 — Mencegah clickjacking.

- [ ] SEC-09-04 — Mengonfigurasi CORS secara aman jika digunakan.

- [ ] SEC-09-05 — Menggunakan HTTPS pada production.

- [ ] SEC-09-06 — Menerapkan security headers.

- [ ] SEC-09-07 — Mencegah open redirect.

- [ ] SEC-09-08 — Tidak mengandalkan penyembunyian menu sebagai access control.

## 10. Security Configuration

- [ ] SEC-10-01 — Menonaktifkan debug mode pada production.

- [ ] SEC-10-02 — Menonaktifkan fitur dan service yang tidak digunakan.

- [ ] SEC-10-03 — Tidak mengekspos internal service tanpa kebutuhan.

- [ ] SEC-10-04 — Memisahkan development dan production environment.

- [ ] SEC-10-05 — Menggunakan environment variable atau secret manager.

- [ ] SEC-10-06 — Menggunakan framework security features secara tepat.

## 11. Dependency & Supply Chain Security

- [ ] SEC-11-01 — Menggunakan dependency dari sumber terpercaya.

- [ ] SEC-11-02 — Memeriksa known vulnerability pada dependency.

- [ ] SEC-11-03 — Menggunakan dependency lock file.

- [ ] SEC-11-04 — Menghapus dependency yang tidak digunakan.

- [ ] SEC-11-05 — Tidak menggunakan dependency yang sudah tidak didukung.

- [ ] SEC-11-06 — Memeriksa keamanan third-party library.

## 12. Error Handling & Logging

- [ ] SEC-12-01 — Tidak mengekspos stack trace kepada pengguna.

- [ ] SEC-12-02 — Tidak mengekspos database error kepada pengguna.

- [ ] SEC-12-03 — Tidak mencatat password, token, atau secret pada log.

- [ ] SEC-12-04 — Mencatat authentication failure.

- [ ] SEC-12-05 — Mencatat authorization failure.

- [ ] SEC-12-06 — Mencatat aktivitas administratif.

- [ ] SEC-12-07 — Melindungi log dari unauthorized access.

## 13. Security Verification

- [ ] SEC-13-01 — Melakukan security code review.

- [ ] SEC-13-02 — Melakukan authentication testing.

- [ ] SEC-13-03 — Melakukan authorization testing.

- [ ] SEC-13-04 — Melakukan input validation testing.

- [ ] SEC-13-05 — Melakukan business logic testing.

- [ ] SEC-13-06 — Melakukan dependency security scanning.

- [ ] SEC-13-07 — Melakukan functional regression testing.

- [ ] SEC-13-08 — Mendokumentasikan hasil pengujian.


