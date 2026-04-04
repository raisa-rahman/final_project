<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import FileUpload from '../components/FileUpload.vue';

const startHour = ref('09');
const startMinute = ref('00');
const startPeriod = ref('AM');
const endHour = ref('10');
const endMinute = ref('00');
const endPeriod = ref('AM');

const allowedHours = computed(() => {
    const hours = [];
    for (let h = 8; h <= 11; h++) hours.push({ display: h, value: pad(h), period: 'AM' });
    hours.push({ display: 12, value: '12', period: 'PM' });
    for (let h = 1; h <= 6; h++) hours.push({ display: h, value: pad(h + 12), period: 'PM' });
    return hours;
});

const pad = (n) => n.toString().padStart(2, '0');

const syncEditData = () => {
    const startObj = allowedHours.value.find(h => h.display == startHour.value && h.period == startPeriod.value);
    if (startObj) editData.value.start_time = `${startObj.value}:${startMinute.value}`;

    const endObj = allowedHours.value.find(h => h.display == endHour.value && h.period == endPeriod.value);
    if (endObj) editData.value.end_time = `${endObj.value}:${endMinute.value}`;
};

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
    university_id:'', 
    bio: '' 
});

const fetchData = async () => {
    try {
        const [profileRes, requestsRes, upcomingRes, uniRes] = await Promise.all([
            axios.get('http://localhost:8000/api/mentor/profile'),
            axios.get('http://localhost:8000/api/mentor/meetings/requests'), 
            axios.get('http://localhost:8000/api/mentor/meetings/upcoming'),
            axios.get('http://localhost:8000/api/universities') 
        ]);
        
        universities.value = uniRes.data;
        const data = profileRes.data.profile || profileRes.data;
mentorProfile.value = data;

console.log("Full Profile Object:", data);         
        profileForm.value = {
    subject_expertise: data.subject_expertise || '',
    bio: data.bio || '',
    university_id: data.university_id || data.university?.id || ''
};

        meetingRequests.value = requestsRes.data;
        upcomingMeetings.value = upcomingRes.data;
    } catch (err) {
        console.error("Failed to load dashboard data", err);
    } finally {
        loading.value = false;
    }
};

