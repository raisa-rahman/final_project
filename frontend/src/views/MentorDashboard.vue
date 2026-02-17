<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import FileUpload from '../components/FileUpload.vue'; 

const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user')));
const loading = ref(true);
const selectedSlot = ref(null);
const isEditing = ref(false);

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

const handleLogout = async () => {
    try {
        await axios.post('http://localhost:8000/logout');
    } finally {
        localStorage.removeItem('user');
        router.push('/'); 
    }
};

const availabilities = ref([]);
const newSlot = ref({
    available_date: '',
    start_time: '',
    end_time: ''
});

const fetchAvailabilities = async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/availabilities');
        availabilities.value = response.data;
    } catch (err) {
        console.error("Failed to load slots", err);
    }
};

const addSlot = async () => {
    try {
        await axios.post('http://localhost:8000/api/availabilities', newSlot.value);
        newSlot.value = { available_date: '', start_time: '', end_time: '' };
        fetchAvailabilities();
    } catch (err) {
        alert("Error adding slot. Ensure date is in the future.");
    }
};

const deleteSlot = async (id) => {
    if (!confirm("Remove this time slot?")) return;
    try {
        await axios.delete(`http://localhost:8000/api/availabilities/${id}`);
        fetchAvailabilities();
    } catch (err) {
        console.error("Delete failed", err);
    }
};

const currentMonth = ref(new Date());
const calendarDays = computed(() => {
const year = currentMonth.value.getFullYear();
const month = currentMonth.value.getMonth();
  
const firstDay = new Date(year, month, 1).getDay();
const daysInMonth = new Date(year, month + 1, 0).getDate();
  
const days = [];
  for (let i = 0; i < firstDay; i++) days.push(null);
  
  for (let d = 1; d <= daysInMonth; d++) {
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    const slots = availabilities.value.filter(s => s.available_date === dateStr);
    days.push({ day: d, date: dateStr, slots });
  }
  return days;
});

const isToday = (dateStr) => {
  return dateStr === new Date().toISOString().split('T')[0];
};

const changeMonth = (offset) => {
  currentMonth.value = new Date(currentMonth.value.setMonth(currentMonth.value.getMonth() + offset));
};

    const defaultSlots = ref([]);
    const newDefault = ref({ day_of_week: 1, start_time: '', end_time: '' 
});

    const fetchDefaults = async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/mentor/default-availability');
        defaultSlots.value = response.data;
    } catch (err) { console.error("Load error", err); }
};

const addDefault = async () => {
    try {
        await axios.post('http://localhost:8000/api/mentor/default-availability', newDefault.value);
        fetchDefaults();
    } catch (err) { alert("Error saving pattern. Check console."); }
};

const confirmDelete = async () => {
    if (confirm("Permanently remove this recurring slot?")) {
        try {
            await axios.delete(`http://localhost:8000/api/mentor/default-availability/${selectedSlot.value.id}`);
            closeManageModal();
            fetchDefaults(); 
        } catch (err) {
            console.error("Delete failed:", err.response?.data);
            alert("Failed to delete. Check console for details.");
        }
    }
};

const getSlotStyle = (start, end) => {
    const startParts = start.split(':').map(Number);
    const endParts = end.split(':').map(Number);
    
    const startMinutes = startParts[0] * 60 + startParts[1];
    const endMinutes = endParts[0] * 60 + endParts[1];
    
    const top = (startMinutes / 1440) * 100;
    const height = ((endMinutes - startMinutes) / 1440) * 100;
    
    return {
        top: `${top}%`,
        height: `${height}%`
    };
};

const openManageModal = (slot) => {
    selectedSlot.value = { ...slot }; 
    isEditing.value = true;
};

const closeManageModal = () => {
    isEditing.value = false;
    selectedSlot.value = null;
};

