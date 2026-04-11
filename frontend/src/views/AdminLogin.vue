<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

/**
 * AdminLogin component handles the secure authentication for staff members.
 * It utilises Laravel Sanctum's CSRF protection and validates the 'admin' 
 * role before granting access to the dashboard.
 */

const router = useRouter();
// Reactive reference to store user credentials
const form = ref({ email: '', password: '' });

/**
 * Execute the administrative login sequence.
 * This involve obtaining a CSRF cookie, verifying credentials, 
 * and performing a role-based authorisation check.
 */
const handleAdminLogin = async () => {
    try {
        // Step 1: Request a CSRF cookie from the backend to prevent cross-site request forgery
        // This is a requirement for stateful authentication using Laravel Sanctum
        await axios.get('http://localhost:8000/sanctum/csrf-cookie', { withCredentials: true });
        
        // Step 2: Post the login credentials to the authentication endpoint
        const response = await axios.post('http://localhost:8000/api/login', form.value, { 
            withCredentials: true,
            headers: { 'Accept': 'application/json' } 
        });
        const userData = response.data.user;
        // Step 3: Authorisation check. Only users with the 'admin' role may proceed
        if (userData && userData.role === 'admin') {
            // Persist user data to local storage for session management on the frontend
            localStorage.setItem('user', JSON.stringify(userData));
// Redirect the authorised administrator to the staff dashboard            router.push('/staff-portal/dashboard');
        } else {
            // Rebuff non-admin users even if their credentials are correct
            alert('Access Denied: Admin account required.');
        }
    } catch (error: any) {
        // Log detailed error diagnostics for technical troubleshooting
        console.error("Detailed Login Error:", error.response?.data || error.message);
        // Provide a generic alert to the user for security reasons
        alert('Login failed. Please check your credentials.');
    }
};
</script>

<template>
  <div class="login-container">
    <h1>Staff Portal</h1>
    <form @submit.prevent="handleAdminLogin">
      <input v-model="form.email" type="email" placeholder="Admin Email" required>
      <input v-model="form.password" type="password" placeholder="Password" required>
      <button type="submit">Access Dashboard</button>
    </form>
  </div>
</template>

<style scoped>
.login-container { 
    max-width: 400px; 
    margin: 100px auto; 
    padding: 2rem; 
    border-radius: 12px; 
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); 
    text-align: center; 
    background: white;
    font-family: 'SF-UI Text', 'Arial', sans-serif;
    border: 1px solid #DEE4FE;
}

h1 { 
    color: #1a1f3c; 
    font-family: 'Red Hat Display', sans-serif; 
    font-weight: 700;
    margin-bottom: 1.5rem;
}

label {
    display: block;
    text-align: left;
    font-size: 0.9rem;
    font-weight: 600;
    color: #1a1f3c;
    margin-bottom: 0.3rem;
}

input { 
    width: 100%; 
    padding: 12px; 
    margin-bottom: 1.2rem; 
    border: 1px solid #DEE4FE; 
    border-radius: 8px; 
    font-family: 'SF-UI Text', sans-serif;
    transition: border-color 0.2s;
}

input:focus {
    outline: none;
    border-color: #3e55f8;
}

button { 
    width: 100%; 
    padding: 12px; 
    background: #3e55f8; 
    color: white; 
    border: none; 
    border-radius: 8px; 
    cursor: pointer; 
    font-family: 'Red Hat Display', sans-serif;
    font-weight: 700; 
    font-size: 1rem;
    transition: opacity 0.2s;
}

button:hover {
    opacity: 0.9;
}

.error-message {
    color: #ef4444;
    font-size: 0.85rem;
    margin-top: 1rem;
}
</style>