const saveProfile = async () => {
    try {
        const payload = {
            subject_expertise: profileForm.value.subject_expertise,
            university_id: profileForm.value.university_id, 
            bio: profileForm.value.bio
        };

        console.log("Sending Payload:", payload); 

        await axios.put('http://localhost:8000/api/mentor/profile', payload);
        
        isEditingProfile.value = false; 
        fetchData(); 
        alert("Profile updated successfully!");
    } catch (err) {
        if (err.response && err.response.status === 422) {
            console.log("Validation Errors:", err.response.data.errors);
            alert("Validation Error: " + JSON.stringify(err.response.data.errors));
        } else {
            console.error(err);
        }
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

const calculateDuration = (start, end) => {
    if (!start || !end) return '';
    const [sHours, sMin] = start.split(':').map(Number);
    const [eHours, eMin] = end.split(':').map(Number);
    
    const startTime = new Date(0, 0, 0, sHours, sMin);
    const endTime = new Date(0, 0, 0, eHours, eMin);
    
    let diff = (endTime.getTime() - startTime.getTime()) / 1000 / 60; 
    if (diff < 0) diff += 1440; 
    
    const hours = Math.floor(diff / 60);
    const mins = diff % 60;
    
    return hours > 0 
        ? `${hours} hr ${mins > 0 ? mins + ' min' : ''}` 
        : `${mins} min`;
};

const handlePhotoSuccess = () => {
    fetchData();
    alert("Profile photo updated!");
};

const cancelMeeting = async (meeting) => {
    const id = meeting.id; 
    if (!confirm("Are you sure you want to cancel this mentorship session?")) return;
    
    try {
        await axios.post(`http://localhost:8000/api/meetings/${id}/cancel`);
        await fetchData(); 
        alert("Session cancelled successfully.");
    } catch (err) {
        alert(err.response?.data?.message || "Could not cancel the meeting.");
    }
};

const currentRescheduleDuration = computed(() => {
    const startObj = allowedHours.value.find(h => h.display == startHour.value && h.period == startPeriod.value);
    const endObj = allowedHours.value.find(h => h.display == endHour.value && h.period == endPeriod.value);
    
    if (!startObj || !endObj) return 0;

    const [sH, sM] = [parseInt(startObj.value), parseInt(startMinute.value)];
    const [eH, eM] = [parseInt(endObj.value), parseInt(endMinute.value)];
    
    const startTimeDate = new Date(0, 0, 0, sH, sM);
    const endTimeDate = new Date(0, 0, 0, eH, eM);
    
    const diffMs = endTimeDate - startTimeDate;
    const diffMins = Math.floor(diffMs / 1000 / 60);
    
    return diffMins > 0 ? diffMins : 0;
});

const saveReschedule = async (id) => {
    const startObj = allowedHours.value.find(h => h.display == startHour.value && h.period == startPeriod.value);
    const endObj = allowedHours.value.find(h => h.display == endHour.value && h.period == endPeriod.value);

    if (!startObj || !endObj) {
        alert("Please select valid start and end times.");
        return;
    }

    const startTime24 = `${startObj.value}${startMinute.value}`;
    const endTime24 = `${endObj.value}${endMinute.value}`;

    if (parseInt(endTime24) > 1800) {
        alert("The professional window closes at 6:00 PM.");
        return;
    }

    const duration = currentRescheduleDuration.value;
    const allowedDurations = [15, 30, 45, 60];

    if (duration <= 0) {
        alert("The end time must be later than the start time.");
        return;
    }

    if (!allowedDurations.includes(duration)) {
        alert(`Invalid duration (${duration} mins). Rescheduled meetings must be 15, 30, 45, or 60 minutes long.`);
        return;
    }

    const payload = {
        date: editData.value.date,
        start_time: `${startObj.value}:${startMinute.value}`,
        end_time: `${endObj.value}:${endMinute.value}`
    };

    try {
        await axios.post(`http://localhost:8000/api/meetings/${id}/reschedule`, payload);
        editingMeetingId.value = null;
        fetchData(); 
        alert("Reschedule successful! Status reset to pending.");
    } catch (err) {
        const errorMsg = err.response?.data?.message || "Ensure the date is today or later.";
        alert(errorMsg);
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
        <div class="form-group photo-edit-section">
    <label>Profile Photo</label>
    <div class="edit-photo-preview">
        <img v-if="mentorProfile.profile_photo_path" 
             :src="`http://localhost:8000/storage/${mentorProfile.profile_photo_path}`" 
             class="mentor-dashboard-img small">
        <div v-else class="photo-placeholder small">{{ user?.name?.charAt(0) }}</div>
        
        <FileUpload 
            uploadType="photo" 
            :profileId="mentorProfile.id" 
            @upload-success="handlePhotoSuccess" 
        />
    </div>
</div>
          <div class="form-group">
            <label>Subject Expertise</label>
            <input v-model="profileForm.subject_expertise" class="resched-input">
          </div>
          <div class="form-group">
            <label>University</label>
            <select v-model="profileForm.university_id" class="resched-input uni-select-dropdown">
    <option value="" disabled>Select your institution</option>
    <option v-for="uni in universities" :key="uni.id" :value="uni.id">
        {{ uni.name }}
    </option>
</select>
            </div>
          <div class="form-group">
            <label>Professional Bio</label>
            <textarea v-model="profileForm.bio" class="resched-input" rows="4"></textarea>
          </div>
          <button @click="saveProfile" class="btn-text success">Save Changes</button>
        </div>

        <div v-else class="profile-upload-wrapper">
    <div class="mentor-photo-container">
        <img v-if="mentorProfile.profile_photo_path" :src="`http://localhost:8000/storage/${mentorProfile.profile_photo_path}`" class="mentor-dashboard-img">
        <div v-else class="photo-placeholder">{{ user?.name?.charAt(0) }}</div>
    </div>
    <div class="details-display">
        <p><strong>Expertise:</strong> {{ mentorProfile.subject_expertise || 'Not set' }}</p>
        
        <p><strong>University:</strong> 
            {{ mentorProfile.university?.name || mentorProfile.university || 'Not set' }}
        </p>
        
        <p><strong>Bio:</strong> {{ mentorProfile.bio || 'No bio provided.' }}</p>
    </div>
</div>
      </div>

      <div class="verification-card">
        <h2>Identity Verification</h2>
        <div v-if="mentorProfile.id" class="upload-section">
          <div v-if="Number(mentorProfile.is_verified) === 1" class="verified-badge">✔️ Verified</div>
          <div v-else>
            <FileUpload uploadType="document" :profileId="mentorProfile.id" @upload-success="fetchData" />
          </div>
        </div>
        <hr class="section-divider" />
        <h2>Average Rating</h2>
        <div class="rating-summary" v-if="mentorProfile.rating_avg > 0">
          <span class="avg-val">{{ Number(mentorProfile.rating_avg).toFixed(1) }}</span>
          <p>Based on {{ mentorProfile.reviews_count }} reviews</p>
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
              <div v-if="editingMeetingId !== req.id">
                {{ formatLongDate(req.date) }} <br>
                {{ format12Hour(req.start_time) }} – {{ format12Hour(req.end_time) }}
              </div>
              <div v-else class="edit-inputs">
                  <label class="edit-label">Date</label>
                  <input v-model="editData.date" type="date" class="resched-input">

                  <div class="duration-preview" :class="{ 'invalid': ![15,30,45,60].includes(currentRescheduleDuration) }">
                    Current Duration: {{ currentRescheduleDuration }} minutes
                    <span v-if="![15,30,45,60].includes(currentRescheduleDuration) && currentRescheduleDuration > 0">
                        (Must be 15, 30, 45, or 60)
                    </span>
                </div>
                  
                  <label class="edit-label">Start Time (8 AM - 6 PM)</label>
                  <div class="custom-time-picker">
                      <select v-model="startHour" @change="syncEditData" class="time-select">
                          <option v-for="h in allowedHours.filter(h => h.period === startPeriod)" :key="h.value" :value="h.display">{{ h.display }}</option>
                      </select>
                      <span class="separator">:</span>
                      <select v-model="startMinute" @change="syncEditData" class="time-select">
                          <option value="00">00</option>
                          <option value="15">15</option>
                          <option value="30">30</option>
                          <option value="45">45</option>
                      </select>
                      <select v-model="startPeriod" @change="syncEditData" class="time-select period">
                          <option value="AM">AM</option>
                          <option value="PM">PM</option>
                      </select>
                  </div>

                  <label class="edit-label">End Time</label>
                  <div class="custom-time-picker">
                      <select v-model="endHour" @change="syncEditData" class="time-select">
                          <option v-for="h in allowedHours.filter(h => h.period === endPeriod)" :key="h.value" :value="h.display">{{ h.display }}</option>
                      </select>
                      <span class="separator">:</span>
                      <select v-model="endMinute" @change="syncEditData" class="time-select">
                          <option value="00">00</option>
                          <option value="15">15</option>
                          <option value="30">30</option>
                          <option value="45">45</option>
                      </select>
                      <select v-model="endPeriod" @change="syncEditData" class="time-select period">
                          <option value="AM">AM</option>
                          <option value="PM">PM</option>
                      </select>
                  </div>
              </div>
            </div>
            <div class="action-row-horizontal">
              <template v-if="editingMeetingId !== req.id">
                <button @click="handleMeetingAction(req.id, 'approve')" class="btn-text success">Accept</button>
                <button @click="startEdit(req)" class="btn-text">Reschedule</button>
                <button @click="cancelMeeting(meeting)" class="btn-text">Cancel</button>
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
        <div v-if="editingMeetingId !== meeting.id">
          {{ formatLongDate(meeting.date) }} <br> 
          {{ format12Hour(meeting.start_time) }} – {{ format12Hour(meeting.end_time) }}
          <div class="duration-label">
            ⏱️ {{ calculateDuration(meeting.start_time, meeting.end_time) }}
          </div>
        </div>
        
        <div v-else class="edit-inputs">
                  <label class="edit-label">Date</label>
                  <input v-model="editData.date" type="date" class="resched-input">

                  <div class="duration-preview" :class="{ 'invalid': ![15,30,45,60].includes(currentRescheduleDuration) }">
                      Current Duration: {{ currentRescheduleDuration }} minutes
                      <span v-if="![15,30,45,60].includes(currentRescheduleDuration) && currentRescheduleDuration > 0">
                          (Must be 15, 30, 45, or 60)
                      </span>
                  </div>
                  
                  <label class="edit-label">Start Time (8 AM - 6 PM)</label>
                  <div class="custom-time-picker">
                      <select v-model="startHour" @change="syncEditData" class="time-select">
                          <option v-for="h in allowedHours.filter(h => h.period === startPeriod)" :key="h.value" :value="h.display">{{ h.display }}</option>
                      </select>
                      <span class="separator">:</span>
                      <select v-model="startMinute" @change="syncEditData" class="time-select">
                          <option value="00">00</option>
                          <option value="15">15</option>
                          <option value="30">30</option>
                          <option value="45">45</option>
                      </select>
                      <select v-model="startPeriod" @change="syncEditData" class="time-select period">
                          <option value="AM">AM</option>
                          <option value="PM">PM</option>
                      </select>
                  </div>

                  <label class="edit-label">End Time</label>
                  <div class="custom-time-picker">
                      <select v-model="endHour" @change="syncEditData" class="time-select">
                          <option v-for="h in allowedHours.filter(h => h.period === endPeriod)" :key="h.value" :value="h.display">{{ h.display }}</option>
                      </select>
                      <span class="separator">:</span>
                      <select v-model="endMinute" @change="syncEditData" class="time-select">
                          <option value="00">00</option>
                          <option value="15">15</option>
                          <option value="30">30</option>
                          <option value="45">45</option>
                      </select>
                      <select v-model="endPeriod" @change="syncEditData" class="time-select period">
                          <option value="AM">AM</option>
                          <option value="PM">PM</option>
                      </select>
                  </div>
              </div>
      </div>

      <div class="action-row-horizontal">
        <template v-if="editingMeetingId !== meeting.id">
          <button @click="startEdit(meeting)" class="btn-text">Reschedule</button>
          <button @click="cancelMeeting(meeting)" class="btn-text">Cancel</button>

        </template>
        <template v-else>
          <button @click="saveReschedule(meeting.id)" class="btn-text success">Save</button>
          <button @click="editingMeetingId = null" class="btn-text">Back</button>
        </template>
      </div>
    </div>

    <div class="booking-footer" v-if="editingMeetingId !== meeting.id">
      <a v-if="meeting.meeting_link" :href="meeting.meeting_link" target="_blank" class="join-btn">
        🎥 Join Jitsi
      </a>
      <button @click="addToGoogleCalendar(meeting)" class="google-sync-btn">
        🗓️ Sync Google
      </button>
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

.edit-inputs {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.edit-label {
    font-size: 0.7rem;
    color: #666;
    margin-bottom: -0.4rem;
    font-weight: bold;
}

.resched-input {
    padding: 8px;
    border: 1px solid #DEE4FE;
    border-radius: 6px;
    width: 100%; 
    max-width: 200px;
}

.duration-label {
    display: inline-block;
    background: #f3f4f6;
    color: #4b5563;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 4px;
    border: 1px solid #e5e7eb;
}

.meeting-info span {
    line-height: 1.5;
    display: block;
}

.photo-edit-section {
    background: #f8faff;
    padding: 1rem;
    border-radius: 8px;
    border: 1px dashed #3e55f8;
    margin-bottom: 1.5rem;
}

.edit-photo-preview {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 0.5rem;
}

.mentor-dashboard-img.small, .photo-placeholder.small {
    width: 60px;
    height: 60px;
    font-size: 1.2rem;
}

.custom-time-picker {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8faff;
    padding: 8px;
    border-radius: 10px;
    border: 1px solid #DEE4FE;
    width: fit-content;
    margin-bottom: 1rem;
}

.time-select {
    padding: 8px 12px;
    border: 1px solid #DEE4FE;
    border-radius: 6px;
    background: white;
    font-family: 'SF-UI Text', sans-serif;
    font-weight: 600;
    color: #1a1f3c;
    cursor: pointer;
    outline: none;
}

.time-select:focus {
    border-color: #3e55f8;
}

.separator {
    font-weight: 800;
    color: #1a1f3c;
}

.period {
    background: #3e55f8;
    color: white;
    border: none;
}

.edit-label {
    font-size: 0.8rem;
    color: #1a1f3c;
    font-weight: 700;
    margin-top: 10px;
    margin-bottom: 4px;
    display: block;
}

.duration-preview {
    font-size: 0.85rem;
    padding: 10px;
    background: #eef2ff;
    border-radius: 8px;
    color: #3e55f8;
    font-weight: 700;
    margin: 10px 0;
    text-align: center;
    border: 1px solid #dee4fe;
}

.duration-preview.invalid {
    background: #fee2e2;
    color: #ef4444;
    border-color: #fecaca;
}
</style>