const updateDefault = async () => {
    try {
        await axios.put(`http://localhost:8000/api/mentor/default-availability/${selectedSlot.value.id}`, {
            day_of_week: selectedSlot.value.day_of_week,
            start_time: selectedSlot.value.start_time,
            end_time: selectedSlot.value.end_time
        });
        closeManageModal();
        fetchDefaults();
    } catch (err) {
        console.error("Backend Error Details:", err.response?.data);
        alert(err.response?.data?.message || "Server Error. Check your Laravel logs.");
    }
};

onMounted(() => {
    fetchMentorData(); 
    fetchAvailabilities();
    fetchDefaults();
});

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

    <div class="availability-card calendar-weekly">
      <h2>Weekly Default Availability</h2>
      <p class="subtitle">Click a bar to delete or use the form below to add recurring blocks.</p>

      <div class="week-grid">
        <div class="time-gutter">
          <div v-for="hour in 24" :key="hour" class="hour-label">{{ hour-1 }}:00</div>
        </div>
        
        <div v-for="(day, index) in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" 
             :key="index" class="day-column">
          <div class="column-header">{{ day }}</div>
          <div class="slots-container">
            <div v-for="slot in defaultSlots.filter(s => s.day_of_week === index)" 
     :key="slot.id" 
     class="availability-bar" 
     :style="getSlotStyle(slot.start_time, slot.end_time)"
     @click="openManageModal(slot)"> <strong>available</strong>
    <span>{{ slot.start_time.substring(0,5) }} - {{ slot.end_time.substring(0,5) }}</span>
</div>

<div v-if="isEditing" class="modal-overlay" @click.self="closeManageModal">
    <div class="edit-modal">
        <h3>Manage Availability Block</h3>
        <div class="form-group">
            <label>Day of Week</label>
            <select v-model="selectedSlot.day_of_week">
                <option v-for="(day, i) in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" :key="i" :value="i">{{day}}</option>
            </select>
        </div>
        <div class="form-group">
            <label>Start Time</label>
            <input type="time" v-model="selectedSlot.start_time">
        </div>
        <div class="form-group">
            <label>End Time</label>
            <input type="time" v-model="selectedSlot.end_time">
        </div>
        
        <div class="modal-actions">
            <button @click="updateDefault" class="save-btn">Save Changes</button>
            <button @click="confirmDelete" class="delete-btn">Delete Slot</button>
            <button @click="closeManageModal" class="cancel-btn">Cancel</button>
        </div>
    </div>
</div>
          </div>
        </div>
      </div>

      <div class="slot-form-inline">
        <select v-model="newDefault.day_of_week">
          <option v-for="(day, i) in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" :key="i" :value="i">{{day}}</option>
        </select>
        <input type="time" v-model="newDefault.start_time">
        <input type="time" v-model="newDefault.end_time">
        <button @click="addDefault" class="add-btn">Add Block</button>
      </div>
    </div>

    <div class="availability-card calendar-view">
      <div class="calendar-header">
        <h2>Schedule Overview</h2>
        <div class="month-nav">
          <button @click="changeMonth(-1)">« Prev</button>
          <h3>{{ currentMonth.toLocaleString('default', { month: 'long', year: 'numeric' }) }}</h3>
          <button @click="changeMonth(1)">Next »</button>
        </div>
      </div>

      <div class="calendar-grid">
        <div v-for="label in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" :key="label" class="day-label">
          {{ label }}
        </div>
        
        <div v-for="(day, index) in calendarDays" :key="index" 
             class="calendar-day" 
             :class="{ 'today': day && isToday(day.date), 'empty': !day }">
          <template v-if="day">
            <span class="day-number">{{ day.day }}</span>
            <div class="day-slots">
              <div v-for="slot in day.slots" :key="slot.id" 
                   class="mini-slot" 
                   :class="{ 'mini-booked': slot.is_booked }"
                   :title="`${slot.start_time.substring(0,5)} - ${slot.end_time.substring(0,5)}`"
                   @click="deleteSlot(slot.id)">
                {{ slot.start_time.substring(0,5) }}
              </div>
            </div>
          </template>
        </div>
      </div>

      <hr />
      
      <div class="slot-form">
        <h3>Add One-off Slot</h3>
        <div class="form-inputs">
          <input type="date" v-model="newSlot.available_date" :min="new Date().toISOString().split('T')[0]">
          <input type="time" v-model="newSlot.start_time">
          <input type="time" v-model="newSlot.end_time">
          <button @click="addSlot" class="add-btn">Add Slot</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dashboard-container { padding: 3rem 5%; margin: 0 auto; }
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

