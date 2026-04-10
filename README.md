# Student Circus: Peer-to-Peer Mentorship & Recruitment Platform

A full-stack web application designed to bridge the career "information gap" for international students by connecting them with verified alumni mentors for career guidance and university insights.

## 📂 Folder Structure

The submitted ZIP file is organised as follows to ensure the application runs correctly:

* **`/backend`**: The Laravel 11 API source code.
    * `/database`: Contains the SQLite database migrations and seeders.
    * `/storage`: Location for mentor ID uploads and profile photos.
* **`/frontend`**: The Vue.js 3 source code (Vite-powered).
    * `/src`: Frontend components and reactive logic.
* **`/docs`**: Supplemental documentation including the Final Report.
* **`README.md`**: This instruction file.

---

## 🚀 Project Overview

This platform allows international students to browse a directory of verified mentors, schedule sessions within professional constraints, and synchronise confirmed meetings with Google Calendar.

* **Frontend:** Vue.js 3 (Composition API) & Vite
* **Backend:** Laravel 11 (REST API)
* **Database:** SQLite (Relational)
* **Integrations:** Google Calendar API (OAuth 2.0)

---

## 🔧 Installation & Local Setup

### **1. Backend (Laravel)**

```bash
# Navigate to backend folder
cd backend

# Install PHP dependencies
composer install

# Set up environment
cp .env.example .env

# Generate security key
php artisan key:generate

# Initialise SQLite database
touch database/database.sqlite

# IMPORTANT: Run the following command to populate the database with mock data
php artisan migrate:fresh --seed

# Link storage for profile photos and ID documents
php artisan storage:link

# Start server
php artisan serve
```

*Backend runs on: `http://localhost:8000`*

### **2. Frontend (Vue.js)**

```bash
# Navigate to frontend folder
cd frontend

# Install Node dependencies
npm install

# Run the development server
npm run dev
```

*Frontend runs on: `http://localhost:5173`*

---

## 🔑 Access Credentials & Mock Data

For testing purposes, the database is populated with mock data. Please note that **all mentors in the seeded data share the same identification document image**, as this is a demonstration of the verification workflow.

### **Testing Accounts**
You may create new accounts via the "Sign Up" tabs or use the existing seeded accounts below:

* **Existing Student/Mentor Accounts:**
    * **Password for all seeded accounts:** `testtest32!`
* **Administrator Account (For Mentor Verification):**
    * **Email:** `admin@studentcircus.com`
    * **Password:** `testtest32!`

---

## 🧪 Testing Instructions for Markers

To verify the "Professional Product" criteria, markers are encouraged to test the following core functionalities:

1.  **Mentor Directory:** Use searchable filters to find mentors by expertise or university.
2.  **Booking & Scheduling:** Schedule a session as a student; note the 15-minute increment constraints and professional hours (8 AM - 6 PM).
3.  **Mentor Verification Flow:** Log in as the Admin to view and approve/reject mentor ID uploads.
4.  **Feedback & Ratings:** Complete a session and submit a review to see the mentor's average rating update in real-time.

---

## 👤 Author

**Raisa Rahman** (IN3007 Individual Project)
City, University of London
**Consultant:** Peter Popov
**Client Contact:** Parikshit Singh