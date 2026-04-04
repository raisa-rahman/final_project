<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router'; 

const router = useRouter(); 

interface User { id: number; name: string; email: string; }
interface Mentor { 
  id: number; is_verified: boolean | number; user: User; 
  profile_photo_path?: string; document_path?: string; 
}
interface University { id: number; name: string; country: string; }

const mentors = ref<Mentor[]>([]);
const universities = ref<University[]>([]);
const loading = ref(true);

const newUniversityName = ref('');
const newUniversityCountry = ref('');
const isSubmittingUniv = ref(false);
const editingUnivId = ref<number | null>(null);
const editUnivData = ref({ name: '', country: '' });

const fetchData = async () => {
  loading.value = true;
  try {
    const [mentorRes, univRes] = await Promise.all([
      axios.get('http://localhost:8000/api/admin/mentors', { withCredentials: true }),
      axios.get('http://localhost:8000/api/universities')
    ]);
    mentors.value = mentorRes.data;
    universities.value = univRes.data;
  } catch (err) {
    console.error("Failed to fetch data", err);
  } finally { loading.value = false; }
};

const addUniversity = async () => {
    if (!newUniversityName.value || !newUniversityCountry.value) return alert("Fill all fields");
    isSubmittingUniv.value = true;
    try {
        await axios.post('http://localhost:8000/api/admin/universities', {
            name: newUniversityName.value, country: newUniversityCountry.value
        });
        newUniversityName.value = ''; newUniversityCountry.value = '';
        fetchData();
    } catch (err) { alert("Error adding record"); } 
    finally { isSubmittingUniv.value = false; }
};

const deleteUniversity = async (id: number) => {
    if (!confirm("Are you sure you want to delete this university?")) return;
    try {
        await axios.delete(`http://localhost:8000/api/admin/universities/${id}`);
        fetchData();
    } catch (err) { alert("Error deleting university"); }
};

const startEditUniv = (univ: University) => {
    editingUnivId.value = univ.id;
    editUnivData.value = { name: univ.name, country: univ.country };
};

const saveUnivEdit = async (id: number) => {
    try {
        await axios.put(`http://localhost:8000/api/admin/universities/${id}`, editUnivData.value);
        editingUnivId.value = null;
        fetchData();
    } catch (err) { alert("Error updating university"); }
};

const toggleVerification = async (profileId: number) => {
    try {
        await axios.post(`http://localhost:8000/api/admin/mentors/${profileId}/toggle-verify`);
        fetchData();
    } catch (err) { alert("Error updating status"); }
};

const adminLogout = async () => {
    await axios.post('http://localhost:8000/logout');
    router.push('/adminlogin');
};

const updateStatus = async (id: number, status: number) => {
  try {
    const response = await axios.post(`http://localhost:8000/api/admin/mentors/${id}/status`, {
      is_verified: status
    });
    
    console.log("Server Response:", response.data);
    await fetchData(); 
    
    alert(status === 2 ? "Mentor rejected and ID deleted." : "Mentor approved.");
  } catch (err) {
    console.error(err);
    alert("Error updating status");
  }
};

onMounted(fetchData);
</script>

