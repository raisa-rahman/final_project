<script setup>
import axios from 'axios';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const form = ref({
    email: '',
    password: '',
});

const handleLogin = async () => {
    try {
        await axios.get('http://localhost:8000/sanctum/csrf-cookie', { withCredentials: true });

        await axios.post('http://localhost:8000/login', form.value, { withCredentials: true });
        
        router.push('/dashboard');
    } catch (error) {
        alert('Login failed. Ensure your email and password are correct.');
        console.error("Error Status:", error.response?.status);
    }
};
</script>

<template>
  <div class="login-container">
    <h1>Login</h1>
    <form @submit.prevent="handleLogin">
      <label>Email:
        <input v-model="form.email" type="email" required placeholder="email@example.com">
      </label>

      <label>Password:
        <input v-model="form.password" type="password" required>
      </label>

      <button type="submit">Sign In</button>
      <p>Don't have an account? <router-link to="/onboarding">Join as a Mentor</router-link></p>
    </form>
  </div>
</template>

<style scoped>
/* Use the Blue and Red Hat Display font theme from Day 4 */
.login-container {
  max-width: 400px;
  margin: 4rem auto;
  padding: 40px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  font-family: 'SF-UI Text', sans-serif;
}
h1 {
  font-family: 'Red Hat Display', sans-serif;
  color: #3e55f8; /* Blue Theme */
}
input {
  width: 100%;
  padding: 12px;
  margin: 8px 0 16px;
  border: 1px solid #DEE4FE; /* Light Purple Theme */
  border-radius: 8px;
}
button {
  width: 100%;
  background-color: #3e55f8; /* Blue */
  color: white;
  padding: 14px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}
button:hover {
  background-color: #2DE1AA; /* Green Theme hover */
}
</style>