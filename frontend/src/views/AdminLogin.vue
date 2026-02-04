<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const form = ref({ email: '', password: '' });

const handleAdminLogin = async () => {
    try {
        await axios.get('http://localhost:8000/sanctum/csrf-cookie', { withCredentials: true });
        
        await axios.post('http://localhost:8000/login', form.value, { withCredentials: true });

        const response = await axios.get('http://localhost:8000/api/user', { withCredentials: true });
        
        localStorage.setItem('user', JSON.stringify(response.data));
        
        router.push('/adminlogin/home');
    } catch (error: any) {
        console.error("Login Error:", error.response?.data);
        alert('Access Denied. Ensure you are using the Admin account.');
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