<template>
  <div class="admin-dashboard">
    <header class="admin-header">
        <div class="header-content">
            <h2>Admin Management Portal</h2>
            <button @click="adminLogout" class="logout-btn">Admin Logout</button>
        </div>
    </header>

    <div v-if="loading">Loading records...</div>
    
    <div v-else class="dashboard-grid">
      <section class="admin-section">
  <h3>Mentor Verification</h3>
  <div class="mentor-list">
    <div v-for="mentor in mentors" :key="mentor.id" class="mentor-row">
      
      <div class="info-group">
        <img v-if="mentor.profile_photo_path" 
             :src="'http://localhost:8000/storage/' + mentor.profile_photo_path" 
             class="admin-thumbnail" />
        <div v-else class="admin-thumbnail placeholder">No Photo</div>
        
        <div class="info">
          <strong>{{ mentor.user?.name || 'Unknown Mentor' }}</strong>
          <span class="email">{{ mentor.user?.email }}</span>
          
          <a v-if="mentor.document_path" 
             :href="'http://localhost:8000/storage/' + mentor.document_path" 
             target="_blank" 
             class="view-id-link">View University ID</a>
          <span v-else class="no-id">No ID Uploaded</span>
        </div>
      </div>

      <div class="status-pill" 
           :class="{ 
             'verified': Number(mentor.is_verified) === 1, 
             'pending-pill': Number(mentor.is_verified) === 0, 
             'rejected-pill': Number(mentor.is_verified) === 2 
           }">
        {{ Number(mentor.is_verified) === 1 ? 'Approved' : (Number(mentor.is_verified) === 2 ? 'Unapproved' : 'Pending') }}
      </div>

      <div class="admin-actions">
        <button v-if="Number(mentor.is_verified) !== 1" 
                @click="updateStatus(mentor.id, 1)" 
                class="btn-success">
          Approve
        </button>
        
        <button v-if="Number(mentor.is_verified) !== 2" 
                @click="updateStatus(mentor.id, 2)" 
                class="btn-danger">
          Reject
        </button>
      </div>
    </div>
  </div>
</section>

      <section class="admin-section">
  <h3>University Management</h3>
  
  <div class="university-form card">
      <p>Add New Institution:</p>
      <div class="form-row">
          <input v-model="newUniversityName" placeholder="Name" class="admin-input" />
          <input v-model="newUniversityCountry" placeholder="Country" class="admin-input" />
          <button @click="addUniversity" :disabled="isSubmittingUniv" class="btn-success">
              {{ isSubmittingUniv ? 'Saving...' : 'Add' }}
          </button>
      </div>
  </div>

  <div class="university-list">
    <div v-for="univ in universities" :key="univ.id" class="univ-row">
      <template v-if="editingUnivId !== univ.id">
        <div class="univ-info">
          <strong>{{ univ.name }}</strong> ({{ univ.country }})
        </div>
        <div class="univ-actions">
          <button @click="startEditUniv(univ)" class="btn-edit">Edit</button>
          <button @click="deleteUniversity(univ.id)" class="btn-text danger">Delete</button>
        </div>
      </template>

      <template v-else>
        <div class="edit-univ-row">
          <input v-model="editUnivData.name" class="admin-input small" />
          <input v-model="editUnivData.country" class="admin-input small" />
          <button @click="saveUnivEdit(univ.id)" class="btn-text success">Save</button>
          <button @click="editingUnivId = null" class="btn-text">Cancel</button>
        </div>
      </template>
    </div>
  </div>
</section>
    </div>
  </div>
</template>

<style scoped>

.dashboard-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 2rem;
}

.admin-section {
  background: white;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid var(--sc-light-purple);
  box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}

.university-list {
  margin-top: 20px;
  max-height: 400px;
  overflow-y: auto;
}

.univ-row {
  padding: 10px;
  border-bottom: 1px solid #eee;
  font-size: 0.9rem;
}

.form-row {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 20px;
}

.admin-input {
  padding: 8px;
  border: 1px solid var(--sc-light-purple);
  border-radius: 6px;
}

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
  font-family: var(--font-body); 
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

.univ-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  border-bottom: 1px solid #eee;
}

.univ-actions {
  display: flex;
  gap: 10px;
}

.edit-univ-row {
  display: flex;
  gap: 8px;
  width: 100%;
}

.admin-input.small {
  padding: 4px 8px;
  font-size: 0.85rem;
}

.btn-edit {
  background: none;
  border: 1px solid var(--sc-blue);
  color: var(--sc-blue);
  padding: 2px 8px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8rem;
}

.btn-text.success { color: var(--sc-green); }
.btn-text.danger { color: #ff4d4d; }

.pending-pill { 
  background: var(--sc-light-purple); 
  color: var(--sc-blue); 
}

.rejected-pill { 
  background: #fee2e2; 
  color: #ff4d4d; 
}

.admin-actions {
  display: flex;
  gap: 8px;
}
</style>