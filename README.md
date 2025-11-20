# PA-FRAMEWORK-2025-EZX-TOPUP-STORE
2209106033 - Ashadi Permana
![alt text](https://github.com/EzXens/PA-FRAMEWORK-2025-EZX-TOPUP-STORE/blob/main/ss-web-ezx/logo_ezx.png?raw=true)

# 🎮 EzX Game Top Up Store – Laravel Web App  
Platform top up game cepat, aman, dan modern. Dibangun menggunakan **Laravel**, **Tailwind CSS**, dan **daisyUI**, website ini menyediakan pengalaman transaksi yang mudah untuk pengguna dan panel manajemen lengkap untuk admin & superadmin.

## 🚀 Tech Stack

### Backend  
![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

### Frontend  
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)
![daisyUI](https://img.shields.io/badge/daisyUI-5A0EF8?style=for-the-badge&logo=daisyui&logoColor=white)


## 📌 Deskripsi Project
EzX Game Top Up Store adalah website top up game berbasis web yang dirancang untuk memberikan pengalaman pembelian cepat dan efisien.  
Website ini mendukung berbagai metode pembayaran seperti **QRIS**, **transfer bank**, **e-wallet**, hingga **saldo koin internal**.

Dengan panel **Admin** dan **Superadmin**, pengelolaan pesanan, data game, harga, user, dan bukti pembayaran dapat dilakukan dengan mudah.

---

## 🔥 Fitur Utama

### 👤 User Features
- Registrasi & Login pengguna  
- Dashboard pengguna  
- Riwayat transaksi lengkap  
- Top up berbagai game populer  
- Upload bukti pembayaran  
- Konfirmasi otomatis via WhatsApp  
- Sistem koin internal  

---

### 🛠️ Admin Features
- Dashboard admin  
- Approve / Reject bukti pembayaran  
- CRUD Game
- Melihat detail user & pesanan  
- Verifikasi bukti pembayaran  

---

### 🛡️ Superadmin Features
- CRUD Admin   
- Monitoring seluruh transaksi  
- Statistik penjualan  

---

## 💳 Payment Features
- QRIS (custom image QR)  
- Transfer Bank  
- E-wallet (Dana, OVO, dll)  
- Payment instructions otomatis  
- Format WA auto-message  

---


## 🖼️ Dokumentasi (Screenshots)

### 🏠 Homepage  
![alt text](https://github.com/EzXens/PA-FRAMEWORK-2025-EZX-TOPUP-STORE/blob/main/ss-web-ezx/homepage.png?raw=true) 

### 🎮 List Game  
![alt text](https://github.com/EzXens/PA-FRAMEWORK-2025-EZX-TOPUP-STORE/blob/main/ss-web-ezx/listgame.png?raw=true)  

### 📄 Detail Game  
![alt text](https://github.com/EzXens/PA-FRAMEWORK-2025-EZX-TOPUP-STORE/blob/main/ss-web-ezx/detail-game.png?raw=true) 

### 💰 Konfirmasi Pembayaran  
![alt text](https://github.com/EzXens/PA-FRAMEWORK-2025-EZX-TOPUP-STORE/blob/main/ss-web-ezx/konfirmasi-pembayaran.png?raw=true) 

### 🔐 Login  
![alt text](https://github.com/EzXens/PA-FRAMEWORK-2025-EZX-TOPUP-STORE/blob/main/ss-web-ezx/login.png?raw=true) 

### 📝 Register  
![alt text](https://github.com/EzXens/PA-FRAMEWORK-2025-EZX-TOPUP-STORE/blob/main/ss-web-ezx/register.png?raw=true) 

### 👤 User Dashboard  
![alt text](https://github.com/EzXens/PA-FRAMEWORK-2025-EZX-TOPUP-STORE/blob/main/ss-web-ezx/dashboard-user.png?raw=true) 

### 📜 Riwayat Transaksi  
![alt text](https://github.com/EzXens/PA-FRAMEWORK-2025-EZX-TOPUP-STORE/blob/main/ss-web-ezx/riwayat-transaksi.png?raw=true) 

### 🧩 Admin Dashboard  
![alt text](https://github.com/EzXens/PA-FRAMEWORK-2025-EZX-TOPUP-STORE/blob/main/ss-web-ezx/admin-dashboard.png?raw=true) 

### 🧾 Superadmin Dashboard  
![alt text](https://github.com/EzXens/PA-FRAMEWORK-2025-EZX-TOPUP-STORE/blob/main/ss-web-ezx/superadmin-dashboard.png?raw=true)  

### 🛠️ CRUD Admin  
![alt text](https://github.com/EzXens/PA-FRAMEWORK-2025-EZX-TOPUP-STORE/blob/main/ss-web-ezx/crud-admin.png?raw=true) 

### ✔️ Form Approve Admin  
![alt text](https://github.com/EzXens/PA-FRAMEWORK-2025-EZX-TOPUP-STORE/blob/main/ss-web-ezx/form-approve-admin.png?raw=true)  

---

## 🏗️ Cara Instalasi (Local Development)

```bash
git clone https://github.com/username/ezx-store.git
cd ezx-store

composer install
npm install
npm run dev

cp .env.example .env
php artisan key:generate

php artisan migrate
php artisan serve
