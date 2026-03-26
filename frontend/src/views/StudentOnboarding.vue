<script setup>
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import logoUrl from '../assets/logo.png'; 
import { ref, watch, computed } from 'vue'; 

const passwordRequirements = computed(() => {
    const p = form.value.password;
    return [
        { label: 'At least 8 characters', met: p.length >= 8 },
        { label: 'Contains a number', met: /\d/.test(p) },
        { label: 'Contains a special character (@$!%*?)', met: /[!@#$%^&*(),.?":{}|<>]/.test(p) }
    ];
});

const isPasswordValid = computed(() => passwordRequirements.value.every(r => r.met));
const passwordsMatch = computed(() => form.value.password === form.value.password_confirmation);

const route = useRoute();
const router = useRouter();
const props = defineProps(['initialMode']);

const mode = ref(props.initialMode || (route.path.includes('signup') ? 'signup' : 'login'));

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '', 
    role: 'student'
});

const handleSubmit = async () => {
    const endpoint = mode.value === 'signup' ? 'http://localhost:8000/api/register' : 'http://localhost:8000/api/login';
    
    try {
        await axios.get('http://localhost:8000/sanctum/csrf-cookie');
        const response = await axios.post(endpoint, form.value);
        const userData = mode.value === 'signup' ? response.data : response.data.user;
        localStorage.setItem('user', JSON.stringify(userData));

        if (mode.value === 'signup') {
        if (!isPasswordValid.value) {
            alert("Please meet all password requirements.");
            return;
        }
        if (!passwordsMatch.value) {
            alert("Passwords do not match.");
            return;
        }
    }
        
        if (userData.role === 'student') {
            router.push('/student/login/dashboard');
        } else {
            console.warn("User has incorrect role for this portal.");
            alert("Unauthorized: This portal is for students only.");
        }
    } catch (error) {
        const errorData = error.response?.data;
        console.error("Auth Error:", errorData || error.message);
        
        if (errorData?.errors?.email) {
            alert("Error: The email has already been taken.");
        } else {
            alert("Action failed. Please check your credentials and try again.");
        }
    }
};

watch(() => route.path, (newPath) => {
  mode.value = newPath.includes('signup') ? 'signup' : 'login';
});

const toggleMode = (newMode) => {
  mode.value = newMode;
  router.push(`/student/${newMode}`);
};
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
        <h1>{{ mode === 'signup' ? 'Join as a Student' : 'Student Login' }}</h1>
        <p>{{ mode === 'signup' ? 'Connect with mentors to navigate your career journey.' : 'Access your university pathway tools.' }}</p>
        
        <form @submit.prevent="handleSubmit">
          <label v-if="mode === 'signup'">
            Full Name:
            <input v-model="form.name" type="text" required placeholder="e.g. Jane Doe">
          </label>

          <label>
            University Email:
            <input v-model="form.email" type="email" required placeholder="email@university.ac.uk">
          </label>

          <label>
            Password:
            <input v-model="form.password" type="password" required>
          </label>

          <div v-if="mode === 'signup'" class="password-checklist">
            <div v-for="req in passwordRequirements" :key="req.label" 
                :class="['requirement-item', { 'is-met': req.met }]">
              <span class="icon">{{ req.met ? '✓' : '○' }}</span>
              {{ req.label }}
            </div>
          </div>

          <label v-if="mode === 'signup'">
            Confirm Password:
            <input v-model="form.password_confirmation" type="password" required>
            <span v-if="form.password && !passwordsMatch" class="error-text">
              Passwords do not match
            </span>
          </label>

          <button 
            type="submit" 
            class="submit-btn"
            :disabled="mode === 'signup' && (!isPasswordValid || !passwordsMatch)"
          >
            {{ mode === 'signup' ? 'Create Account' : 'Sign In' }} →
          </button>
        </form>

        
      </div>
    </div>
  </div>
</template>

<style scoped>
.auth-wrapper {
  background-color: #fbfcff; 
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding-top: 5rem;
  font-family: 'SF-UI Text', 'Arial', sans-serif;
}

.onboarding-container {
  width: 100%;
  max-width: 500px;
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
  border-bottom: 1px solid #DEE4FE;
}

.tabs button {
  flex: 1;
  padding: 1rem;
  background: none;
  border: none;
  font-family: 'SF-UI Text', sans-serif;
  font-weight: 700;
  color: #9ca3af;
  cursor: pointer;
  transition: all 0.2s ease;
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
  font-family: 'SF-UI Text', sans-serif;
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

.institution-link {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.8rem;
  font-family: 'SF-UI Text', sans-serif;
}

.institution-link a { 
  color: #3e55f8; 
  text-decoration: none; 
  font-weight: 600; 
}

.password-checklist {
    margin-top: -0.5rem;
    margin-bottom: 1rem;
    padding: 10px;
    background: #f8faff;
    border-radius: 8px;
    border: 1px solid #DEE4FE;
}

.requirement-item {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: color 0.2s ease;
}

.requirement-item.is-met {
    color: #065f46; 
    font-weight: 600;
}

.error-text {
    font-size: 0.75rem;
    color: #ef4444;
    display: block;
    margin-top: 4px;
}

.submit-btn:disabled {
    background-color: #9ca3af;
    cursor: not-allowed;
    opacity: 0.7;
}
</style>