.availability-card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); grid-column: span 2; }
.time-inputs { display: flex; gap: 1rem; }
.add-btn { width: 100%; background: #3e55f8; color: white; padding: 12px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; margin-top: 0.5rem; }
.slot-item { display: flex; align-items: center; justify-content: space-between; padding: 1rem; border-bottom: 1px solid #eee; }
.badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; }
.free { background: #e6fffa; color: #065f46; }
.booked { background: #fff5f5; color: #c53030; }
.remove-btn { background: none; border: none; color: #ff4d4d; font-size: 1.5rem; cursor: pointer; }

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: #eef2ff;
    border: 1px solid #DEE4FE;
    margin: 1.5rem 0;
}

.day-label {
    background: #3e55f8;
    color: white;
    padding: 0.5rem;
    text-align: center;
    font-weight: bold;
    font-size: 0.8rem;
}

.calendar-day {
    background: white;
    min-height: 100px;
    padding: 0.5rem;
    position: relative;
}

.calendar-day.today {
    background: #fffbea;
    border: 2px solid #F4BD3F;
}

.calendar-day.empty { background: #f9fafb; }

.day-number { font-weight: bold; font-size: 0.9rem; color: #1a1f3c; }

.day-slots { margin-top: 0.5rem; display: flex; flex-direction: column; gap: 4px; }

.mini-slot {
    font-size: 0.7rem;
    padding: 2px 4px;
    background: #e6fffa;
    color: #065f46;
    border-radius: 4px;
    cursor: pointer;
    text-align: center;
}

.mini-slot.mini-booked {
    background: #fff5f5;
    color: #c53030;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.month-nav { display: flex; align-items: center; gap: 1rem; }
.month-nav button { background: #eee; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }

.week-grid {
    display: grid;
    grid-template-columns: 50px repeat(7, 1fr);
    background: white;
    border: 1px solid #DEE4FE;
    height: 600px;
    overflow-y: scroll;
}

.day-column {
    border-left: 1px solid #eee;
    position: relative;
    background-image: linear-gradient(#eee 1px, transparent 1px);
    background-size: 100% 25px; 
}

.column-header {
    text-align: center;
    padding: 10px;
    font-weight: bold;
    border-bottom: 2px solid #3e55f8;
    background: #f8faff;
    position: sticky;
    top: 0;
    z-index: 10;
}

.slots-container {
    position: relative;
    height: 1440px; 
}

.availability-bar {
    position: absolute;
    left: 5%;
    width: 90%;
    background: rgba(62, 85, 248, 0.7);
    border: 1px solid #3e55f8;
    color: white;
    border-radius: 4px;
    padding: 4px;
    font-size: 0.75rem;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.2s;
}

.availability-bar:hover {
    transform: scale(1.02);
    z-index: 20;
    background: #ef4444; 
}

.hour-label {
    height: 60px; 
    font-size: 0.7rem;
    color: #9ca3af;
    text-align: right;
    padding-right: 5px;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.edit-modal {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    width: 400px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.modal-actions {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    margin-top: 1.5rem;
}

.save-btn { background: #3e55f8; color: white; border: none; padding: 10px; border-radius: 6px; cursor: pointer; }
.delete-btn { background: #fee2e2; color: #ef4444; border: 1px solid #ef4444; padding: 10px; border-radius: 6px; cursor: pointer; }
.cancel-btn { background: none; border: none; color: #6b7280; padding: 5px; cursor: pointer; }
</style>
