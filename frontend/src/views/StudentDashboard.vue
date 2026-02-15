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
const favoriteIds = ref([]); 

const isMentorFavorited = (id) => favoriteIds.value.includes(id);

const toggleFavorite = async (mentorId) => {
    try {
        const response = await axios.post(`http://localhost:8000/api/mentors/${mentorId}/favorite`);
        
        if (response.data.status === 'added') {
            if (!favoriteIds.value.includes(mentorId)) {
                favoriteIds.value.push(mentorId);
            }
        } else if (response.data.status === 'removed') {
            favoriteIds.value = favoriteIds.value.filter(id => id !== mentorId);
        }
        
        console.log("Updated Favorite IDs:", favoriteIds.value);
    } catch (err) {
        console.error("Error toggling favorite. Check if User.php has favorites() method.", err);
    }
};

const savedMentors = computed(() => {
    return mentors.value.filter(mentor => favoriteIds.value.includes(mentor.id));
});

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

const fetchFavorites = async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/student/favorites');
        favoriteIds.value = response.data.map(fav => fav.mentor_profile_id);
    } catch (err) {
        console.error("Failed to load favorites", err);
    }
};

onMounted(() => {
    fetchProfile();
    fetchMentors();
    fetchFavorites(); 
});

</script>

<template>
  <div class="dashboard-container">
    <aside class="sidebar">
      <div class="profile-card">
        <h2>Educational Goals</h2>
        <div class="form-group">
          <label>University</label>
          <input v-model="profile.target_university" type="text">
        </div>
        <div class="form-group">
          <label>Country</label>
          <input v-model="profile.target_country" type="text">
        </div>
        <button @click="updateProfile" :disabled="loading" class="save-btn small">Update</button>
      </div>

      <div class="saved-mentors-section">
        <h3>Saved Mentors ({{ savedMentors.length }})</h3>
        <div v-if="savedMentors.length === 0" class="empty-mini">No bookmarks yet.</div>
        <ul class="saved-list">
          <li v-for="mentor in savedMentors" :key="mentor.id" @click="router.push(`/mentor/profile/${mentor.id}`)">
            {{ mentor.user?.name }} - <span class="mini-expertise">{{ mentor.subject_expertise }}</span>
          </li>
        </ul>
      </div>
    </aside>

    <main class="main-content">
      <header class="dashboard-header">
        <div class="header-main">
          <h1>Welcome, {{ user?.name }}</h1>
          <button @click="handleLogout" class="logout-btn">Log Out</button>
        </div>
      </header>

      <div class="directory-section">
        <div class="section-header">
          <h2>Verified Mentors</h2>
          <div class="search-filters">
            <input v-model="searchQuery.subject" placeholder="Filter by Expertise..." class="search-input">
            <input v-model="searchQuery.university" placeholder="Filter by University..." class="search-input">
          </div>
        </div>

        <div v-if="mentorsLoading" class="loading-state">Finding mentors...</div>

        <div v-else class="mentor-grid">
          <div v-for="mentor in filteredMentors" :key="mentor.id" class="mentor-card">
            <button @click.stop="toggleFavorite(mentor.id)" class="fav-btn">
               <span :class="{ 'heart-active': isMentorFavorited(mentor.id) }">♥</span>
            </button>
            
            <div class="mentor-avatar">
               <img :src="mentor.profile_photo_path ? `http://localhost:8000/storage/${mentor.profile_photo_path}` : 'https://via.placeholder.com/80'">
               <span class="verify-tag">Verified ✅</span>
            </div>
            <div class="mentor-info">
               <h3>{{ mentor.user?.name }}</h3>
               <p class="expertise">{{ mentor.subject_expertise }}</p>
               <button @click="router.push(`/mentor/profile/${mentor.id}`)" class="connect-btn">View Profile</button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
/* Main Container - takes up full width */
.dashboard-container {
  display: flex;
  gap: 2rem;
  padding: 2rem;
  width: 100%; /* Ensure it spans the whole screen */
  max-width: 100vw; /* Remove narrow 900px limits */
  margin: 0;
  align-items: flex-start;
  background-color: #fbfcff;
  min-height: 100vh;
}

/* Sidebar - fixed width on the left */
.sidebar {
  flex: 0 0 320px;
  position: sticky;
  top: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Main Content - grows to fill all remaining horizontal space */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Header Styles */
.header-main {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.logout-btn {
  color: #ef4444;
  border: 1px solid #ef4444;
  background: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}

/* Profile and Saved Cards */
.profile-card, .saved-mentors-section {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  border: 1px solid #DEE4FE;
}

.form-group { margin-bottom: 1rem; }
label { display: block; font-weight: 600; margin-bottom: 0.3rem; color: #374151; font-size: 0.9rem; }
input { width: 100%; padding: 10px; border: 1px solid #DEE4FE; border-radius: 8px; font-size: 0.9rem; }

.save-btn {
  background-color: #3e55f8;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  width: 100%;
}

/* Directory and Grid */
.mentor-grid {
  display: grid;
  /* Adjusts number of columns automatically based on screen width */
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.mentor-card {
  position: relative;
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  border: 1px solid #DEE4FE;
  text-align: center;
}

.mentor-avatar img {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #3e55f8;
  margin-bottom: 1rem;
}

.fav-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  background: none;
  border: none;
  font-size: 1.8rem;
  cursor: pointer;
  color: #e5e7eb;
}

.heart-active { color: #ef4444; }

.expertise { color: #3e55f8; font-weight: 700; margin: 0.5rem 0; }

.connect-btn {
  background: #3e55f8;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  width: 100%;
  font-weight: 600;
  cursor: pointer;
  margin-top: 1rem;
}

/* Responsive */
@media (max-width: 1024px) {
  .dashboard-container { flex-direction: column; padding: 1rem; }
  .sidebar { flex: 1; width: 100%; position: static; }
}
</style>