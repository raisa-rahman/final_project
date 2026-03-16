<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import logoUrl from '../assets/logo.png'; 

const props = defineProps(['initialMode']);
const route = useRoute();
const router = useRouter();

const mode = ref(props.initialMode || (route.path.includes('signup') ? 'signup' : 'login'));

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    university_id: '',
    role: 'mentor'
});

const handleSubmit = async () => {
    try {
        await axios.get('http://localhost:8000/sanctum/csrf-cookie', { withCredentials: true });
        await axios.post('http://localhost:8000/api/register', form.value, {
            headers: { 'Accept': 'application/json' },
            withCredentials: true
        });
        
        alert('Signup request submitted! An administrator will verify your profile.');
        mode.value = 'login'; 
    } catch (error) {
        console.error("Signup Error:", error.response?.data || error.message);
        alert("Signup failed. Check console for details.");
    }
};

const handleLogin = async () => {
    try {
        await axios.get('http://localhost:8000/sanctum/csrf-cookie', { withCredentials: true });
        const response = await axios.post('http://localhost:8000/api/login', {
            email: form.value.email,     
            password: form.value.password 
        }, { withCredentials: true });
        if (response.data.status === 'success') {
            localStorage.setItem('user', JSON.stringify(response.data.user));
            router.push('/mentor/login/dashboard');
        }
    } catch (err) {
        console.error("Mentor Auth Error:", err.response?.data || err.message);
        alert("Login failed. Check your credentials.");
    }
};

watch(() => route.path, (newPath) => {
  mode.value = newPath.includes('signup') ? 'signup' : 'login';
});

const toggleMode = (newMode) => {
  mode.value = newMode;
  router.push(`/mentor/${newMode}`);
};

const universities = ref([]);

onMounted(async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/universities');
        universities.value = response.data;
    } catch (err) {
        console.error("Failed to load universities", err);
    }
});
</script>

<template>
  <div class="auth-wrapper">
    <div class="onboarding-container">
      <div class="brand-header">
        <img :src="logoUrl" alt="Student Circus" class="logo">
      </div>

      <div class="tabs">
        <button :class="{ active: mode === 'login' }" @click="toggleMode('login')">Login</button>
        <button :class="{ active: mode === 'signup' }" @click="toggleMode('signup')">Sign up</button>
      </div>

      <div class="form-content">
        <h1>{{ mode === 'signup' ? 'Join as a Mentor' : 'Mentor Login' }}</h1>
        <p>{{ mode === 'signup' ? 'Share your expertise.' : 'Welcome back.' }}</p>
        
        <form @submit.prevent="mode === 'signup' ? handleSubmit() : handleLogin()">
          <label v-if="mode === 'signup'">
            Full Name:
            <input v-model="form.name" type="text" required placeholder="e.g. Jane Doe">
          </label>

          <label>
            Professional Email:
            <input v-model="form.email" type="email" required placeholder="name@company.com">
          </label>

          <label v-if="mode === 'signup'">
            Your University:
            <select v-model="form.university_id" required class="uni-select">
              <option value="" disabled>Select your institution</option>
              <option v-for="uni in universities" :key="uni.id" :value="uni.id">
                {{ uni.name }} ({{ uni.country }})
              </option>
            </select>
          </label>

          <label>
            Password:
            <input v-model="form.password" type="password" required>
          </label>

          <label v-if="mode === 'signup'">
            Confirm Password:
            <input v-model="form.password_confirmation" type="password" required>
          </label>

          <button type="submit" class="submit-btn">
            {{ mode === 'signup' ? 'Submit for Verification' : 'Sign In' }} →
          </button>
        </form>

        <p class="footer-note" v-if="mode === 'signup'">
          By signing up, you agree to our <a href="#">Mentor Terms</a>
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.auth-wrapper {
  background-color: #fbfcff; 
  min-height: 100vh;
  font-family: 'SF-UI Text', 'Arial', sans-serif;
}

.back-bar {
  background-color: #3e55f8; 
  padding: 12px 5%;
}

.back-btn {
  background: none;
  border: none;
  color: white;
  font-family: 'SF-UI Text', sans-serif;
  font-weight: 600;
  cursor: pointer;
}

.onboarding-container {
  width: 100%;
  max-width: 500px;
  margin: 3rem auto;
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
  border: 1px solid #DEE4FE; 
  overflow: hidden;
}

.brand-header {
  text-align: center;
  padding: 2rem 0 1rem;
}

.logo { height: 50px; }

.tabs {
  display: flex;
  background-color: #ffffff;
  border-bottom: 1px solid #DEE4FE; 
}

.tabs button {
  flex: 1;
  padding: 1rem;
  background: none;
  border: none;
  font-family: 'Red Hat Display', sans-serif; 
  font-weight: 700;
  color: #9ca3af;
  cursor: pointer;
  transition: all 0.2s;
}

.tabs button.active {
  color: #3e55f8; 
  border-bottom: 3px solid #3e55f8; 
}

.form-content { padding: 2rem 3rem; }

h1 {
  font-family: 'Red Hat Display', sans-serif;
  color: #1a1f3c; 
  margin-bottom: 0.5rem;
  font-size: 1.5rem;
  font-weight: 700;
}

p {
  color: #6b7280;
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
  line-height: 1.5;
}

label {
  display: block;
  font-weight: 600;
  font-size: 0.85rem;
  color: #1a1f3c; 
  margin-bottom: 1rem;
}

input {
  width: 100%;
  padding: 12px;
  margin-top: 6px;
  border: 1px solid #DEE4FE; 
  border-radius: 8px;
  font-family: 'SF-UI Text', sans-serif;
  font-size: 0.95rem;
  transition: border-color 0.2s;
}

input:focus {
  outline: none;
  border-color: #3e55f8; 
}

.submit-btn {
  width: 100%;
  background-color: #3e55f8;
  color: white;
  padding: 14px;
  border: none;
  border-radius: 8px;
  font-family: 'Red Hat Display', sans-serif;
  font-weight: 700;
  cursor: pointer;
  margin-top: 1rem;
  transition: background-color 0.3s ease;
}

.submit-btn:hover {
  background-color: #2DE1AA; 
}

.footer-note {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.8rem;
  color: #6b7280;
}

.footer-note a { 
  color: #3e55f8; 
  text-decoration: none; 
  font-weight: 600; 
}

.uni-select {
  width: 100%;
  padding: 12px;
  margin-top: 6px;
  border: 1px solid #DEE4FE; 
  border-radius: 8px;
  font-family: 'SF-UI Text', sans-serif;
  font-size: 0.95rem;
  background-color: white; 
  transition: border-color 0.2s;
  appearance: none; 
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 16px;
}

.uni-select:focus {
  outline: none;
  border-color: #3e55f8; 
}
</style>