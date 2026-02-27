<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router'; 

const router = useRouter(); 

interface User {
  id: number;
  name: string;
  email: string;
}

interface Mentor {
  id: number;
  is_verified: boolean | number;
  user: User;
  profile_photo_path?: string;
  document_path?: string; 
}

const adminLogout = async () => {
    await axios.post('http://localhost:8000/logout');
    router.push('/adminlogin');
};

const mentors = ref<Mentor[]>([]);
const loading = ref(true);

const fetchAllMentors = async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/admin/mentors', { 
            withCredentials: true 
        });
        mentors.value = response.data;
    } catch (err) {
        console.error("Failed to fetch mentors", err);
    } finally {
        loading.value = false;
    }
};

const toggleVerification = async (profileId: number) => {
    try {
        await axios.post(`/api/admin/mentors/${profileId}/toggle-verify`);
        fetchAllMentors();
    } catch (err) {
        alert("Error updating status");
    }
};

onMounted(fetchAllMentors);
</script>
<template>
  <div class="admin-dashboard">
    <header class="admin-header">
        <div class="header-content">
            <h2>Mentor Verification Management</h2>
            <button @click="adminLogout" class="logout-btn">Admin Logout</button>
        </div>
        <p>Review and approve mentor applications</p>
    </header>

    <div v-if="loading">Loading records...</div>
    
    <div v-else class="mentor-list">
      <div v-for="mentor in mentors" :key="mentor.id" class="mentor-row">
        
        <div class="info-group">
           <img v-if="mentor.profile_photo_path" 
                :src="'http://localhost:8000/storage/' + mentor.profile_photo_path" 
                class="admin-thumbnail" />
           <div v-else class="admin-thumbnail placeholder">No Photo</div>
           
           <div class="info">
             <strong>{{ mentor.user?.name }}</strong>
             <span class="email">{{ mentor.user?.email }}</span>
             
             <a v-if="mentor.document_path" 
                :href="'http://localhost:8000/storage/' + mentor.document_path" 
                target="_blank" 
                class="view-id-link">
                View University ID
             </a>
             <span v-else class="no-id">No ID Uploaded</span>
           </div>
        </div>
        
        <div class="status-pill" :class="mentor.is_verified ? 'verified' : 'pending'">
          {{ mentor.is_verified ? 'Approved' : 'Unapproved' }}
        </div>

        <button @click="toggleVerification(mentor.id)" :class="mentor.is_verified ? 'btn-danger' : 'btn-success'">
          {{ mentor.is_verified ? 'Unapprove' : 'Approve Mentor' }}
        </button>

      </div>
    </div>
  </div>
</template>

<style scoped>
:root {
  --sc-blue: #3e55f8;       
  --sc-yellow: #F4BD3F;     
  --sc-green: #2DE1AA;       
  --sc-light-purple: #DEE4FE;
  --sc-navy: #1a1f3c;        
  
  --font-heading: 'Red Hat Display', sans-serif;
  --font-body: 'SF-UI Text', 'Arial', sans-serif;
}

.admin-dashboard { 
  padding: 40px; 
  max-width: 1000px; 
  margin: 0 auto; 
  font-family: var(--font-body); /* Standardized typography [cite: 3630] */
  background-color: #fbfcff;
  min-height: 100vh;
}

.admin-header { 
  margin-bottom: 30px; 
  border-bottom: 2px solid var(--sc-light-purple); 
  padding-bottom: 20px; 
}

h1, h2, h3 {
  font-family: var(--font-heading); 
  color: var(--sc-navy);
  font-weight: 700;
}

.header-content { display: flex; justify-content: space-between; align-items: center; }

.mentor-row { 
  display: flex; 
  align-items: center; 
  justify-content: space-between; 
  padding: 20px; 
  background: #fff; 
  border: 1px solid var(--sc-light-purple); 
  border-radius: 12px; 
  margin-bottom: 15px; 
  box-shadow: 0 4px 15px rgba(0,0,0,0.02); 
}

.info { display: flex; flex-direction: column; }

.email { 
  font-size: 0.85rem; 
  color: #6b7280; 
  font-family: var(--font-body);
}

.status-pill { 
  padding: 4px 12px; 
  border-radius: 20px; 
  font-size: 0.8rem; 
  font-weight: 800; 
  text-transform: uppercase;
}

.pending { 
  background: var(--sc-light-purple); 
  color: var(--sc-blue); 
}

.verified { 
  background: #e6fffa; 
  color: var(--sc-green); 
}

.btn-success { 
  background: var(--sc-green); 
  color: white; 
  border: none; 
  padding: 10px 16px; 
  border-radius: 8px; 
  cursor: pointer; 
  font-family: var(--font-heading);
  font-weight: 600;
}

.btn-danger { 
  background: #ff4d4d; 
  color: white; 
  border: none; 
  padding: 10px 16px; 
  border-radius: 8px; 
  cursor: pointer; 
  font-family: var(--font-heading);
}

.logout-btn { 
  background: var(--sc-blue); 
  color: white; 
  border: none; 
  padding: 8px 16px; 
  border-radius: 6px; 
  cursor: pointer; 
  font-family: var(--font-heading);
}

.info-group { display: flex; align-items: center; gap: 15px; }

.admin-thumbnail {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover; 
  border: 2px solid var(--sc-light-purple);
  margin-right: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
}

.view-id-link {
  font-size: 0.8rem;
  color: var(--sc-blue); 
  text-decoration: underline;
  margin-top: 4px;
  display: block;
}

.no-id {
  font-size: 0.75rem;
  color: #9ca3af;
  font-style: italic;
}
</style>