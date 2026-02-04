<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

interface User {
  name: string;
  role: string;
  mentor_profile?: {
    is_verified: number | boolean;
  }
}

const user = ref<User | null>(null);
const loading = ref(true);
const errorMsg = ref(''); 
const router = useRouter();

onMounted(async () => {
    try {
        const response = await axios.get('/api/user'); 
        user.value = response.data;
        console.log("Dashboard loaded for:", user.value?.name);
    } catch (err: any) {
        console.error("Auth Error:", err);
        errorMsg.value = "Session expired. Please log in again.";
    } finally {
        loading.value = false;
    }
});

const handleLogout = async () => {
    await axios.post('http://localhost:8000/logout');
    router.push('/login');
};
</script>

<template>
  <div class="dashboard-container">
    <div v-if="loading">Loading security session...</div>
    
    <div v-else-if="user">
        <div class="header">
            <h1>Welcome, {{ user.name }}</h1>
            <button @click="handleLogout" class="logout-btn">Logout</button>
        </div>
        
        <div v-if="user.role === 'mentor' && user.mentor_profile?.is_verified" class="banner active">
            <h3>Verification Status: Active</h3>
            <p>Your profile is now live in the directory.</p>
        </div>
        <div v-else class="banner pending">
            <h3>Verification Status: Pending</h3>
            <p>Our team is currently reviewing your profile.</p>
        </div>
    </div>

    <div v-else class="error-banner">
        <h2>Access Restricted</h2>
        <p>You must be logged in to view your dashboard.</p>
        <button @click="router.push('/login')" class="login-btn">Go to Login</button>
    </div>
  </div>
</template>

<style scoped>
.dashboard-container { padding: 2rem; font-family: 'SF-UI Text', sans-serif; }
.header { display: flex; justify-content: space-between; align-items: center; }
.banner { padding: 20px; border-radius: 12px; margin-top: 20px; border-left: 6px solid; }
.pending { background: #DEE4FE; border-color: #F4BD3F; color: #3e55f8; }
.active { background: #e6fffa; border-color: #2DE1AA; color: #065f46; }
.error-banner { text-align: center; margin-top: 50px; color: #ff4d4d; }
.logout-btn, .login-btn { padding: 8px 16px; background: #3e55f8; color: white; border: none; border-radius: 6px; cursor: pointer; }
</style>