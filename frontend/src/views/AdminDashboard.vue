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
.admin-dashboard { padding: 40px; max-width: 1000px; margin: 0 auto; font-family: 'SF Pro Text', sans-serif; }
.header-content { display: flex; justify-content: space-between; align-items: center; }
.admin-header { margin-bottom: 30px; border-bottom: 2px solid #DEE4FE; padding-bottom: 20px; }
.mentor-row { display: flex; align-items: center; justify-content: space-between; padding: 20px; background: #fff; border: 1px solid #DEE4FE; border-radius: 12px; margin-bottom: 15px; }
.info { display: flex; flex-direction: column; }
.email { font-size: 0.85rem; color: #6b7280; }
.status-pill { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
.pending { background: #DEE4FE; color: #3e55f8; }
.verified { background: #e6fffa; color: #065f46; }
.btn-success { background: #2DE1AA; color: white; border: none; padding: 10px 16px; border-radius: 8px; cursor: pointer; }
.btn-danger { background: #ff4d4d; color: white; border: none; padding: 10px 16px; border-radius: 8px; cursor: pointer; }
.logout-btn { background: #3e55f8; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; }
.info-group { display: flex; align-items: center; gap: 15px; }

.admin-thumbnail {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #DEE4FE;
}

.placeholder {
  background: #f3f4f6;
  font-size: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
  text-align: center;
}

.admin-thumbnail {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover; /* Ensures the photo doesn't stretch */
  margin-right: 15px;
  border: 2px solid #DEE4FE;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
  font-size: 10px;
  color: #9ca3af;
}
.view-id-link {
  font-size: 0.8rem;
  color: #3e55f8;
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