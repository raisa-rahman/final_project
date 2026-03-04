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
const isEditingProfile = ref(false);
const universities = ref([]); 

const mentorProfile = ref({
    id: null,
    subject_expertise: '',
    bio: '', 
    university: '', 
    is_verified: false,
    document_path: null,
    profile_photo_path: null,
    rating_avg: 0,
    reviews_count: 0
});

const profileForm = ref({
    subject_expertise: '',
    university: '', 
    bio: '' 
});

const fetchData = async () => {
    try {
        const [profileRes, requestsRes, upcomingRes] = await Promise.all([
            axios.get('http://localhost:8000/api/mentor/profile'),
            axios.get('http://localhost:8000/api/mentor/meetings/requests'), 
            axios.get('http://localhost:8000/api/mentor/meetings/upcoming')
        ]);
        
        const data = profileRes.data.profile || profileRes.data;
        mentorProfile.value = data;
        
        meetingRequests.value = requestsRes.data;
        upcomingMeetings.value = upcomingRes.data;
        
        const uniName = typeof data.university === 'object' && data.university !== null 
            ? data.university.name 
            : (data.university || '');

        profileForm.value = {
            subject_expertise: data.subject_expertise || '',
            university: uniName,
            bio: data.bio || ''
        };
    } catch (err) {
        console.error("Failed to load dashboard data", err);
    } finally {
        loading.value = false;
    }
};


