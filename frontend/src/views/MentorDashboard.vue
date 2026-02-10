<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import FileUpload from '../components/FileUpload.vue'; 

const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user')));
const loading = ref(true);
const saving = ref(false);
const message = ref('');

const mentorProfile = ref({
    id: null,
    subject_expertise: '',
    bio: '',
    is_verified: false,
    document_path: null 
});

const fetchMentorData = async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/mentor/profile');
        mentorProfile.value = response.data.profile || response.data; 
        
        console.log("Current Verification Status:", mentorProfile.value.is_verified);
    } catch (err) {
        console.error("Failed to load mentor data", err);
    } finally {
        loading.value = false;
    }
};

const updateMentorProfile = async () => {
    saving.value = true;
    message.value = '';
    try {
        await axios.put('http://localhost:8000/api/mentor/profile', mentorProfile.value);
        message.value = "Profile updated successfully!";
    } catch (err) {
        message.value = "Failed to update profile.";
    } finally {
        saving.value = false;
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

onMounted(fetchMentorData);
</script>

<template>
  <div class="dashboard-container">
    <header class="header">
      <h1>Mentor Portal: {{ user?.name }}</h1>
      <button @click="handleLogout" class="logout-btn">Log Out</button>
    </header>
    
<div v-if="mentorProfile.id" 
     class="banner" 
     :class="Number(mentorProfile.is_verified) === 1 ? 'active' : 'pending'">
  <div class="banner-content">
    <span class="status-icon">{{ Number(mentorProfile.is_verified) === 1 ? '✅' : '⏳' }}</span>
    <div>
      <strong>{{ Number(mentorProfile.is_verified) === 1 ? 'Verified Mentor' : 'Verification Pending' }}</strong>
      <p v-if="Number(mentorProfile.is_verified) === 1">Your profile is verified and visible to international students.</p>
      <p v-else>Upload your ID below. Admin will review your profile shortly.</p>
    </div>
  </div>
</div>

    <div class="grid-layout">
<div class="profile-card">
    <h2>Professional Profile</h2>
    <div v-if="mentorProfile.id" class="photo-upload-section">
        <label>Profile Picture</label>
        <FileUpload 
            uploadType="photo" 
            :profileId="mentorProfile.id" 
            @upload-success="fetchMentorData" 
        />
    </div>
    <div v-else>
        <p>Loading photo tools...</p>
    </div>
    <hr />
</div>

<div class="verification-card">
    <h2>Identity Verification</h2>
    
    <div v-if="mentorProfile.id" class="upload-section">
        <div v-if="mentorProfile.is_verified" class="verified-badge">
            <span class="success-icon">✔️ Verified</span>
        </div>
        
        <div v-else-if="mentorProfile.document_path" class="awaiting-review">
            <span class="review-icon">📂 Pending Review</span>
            <FileUpload uploadType="document" :profileId="mentorProfile.id" @upload-success="fetchMentorData" />
        </div>

        <div v-else>
            <p class="subtitle">Please upload a valid Professional ID or Passport.</p>
            <FileUpload uploadType="document" :profileId="mentorProfile.id" @upload-success="fetchMentorData" />
        </div>
    </div>
    
    <div v-else>
        <p>Loading your profile details...</p>
    </div>
</div>
</div>
  </div>
</template>

<style scoped>
.dashboard-container { padding: 3rem 5%; max-width: 1100px; margin: 0 auto; }
.grid-layout { display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }

.banner { padding: 20px; border-radius: 12px; margin-bottom: 2rem; border-left: 6px solid; }
.banner-content { display: flex; align-items: center; gap: 1rem; }
.status-icon { font-size: 1.5rem; }

.pending { background: #DEE4FE; border-color: #F4BD3F; color: #3e55f8; }
.active { background: #e6fffa; border-color: #2DE1AA; color: #065f46; }

.profile-card, .verification-card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
.subtitle { color: #6b7280; font-size: 0.85rem; margin-bottom: 1.5rem; }

.form-group { margin-bottom: 1.5rem; }
label { display: block; font-weight: 600; margin-bottom: 0.5rem; }
input, .bio-area { width: 100%; padding: 12px; border: 1px solid #DEE4FE; border-radius: 8px; }

.submit-btn { width: 100%; background: #3e55f8; color: white; padding: 14px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; }
.logout-btn { color: #ef4444; background: none; border: 1px solid #ef4444; padding: 8px 16px; border-radius: 6px; cursor: pointer; }

.upload-section { margin-top: 1rem; border-top: 1px solid #eee; padding-top: 1.5rem; }
.success-text { color: #2DE1AA; font-weight: 600; font-size: 0.9rem; margin-top: 1rem; }
</style>