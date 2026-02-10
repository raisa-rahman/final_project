<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const user = JSON.parse(localStorage.getItem('user'));
const loading = ref(false);
const message = ref('');
const error = ref(false);

const profile = ref({
    target_university: '',
    target_country: ''
});

const fetchProfile = async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/student/profile');
        if (response.data) {
            profile.value.target_university = response.data.target_university || '';
            profile.value.target_country = response.data.target_country || '';
        }
    } catch (err) {
        console.error("Failed to load profile data", err);
    }
};

const updateProfile = async () => {
    loading.value = true;
    message.value = '';
    error.value = false;
    try {
        await axios.put('http://localhost:8000/api/student/profile', profile.value);
        message.value = "Profile updated successfully!";
    } catch (err) {
        error.value = true;
        message.value = "Failed to update profile. Please try again.";
    } finally {
        loading.value = false;
    }
};

const handleLogout = async () => {
    try {
        await axios.post('http://localhost:8000/logout');
    } finally {
        localStorage.removeItem('user');
        router.push('/'); 
    }
};

onMounted(fetchProfile);
</script>

<template>
  <div class="dashboard-wrapper">
    <header class="dashboard-header">
      <div class="header-main">
        <h1>Welcome, {{ user?.name }}</h1>
        <button @click="handleLogout" class="logout-btn">Log Out</button>
      </div>
      <p>Current Role: <strong>{{ user?.role }}</strong></p>
    </header>

    <div class="profile-card">
      <h2>Edit My Educational Goals</h2>
      <form @submit.prevent="updateProfile" class="edit-form">
        <div class="form-group">
          <label>Target University</label>
          <input v-model="profile.target_university" type="text" placeholder="e.g. City St George's">
        </div>
        <div class="form-group">
          <label>Target Country</label>
          <input v-model="profile.target_country" type="text" placeholder="e.g. United Kingdom">
        </div>
        <button type="submit" :disabled="loading" class="save-btn">
          {{ loading ? 'Saving...' : 'Update Goals' }}
        </button>
      </form>
      <p v-if="message" class="status-msg">{{ message }}</p>
    </div>
  </div>
</template>

<style scoped>
.dashboard-wrapper { padding: 3rem 5%; max-width: 900px; margin: 0 auto; }
.header-main { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
.profile-card { background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-top: 2rem; }
.card-header { margin-bottom: 2rem; }
.subtitle { color: #6b7280; font-size: 0.95rem; }
.form-group { margin-bottom: 1.5rem; }
label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151; }
input { width: 100%; padding: 12px; border: 1px solid #DEE4FE; border-radius: 8px; font-size: 1rem; }
.save-btn { background-color: #3e55f8; color: white; padding: 14px 28px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; }
.save-btn:hover { background-color: #2DE1AA; }
.logout-btn { color: #ef4444; border: 1px solid #ef4444; background: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; }
.status-message.success { color: #3e55f8; margin-top: 1rem; font-weight: 600; }
</style>