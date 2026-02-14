<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const mentor = ref(null);
const loading = ref(true);
const mentorId = route.params.id;

const fetchMentorDetails = async () => {
    loading.value = true; 
    try {
        const response = await axios.get(`http://localhost:8000/api/mentors/profile/${mentorId}`);
        mentor.value = response.data;
        loading.value = false; 
    } catch (err) {
        console.error("API Error:", err.response?.data || err.message);
        router.push('/student/login/dashboard'); 
        loading.value = false; 
    }
};

onMounted(fetchMentorDetails);
</script>

<template>
  <div class="profile-page">
    <div v-if="loading" class="loading">Loading profile...</div>
    
    <div v-else-if="mentor" class="profile-container">
      <button @click="router.back()" class="back-link">← Back to Directory</button>
      
      <div class="profile-header">
        <div class="avatar-section">
          <img 
            :src="mentor.profile_photo_path ? `http://localhost:8000/storage/${mentor.profile_photo_path}` : 'https://via.placeholder.com/150'" 
            class="profile-img"
          >
          <div class="status-badge">Verified Mentor ✅</div>
        </div>
        
        <div class="info-section">
          <h1>{{ mentor.user?.name }}</h1>
          <p class="expertise-tag">{{ mentor.subject_expertise }}</p>
          <p class="uni-name">{{ mentor.university?.name || mentor.target_university }}</p>
        </div>
      </div>

      <div class="profile-content">
        <section class="bio-card">
          <h3>About Me</h3>
          <p>{{ mentor.bio || 'This mentor hasn’t added a bio yet.' }}</p>
        </section>

        <section class="expertise-card">
          <h3>Mentorship Focus</h3>
          <ul>
            <li>Application Strategy</li>
            <li>Cultural Adaptation in {{ mentor.target_country }}</li>
            <li>Subject Deep-dives</li>
          </ul>
          <button class="book-now-btn">Request a Session</button>
        </section>
      </div>
    </div>
  </div>
</template>

<style scoped>
.profile-page { padding: 3rem 5%; max-width: 1000px; margin: 0 auto; }
.profile-header { display: flex; gap: 3rem; align-items: center; margin: 2rem 0; background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
.profile-img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid #3e55f8; }
.expertise-tag { color: #3e55f8; font-weight: 700; font-size: 1.2rem; margin: 0.5rem 0; }
.profile-content { display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem; }
.bio-card, .expertise-card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
.book-now-btn { width: 100%; background: #3e55f8; color: white; border: none; padding: 15px; border-radius: 8px; font-weight: 700; cursor: pointer; margin-top: 1rem; }
.back-link { background: none; border: none; color: #6b7280; cursor: pointer; font-weight: 600; }
</style>