const saveProfile = async () => {
    try {
        await axios.put('http://localhost:8000/api/mentor/profile', profileForm.value);
        isEditingProfile.value = false; 
        fetchData(); 
        alert("Profile updated successfully!");
    } catch (err) {
        console.error(err);
        alert("Failed to update profile. Ensure all fields are filled.");
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
  <div class="header-with-action">
    <h2>Professional Profile</h2>
    <button @click="isEditingProfile = !isEditingProfile" class="btn-text">
      {{ isEditingProfile ? 'Cancel' : 'Edit Details' }}
    </button>
  </div>

  <div v-if="isEditingProfile" class="edit-form">
    <div class="form-group">
        <label>Subject Expertise</label>
        <input v-model="profileForm.subject_expertise" class="resched-input">
    </div>
    
    <div class="form-group">
        <label>University</label>
        <input 
            v-model="profileForm.university" 
            type="text" 
            class="resched-input" 
            placeholder="Enter your university name"
        />
    </div>

    <div class="form-group">
        <label>Professional Bio</label>
        <textarea 
            v-model="profileForm.bio" 
            class="resched-input" 
            rows="4" 
            placeholder="Tell students about your background..."
            style="width: 100%; resize: vertical; margin-top: 0.5rem;"
        ></textarea>
    </div>
    
    <button @click="saveProfile" class="btn-text success">Save Changes</button>
</div>

<div v-else class="profile-upload-wrapper">
    <div class="mentor-photo-container">
        </div>
    <div class="details-display">
    <p><strong>Expertise:</strong> {{ mentorProfile.subject_expertise || 'Not set' }}</p>
    
    <p><strong>University:</strong> 
        {{ typeof mentorProfile.university === 'object' ? mentorProfile.university.name : (mentorProfile.university || 'Not set') }}
    </p>
    
    <p><strong>Bio:</strong> {{ mentorProfile.bio || 'No bio provided.' }}</p>
</div>
</div>
  
  <div v-if="!isEditingProfile" class="photo-upload-section">
    <div v-if="mentorProfile.id">
        <label>Update Profile Picture</label>
        <FileUpload 
            uploadType="photo" 
            :profileId="mentorProfile.id" 
            @upload-success="fetchData" 
        />
    </div>
</div>
  <div>
    <FileUpload 
        uploadType="photo" 
        :profileId="mentorProfile.id" 
        @upload-success="fetchData" 
        v-if="!mentorProfile.profile_photo_path"
    />
</div>

</div>

      <div class="verification-card">
        <h2>Identity Verification</h2>
        <div v-if="mentorProfile.id" class="upload-section">
          <div v-if="Number(mentorProfile.is_verified) === 1" class="verified-badge">
            <span class="success-icon">✔️ Verified</span>
          </div>
          <div v-else>
            <p class="subtitle">Upload a valid Professional ID or Passport.</p>
            <FileUpload uploadType="document" :profileId="mentorProfile.id" @upload-success="fetchData" />
          </div>
        </div>
        
        <hr class="section-divider" />

        <h2>Average Rating</h2>
        <div class="rating-summary" v-if="mentorProfile.rating_avg > 0">
          <div class="stat-big">
            <span class="star-large">★</span>
            <span class="avg-val">{{ Number(mentorProfile.rating_avg).toFixed(1) }}</span>
          </div>
          <p class="review-meta">Based on {{ mentorProfile.reviews_count }} reviews</p>
        </div>
        <div v-else class="empty-state">No ratings yet.</div>
      </div>
    </div>

    <div class="grid-layout meetings-section" style="margin-top: 2rem;">
        <div class="profile-card">
            <h2>Meeting Requests</h2>
            <div v-if="meetingRequests.length === 0" class="empty-state">No pending requests.</div>
            <div v-for="req in meetingRequests" :key="req.id" class="meeting-item-card">
                <div class="booking-content">
                    <div class="meeting-info">
                        <strong>{{ req.student_name }}</strong>
                        <span v-if="editingMeetingId !== req.id">{{ formatLongDate(req.date) }} at {{ format12Hour(req.start_time) }}</span>
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

        <div class="verification-card">
            <h2>Confirmed Meetings</h2>
            <div v-if="upcomingMeetings.length === 0" class="empty-state">No upcoming meetings.</div>
            <div v-for="meeting in upcomingMeetings" :key="meeting.id" class="meeting-item-card confirmed">
                <div class="booking-content">
                    <div class="meeting-info">
                        <strong>{{ meeting.student_name }}</strong>
                        <span v-if="editingMeetingId !== meeting.id">{{ formatLongDate(meeting.date) }} at {{ format12Hour(meeting.start_time) }}</span>
                        <div v-else class="edit-inputs">
                            <input v-model="editData.date" type="date" class="resched-input">
                            <input v-model="editData.start_time" type="time" class="resched-input">
                        </div>
                    </div>
                    <div class="action-row-horizontal">
                        <template v-if="editingMeetingId !== meeting.id">
                            <button @click="startEdit(meeting)" class="btn-text">Reschedule</button>
                            <button @click="handleMeetingAction(meeting.id, 'decline')" class="btn-text danger">Cancel</button>
                        </template>
                        <template v-else>
                            <button @click="saveReschedule(meeting.id)" class="btn-text success">Save</button>
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
.profile-upload-wrapper {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-top: 1rem;
}

.mentor-photo-container {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #DEE4FE;
    flex-shrink: 0;
}

.mentor-dashboard-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.section-divider {
    border: 0;
    border-top: 1px solid #eee;
    margin: 2rem 0;
}

.dashboard-container { padding: 3rem 5%; margin: 0 auto; max-width: auto }
.grid-layout { display: grid; grid-template-columns: 1.2fr 1fr; gap: 2rem; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.profile-card, .verification-card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
.banner { padding: 20px; border-radius: 12px; margin-bottom: 2rem; border-left: 6px solid; }
.active { background: #e6fffa; border-color: #2DE1AA; color: #065f46; }
.pending { background: #DEE4FE; border-color: #F4BD3F; color: #3e55f8; }
.btn-text { background: none; border: none; font-weight: 600; cursor: pointer; color: #3e55f8; }
.btn-text.danger { color: #ef4444; }
.btn-text.success { color: #2DE1AA; }
.meeting-item-card { background: white; border: 1px solid #DEE4FE; border-radius: 12px; padding: 1.2rem; margin-bottom: 1rem; }
.booking-content { display: flex; justify-content: space-between; align-items: center; }
.booking-footer { display: flex; gap: 10px; padding-top: 0.8rem; border-top: 1px dashed #DEE4FE; margin-top: 0.5rem;}
.join-btn { background: #e6fffa; color: #065f46; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 700; border: 1px solid #2de1aa; }
.google-sync-btn { background: #eee; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; }
.rating-summary { text-align: center; }
.avg-val { font-size: 2.5rem; font-weight: 800; }
.star-large { font-size: 2rem; color: #F4BD3F; }

.details-display p {
    margin-bottom: 0.5rem;
    line-height: 1.4;
    word-break: break-word; 
}
</style>