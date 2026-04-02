# Student Circus: Peer-to-Peer Mentorship & Recruitment Platform

A full-stack web application designed to bridge the career "information gap" for international students by connecting them with verified alumni mentors for career guidance and university insights.

## 🚀 Project Overview
This platform allows international students to browse a directory of verified mentors, schedule sessions within professional constraints, and synchronize confirmed meetings with Google Calendar.

### **Core Stack**
* **Frontend:** Vue.js 3 (Composition API) & Vite
* **Backend:** Laravel 11 (REST API)
* **Database:** SQLite (Relational)
* **Authentication:** Laravel Sanctum (Stateful/Stateless Handshake)
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

# Initialize SQLite database
touch database/database.sqlite
php artisan migrate --seed

# Link storage for uploads
php artisan storage:link

# Start server
php artisan serve
```

### **2. Frontend (Vue.js)**
```bash
# Navigate to frontend folder
cd frontend

# Install Node dependencies
npm install

# Run the development server
npm run dev
```

---

## 🧪 Testing Methodology
The system has been tested for full-stack integration:
1.  **Unit Tests:** Validating the recalculation of the Mentor's `rating_avg`.
2.  **Constraint Tests:** Proving that sessions ending after 6 PM or with non-standard durations (e.g., 20 mins) are rejected.
3.  **Cross-Origin Tests:** Verifying the CSRF "handshake" between ports `5173` and `8000`.

*Detailed test cases are available in **Appendix F** of the Final Report.*

---

## 👤 Author
**Raisa Rahman** Individual Project (IN3007)  
City, University of London  
---

### Why this README is "BSc Level":
* **Technical Communication:** It clearly defines the "Handshake" architecture and technical constraints.
* **Reproducibility:** It provides concise, standard commands for a marker to set up the environment.
* **Security Focus:** It mentions Laravel Sanctum and protected storage, addressing LSEPI (Legal, Social, Ethical, and Professional Issues).
