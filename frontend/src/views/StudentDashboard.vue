<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const user = JSON.parse(localStorage.getItem('user'));
const loading = ref(false);
const mentorsLoading = ref(true); 
const mentors = ref([]); 
const message = ref('');
const error = ref(false);

const searchQuery = ref({
    subject: '',
    university: ''
});

const profile = ref({
    target_university: '',
    target_country: ''
});

const filteredMentors = computed(() => {
    return mentors.value.filter(mentor => {
        const matchesSubject = !searchQuery.value.subject || 
            mentor.subject_expertise?.toLowerCase().includes(searchQuery.value.subject.toLowerCase());
            
        const matchesUniversity = !searchQuery.value.university || 
            mentor.university?.name?.toLowerCase().includes(searchQuery.value.university.toLowerCase()) || 
            mentor.target_university?.toLowerCase().includes(searchQuery.value.university.toLowerCase());

        return matchesSubject && matchesUniversity;
    });
});

const fetchMentors = async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/mentors/directory');
        const rawData = Array.isArray(response.data) ? response.data : (response.data.mentors || []);
        mentors.value = rawData.filter(mentor => Number(mentor.is_verified) === 1);
        console.log("Verified Mentors initialized:", mentors.value.length);
    } catch (err) {
        console.error("Directory Load Error. Check api.php import.", err);
    } finally {
        mentorsLoading.value = false;
    }
};

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
        message.value = "Failed to update profile.";
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

onMounted(() => {
    fetchProfile();
    fetchMentors();
});
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
      <div class="form-group">
        <label>Target University</label>
        <input v-model="profile.target_university" type="text" placeholder="e.g. City St George's">
      </div>
      <div class="form-group">
        <label>Target Country</label>
        <input v-model="profile.target_country" type="text" placeholder="e.g. United Kingdom">
      </div>
      <button @click="updateProfile" :disabled="loading" class="save-btn">
        {{ loading ? 'Saving...' : 'Update Goals' }}
      </button>
      <p v-if="message" :class="{ 'status-msg': true, 'error-text': error }">{{ message }}</p>
    </div>

    <div class="directory-section">
      <div class="section-header">
        <h2>Available Verified Mentors</h2>
        <div class="search-filters">
          <input v-model="searchQuery.subject" placeholder="Search by Expertise..." class="search-input">
          <input v-model="searchQuery.university" placeholder="Search by University..." class="search-input">
        </div>
      </div>

      <div v-if="mentorsLoading" class="loading-state">
        Finding mentors...
      </div>

<div v-else class="mentor-grid">
  <div v-for="mentor in filteredMentors" :key="mentor.id" class="mentor-card">
    <div class="mentor-avatar">
       <img :src="mentor.profile_photo_path ? `http://localhost:8000/storage/${mentor.profile_photo_path}` : 'https://via.placeholder.com/80'">
       <span class="verify-tag">Verified ✅</span>
    </div>
    <div class="mentor-info">
       <h3>{{ mentor.user?.name || 'Alumni Mentor' }}</h3>
       <p class="expertise">{{ mentor.subject_expertise }}</p>
        <button 
        @click="router.push(`/mentor/profile/${mentor.id}`)" 
        class="connect-btn"
        >
        View Profile
        </button>    
    </div>
  </div>
  
  <div v-if="filteredMentors.length === 0" class="empty-state">
    No verified mentors found.
  </div>
</div>
    </div>
  </div>
</template>

<style scoped>
/* Keeping your existing styles */
.dashboard-wrapper { padding: 3rem 5%; max-width: 900px; margin: 0 auto; }
.header-main { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
.profile-card { background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-top: 2rem; }
.form-group { margin-bottom: 1.5rem; }
label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151; }
input { width: 100%; padding: 12px; border: 1px solid #DEE4FE; border-radius: 8px; font-size: 1rem; }
.save-btn { background-color: #3e55f8; color: white; padding: 14px 28px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; }
.save-btn:hover { background-color: #2DE1AA; }
.logout-btn { color: #ef4444; border: 1px solid #ef4444; background: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; }
.directory-section { margin-top: 3rem; }
.section-header { margin-bottom: 1.5rem; }
.search-filters { display: flex; gap: 1rem; margin-bottom: 1rem; }
.search-input { flex: 1; }
.mentor-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem; }
.mentor-card { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #DEE4FE; text-align: center; }
.mentor-avatar img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #3e55f8; }
.verify-tag { background: #2DE1AA; color: white; font-size: 0.7rem; padding: 2px 8px; border-radius: 20px; font-weight: 700; }
.expertise { color: #3e55f8; font-weight: 700; }
.connect-btn { background: #3e55f8; color: white; border: none; padding: 10px; border-radius: 8px; width: 100%; cursor: pointer; }
.loading-state, .empty-state { grid-column: 1 / -1; text-align: center; padding: 2rem; color: #6b7280; }
</style>