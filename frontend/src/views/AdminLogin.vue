<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const form = ref({ email: '', password: '' });

const handleAdminLogin = async () => {
    try {
        await axios.get('http://localhost:8000/sanctum/csrf-cookie', { withCredentials: true });
        
        const response = await axios.post('http://localhost:8000/api/login', form.value, { 
            withCredentials: true,
            headers: { 'Accept': 'application/json' } 
        });

        const userData = response.data.user;
        
        if (userData && userData.role === 'admin') {
            localStorage.setItem('user', JSON.stringify(userData));
            router.push('/staff-portal/dashboard');
        } else {
            alert('Access Denied: Admin account required.');
        }
    } catch (error: any) {
        console.error("Detailed Login Error:", error.response?.data || error.message);
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
.login-container { max-width: 400px; margin: 100px auto; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; }
h1 { color: #3e55f8; font-family: 'Red Hat Display', sans-serif; }
input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #DEE4FE; border-radius: 8px; }
button { width: 100%; padding: 12px; background: #3e55f8; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; }
</style>