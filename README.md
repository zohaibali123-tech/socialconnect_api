# SocialConnect API

SocialConnect API is a Laravel-based backend application built entirely using RESTful APIs.  
It allows user registration, authentication, and social interactions (posts, likes, comments) using **token-based authentication** via Laravel Sanctum.  
The project also integrates **Google OAuth 2.0** for social login.

---

## 🚀 Features
- **User Authentication** using Laravel Sanctum (Login, Register, Logout)
- **Google Login** via OAuth 2.0
- CRUD APIs for posts and comments
- Like/Unlike posts
- Profile management with image upload
- API-only backend (frontend communicates via AJAX requests)
- Third-party authentication with Google

---

## 🛠️ Technologies Used
- **Laravel** (PHP Framework)
- **Laravel Sanctum** (Token-based authentication)
- **Google OAuth 2.0** (Social Login)
- **MySQL** (Database)
- **Bootstrap + jQuery** (Frontend integration)
- **RESTful API architecture**

---

## 📦 Laravel Packages Used
- `laravel/sanctum` – API authentication
- `laravel/socialite` – Google OAuth 2.0 integration
- `guzzlehttp/guzzle` – HTTP client
- `intervention/image` – Image processing

---

---

## ⚙️ Installation Guide

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/socialconnect-api.git
cd socialconnect-api
composer install
npm install && npm run dev
cp .env.example .env

## 📂 Project StrucDB_DATABASE=socialconnect
DB_USERNAME=root
DB_PASSWORD=
ture Overview
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve

