<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import FileUpload from '../components/FileUpload.vue'; 

const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user')));
const loading = ref(true);

const meetingRequests = ref([]);
const upcomingMeetings = ref([]);

const mentorProfile = ref({
    id: null,
    subject_expertise: '',
    is_verified: false,
    document_path: null 
});

const fetchData = async () => {
    try {
        const [profileRes, requestsRes, upcomingRes] = await Promise.all([
            axios.get('http://localhost:8000/api/mentor/profile'),
            axios.get('http://localhost:8000/api/mentor/meetings/requests'), 
            axios.get('http://localhost:8000/api/mentor/meetings/upcoming') 
        ]);
        mentorProfile.value = profileRes.data.profile || profileRes.data;
        meetingRequests.value = requestsRes.data;
        upcomingMeetings.value = upcomingRes.data;
    } catch (err) {
        console.error("Failed to load dashboard data", err);
    } finally {
        loading.value = false;
    }
};

const handleMeetingAction = async (id, action) => {
    try {
        const url = action === 'approve' 
            ? `http://localhost:8000/api/mentor/meetings/${id}/approve` 
            : `http://localhost:8000/api/mentor/meetings/${id}/decline`;

        await axios.post(url);
        fetchData();
    } catch (err) {
        console.error("Action failed:", err);
        alert("Error updating meeting status.");
    }
};

const addToGoogleCalendar = (meeting) => {
    const studentName = encodeURIComponent(meeting.student_name);
    
    const start = meeting.date.replace(/-/g, '') + 'T' + meeting.start_time.replace(/:/g, '') + '00';
    const end = meeting.date.replace(/-/g, '') + 'T' + meeting.end_time.replace(/:/g, '') + '00';
    
    const url = `https://www.google.com/calendar/render?action=TEMPLATE&text=Mentor+Session+with+${studentName}&dates=${start}/${end}&details=Student+Circus+Mentorship+Session`;
    
    window.open(url, '_blank');
};

const handleLogout = async () => {
    try { await axios.post('http://localhost:8000/logout'); }
    finally {
        localStorage.removeItem('user');
        router.push('/'); 
    }
};

const editingMeetingId = ref(null);
const editData = ref({ date: '', start_time: '', end_time: '' });
const format12Hour = (timeStr) => {
    if (!timeStr) return '';
    const [hours, minutes] = timeStr.split(':');
    const suffix = hours >= 12 ? 'pm' : 'am';
    const h12 = hours % 12 || 12;
    return `${h12}:${minutes}${suffix}`;
};

const formatLongDate = (dateStr) => {
    const date = new Date(dateStr);
    const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
    let formatted = new Intl.DateTimeFormat('en-GB', options).format(date);
    return formatted.replace(/\d+/, (day) => {
        const d = parseInt(day);
        if (d > 3 && d < 21) return d + 'th';
        switch (d % 10) {
            case 1:  return d + "st";
            case 2:  return d + "nd";
            case 3:  return d + "rd";
            default: return d + "th";
        }
    });
};

const startEdit = (meeting) => {
    editingMeetingId.value = meeting.id;
    editData.value = { 
        date: meeting.date, 
        start_time: meeting.start_time.substring(0, 5),
        end_time: meeting.end_time ? meeting.end_time.substring(0, 5) : '' 
    };
};

const saveReschedule = async (id) => {
    try {
        await axios.post(`http://localhost:8000/api/meetings/${id}/reschedule`, editData.value);
        editingMeetingId.value = null;
        fetchData(); 
    } catch (err) {
        alert("Error: Ensure the date is today or later.");
    }
};

onMounted(fetchData);

</script>

<template>
  <div class="dashboard-container">
    <header class="header">
      <h1>Mentor Portal: {{ user?.name }}</h1>
      <button @click="handleLogout" class="logout-btn">Log Out</button>
    </header>
    
    <div v-if="mentorProfile.id" class="banner" :class="Number(mentorProfile.is_verified) === 1 ? 'active' : 'pending'">
      <div class="banner-content">
        <span class="status-icon">{{ Number(mentorProfile.is_verified) === 1 ? '✅' : '⏳' }}</span>
        <div>
          <strong>{{ Number(mentorProfile.is_verified) === 1 ? 'Verified Mentor' : 'Verification Pending' }}</strong>
          <p v-if="Number(mentorProfile.is_verified) === 1">Your profile is visible to international students.</p>
          <p v-else>Please upload your ID below for admin review.</p>
        </div>
      </div>
    </div>

    <div class="grid-layout">
      <div class="profile-card">
        <h2>Professional Profile</h2>
        <div v-if="mentorProfile.id" class="photo-upload-section">
            <label>Profile Picture</label>
            <FileUpload uploadType="photo" :profileId="mentorProfile.id" @upload-success="fetchData" />
        </div>
      </div>
      <div class="grid-layout">
  <div class="profile-card">
    <h2>Reputation & Feedback</h2>
    <div class="rating-summary" v-if="mentorProfile.rating_avg > 0">
      <div class="stat-big">
        <span class="star-large">★</span>
        <span class="avg-val">{{ Number(mentorProfile.rating_avg).toFixed(1) }}</span>
      </div>
      <p class="review-meta">Based on {{ mentorProfile.reviews_count }} reviews</p>
    </div>
    <div v-else class="empty-state">No ratings yet. Your average will appear here after sessions.</div>
  </div>
</div>

      <div class="verification-card">
        <h2>Identity Verification</h2>
        <div v-if="mentorProfile.id" class="upload-section">
          <div v-if="mentorProfile.is_verified" class="verified-badge">
            <span class="success-icon">✔️ Verified</span>
          </div>
          <div v-else>
            <p class="subtitle">Upload a valid Professional ID or Passport.</p>
            <FileUpload uploadType="document" :profileId="mentorProfile.id" @upload-success="fetchData" />
          </div>
        </div>
      </div>
    </div>

    <div class="grid-layout meetings-section">
        <div class="profile-card">
    <h2>Meeting Requests</h2>
    <div v-if="meetingRequests.length === 0" class="empty-state">No pending requests.</div>
    
    <div v-for="req in meetingRequests" :key="req.id" class="meeting-item-card">
        <div class="booking-content">
            <div class="meeting-info">
                <strong>{{ req.student_name }}</strong>
                
                <span v-if="editingMeetingId !== req.id">
                    {{ formatLongDate(req.date) }} at {{ format12Hour(req.start_time) }}
                </span>

                <div v-else class="edit-inputs">
                    <input v-model="editData.date" type="date" class="resched-input">
                    <input v-model="editData.start_time" type="time" class="resched-input">
                </div>
            </div>

            <div class="action-row-horizontal">
                <template v-if="editingMeetingId !== req.id">
                    <button @click="handleMeetingAction(req.id, 'approve')" class="btn-text success">Accept</button>
                    <button @click="startEdit(req)" class="btn-text">Reschedule</button>
                    <button @click="handleMeetingAction(req.id, 'decline')" class="btn-text danger">Decline</button>
                </template>
                <template v-else>
                    <button @click="saveReschedule(req.id)" class="btn-text success">Save</button>
                    <button @click="editingMeetingId = null" class="btn-text">Back</button>
                </template>
            </div>
        </div>
    </div>
</div>

 <<div class="verification-card">
    <h2>Confirmed Meetings</h2>
    <div v-if="upcomingMeetings.length === 0" class="empty-state">No upcoming meetings.</div>

    <div v-for="meeting in upcomingMeetings" :key="meeting.id" class="meeting-item-card confirmed">
        <div class="booking-content">
            <div class="meeting-info">
                <strong>{{ meeting.student_name }}</strong>
                
                <span v-if="editingMeetingId !== meeting.id">
                    {{ formatLongDate(meeting.date) }} at {{ format12Hour(meeting.start_time) }}
                </span>

                <div v-else class="edit-inputs">
    <div class="input-group">
        <label>Date</label>
        <input v-model="editData.date" type="date" class="resched-input">
    </div>
    <div class="input-group">
        <label>Start</label>
        <input v-model="editData.start_time" type="time" class="resched-input">
    </div>
    <div class="input-group">
        <label>End</label>
        <input v-model="editData.end_time" type="time" class="resched-input">
    </div>
</div>
            </div>
            
            <div class="action-row-horizontal">
                <template v-if="editingMeetingId !== meeting.id">
                    <button @click="startEdit(meeting)" class="btn-text">Reschedule</button>
                    <button @click="handleMeetingAction(meeting.id, 'decline')" class="btn-text danger">Cancel</button>
                    <span class="status-pill-small">APPROVED</span>
                </template>
                <template v-else>
                    <button @click="saveReschedule(meeting.id)" class="btn-text success">Save Changes</button>
                    <button @click="editingMeetingId = null" class="btn-text">Back</button>
                </template>
            </div>
        </div>
        
        <div class="booking-footer" v-if="meeting.meeting_link && editingMeetingId !== meeting.id">
            <a :href="meeting.meeting_link" target="_blank" class="join-btn">🎥 Join Jitsi</a>
            <button @click="addToGoogleCalendar(meeting)" class="google-sync-btn">🗓️ Sync Google</button>
        </div>
    </div>
</div>
    </div>
</div>
</template>

<style scoped>
.google-sync-btn {
    background: white;
    border: 1px solid #DEE4FE;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.8rem;
    transition: background 0.2s;
}
.google-sync-btn:hover { background: #f8faff; border-color: #3e55f8; }

.approve-btn { background: #2DE1AA; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; }
.decline-btn { background: #fee2e2; color: #ef4444; border: 1px solid #ef4444; padding: 8px 16px; border-radius: 6px; cursor: pointer; }
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

.meetings-section { margin-bottom: 2rem; }

.meeting-item {
    padding: 1rem;
    border: 1px solid #DEE4FE;
    border-radius: 8px;
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.meeting-info { display: flex; flex-direction: column; }
.meeting-info strong { color: #1a1f3c; }
.meeting-info span { font-size: 0.85rem; color: #6b7280; }

.action-buttons { display: flex; gap: 0.5rem; }

.approve-btn { 
    background: #2DE1AA; color: white; border: none; 
    padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem;
}

.decline-btn { 
    background: #fee2e2; color: #ef4444; border: 1px solid #ef4444; 
    padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem;
}

.empty-state { color: #9ca3af; font-style: italic; text-align: center; padding: 1rem; }

.meeting-actions { 
    display: flex; 
    gap: 10px; 
    margin-top: 10px; 
    align-items: center; 
}

.join-btn {
    background: #e6fffa;
    color: #065f46;
    border: 1px solid #2de1aa;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.8rem;
    font-weight: 700;
    display: inline-flex; 
    align-items: center;
    transition: all 0.2s ease;
}

.join-btn:hover {
    background: #2de1aa;
    color: white;
}

.meeting-item-card {
    background: white;
    border: 1px solid #DEE4FE;
    border-radius: 12px;
    padding: 1.2rem;
    margin-bottom: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.booking-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.action-row-horizontal {
    display: flex;
    gap: 12px;
    align-items: center;
}

.btn-text {
    background: none;
    border: none;
    font-size: 0.85rem;
    font-weight: 600;
    color: #3e55f8;
    cursor: pointer;
    padding: 0;
}

.btn-text.danger { color: #ef4444; }
.btn-text.success { color: #2DE1AA; }
.btn-text:hover { text-decoration: underline; }

.status-pill-small {
    font-size: 0.65rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 4px;
    background: #ecfdf5;
    color: #2DE1AA;
}

.edit-inputs { display: flex; gap: 8px; margin-top: 5px; }
.resched-input { padding: 4px; border: 1px solid #dee4fe; border-radius: 6px; font-size: 0.8rem; }

.booking-footer {
    display: flex;
    gap: 10px;
    padding-top: 0.8rem;
    border-top: 1px dashed #DEE4FE;
}

.meeting-item-card {
    background: white;
    border: 1px solid #DEE4FE;
    border-radius: 12px;
    padding: 1.2rem;
    margin-bottom: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}

.booking-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.action-row-horizontal {
    display: flex;
    gap: 12px;
    align-items: center;
}

.btn-text {
    background: none;
    border: none;
    font-size: 0.85rem;
    font-weight: 600;
    color: #3e55f8;
    cursor: pointer;
    padding: 0;
    transition: color 0.2s;
}

.btn-text.danger { color: #ef4444; }
.btn-text.success { color: #2DE1AA; }
.btn-text:hover { text-decoration: underline; }

.edit-inputs { display: flex; gap: 8px; margin-top: 5px; }
.resched-input { 
    padding: 4px; 
    border: 1px solid #dee4fe; 
    border-radius: 6px; 
    font-size: 0.8rem; 
    font-family: inherit;
}

.rating-summary { text-align: center; padding: 1rem; }
.stat-big { display: flex; align-items: center; justify-content: center; gap: 10px; }
.star-large { font-size: 2.5rem; color: #F4BD3F; }
.avg-val { font-size: 3rem; font-weight: 800; color: #1a1f3c; }
.review-meta { color: #6b7280; font-weight: 600; margin-top: 0.5rem; }

.reviews-container { margin-top: 2rem; background: white; padding: 2rem; border-radius: 12px; border: 1px solid #DEE4FE; }
.reviews-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1rem; }
.review-bubble { background: #f8faff; padding: 1.5rem; border-radius: 10px; border: 1px solid #eef2ff; }
.review-top { display: flex; justify-content: space-between; margin-bottom: 0.8rem; }
.bubble-stars { font-weight: 800; color: #F4BD3F; }
.bubble-date { font-size: 0.8rem; color: #9ca3af; }
.bubble-text { font-style: italic; color: #374151; line-height: 1.5; }
</style>
