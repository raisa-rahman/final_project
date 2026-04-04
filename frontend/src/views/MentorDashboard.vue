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
const pastMeetings = ref([]); 

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
    current_role: '',
    company: '',
    graduation_year: '',
    bio: '',
    interests: '',
    roadmap: '',
    ask_me_about: '',
    university_id: ''
});

const fetchData = async () => {
    try {
        const [profileRes, requestsRes, upcomingRes, uniRes, pastRes] = await Promise.all([
            axios.get('http://localhost:8000/api/mentor/profile'),
            axios.get('http://localhost:8000/api/mentor/meetings/requests'), 
            axios.get('http://localhost:8000/api/mentor/meetings/upcoming'),
            axios.get('http://localhost:8000/api/universities'),
            axios.get('http://localhost:8000/api/mentor/meetings/past')
        ]);
        
        universities.value = uniRes.data;
        const data = profileRes.data.profile || profileRes.data;
        mentorProfile.value = data;
        meetingRequests.value = requestsRes.data;
        upcomingMeetings.value = upcomingRes.data; 
        pastMeetings.value = pastRes.data; 

console.log("Full Profile Object:", data);         
        profileForm.value = {
    subject_expertise: data.subject_expertise || '',
    current_role: data.current_role || '',
    company: data.company || '',
    graduation_year: data.graduation_year || '',
    bio: data.bio || '',
    interests: data.interests || '',
    roadmap: data.roadmap || '',
    ask_me_about: data.ask_me_about || '',
    university_id: data.university_id || ''
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

const activeTab = ref('thisWeek');
const pastSearchName = ref('');
const pastSearchDate = ref('');

const filteredMeetings = computed(() => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  const oneWeekFromNow = new Date(today);
  oneWeekFromNow.setDate(today.getDate() + 7);

  const confirmed = upcomingMeetings.value.filter(m => m.status === 'approved');

  if (activeTab.value === 'thisWeek') {
    return confirmed.filter(m => {
      const d = new Date(m.date);
      return d >= today && d < oneWeekFromNow;
    });
  } 
  
  if (activeTab.value === 'future') {
    return confirmed.filter(m => {
      const d = new Date(m.date);
      return d >= oneWeekFromNow;
    });
  }

  if (activeTab.value === 'past') {
    return pastMeetings.value.filter(m => {
      const matchesName = m.student_name?.toLowerCase().includes(pastSearchName.value.toLowerCase());
      const matchesDate = pastSearchDate.value ? m.date === pastSearchDate.value : true;
      return matchesName && matchesDate;
    });
  }

  return [];
});

const uniquePastStudents = computed(() => {
    const names = pastMeetings.value.map(m => m.student_name);
    return [...new Set(names)].sort();
});

onMounted(fetchData);
</script>

<template>
  <div class="dashboard-container">
    <header class="header">
      <h1>Mentor Portal: {{ user?.name }}</h1>
      <button @click="handleLogout" class="logout-btn">Log Out</button>
    </header>
    
    <div v-if="mentorProfile.id" class="banner" 
     :class="{ 
       'active': Number(mentorProfile.is_verified) === 1, 
       'pending': Number(mentorProfile.is_verified) === 0, 
       'rejected': Number(mentorProfile.is_verified) === 2 
     }">
  <div class="banner-content">
    <span class="status-icon">
      {{ Number(mentorProfile.is_verified) === 1 ? '✅' : (Number(mentorProfile.is_verified) === 2 ? '❌' : '⏳') }}
    </span>
    <div>
      <strong v-if="Number(mentorProfile.is_verified) === 1">Verified Mentor</strong>
      <strong v-else-if="Number(mentorProfile.is_verified) === 2">Verification Declined</strong>
      <strong v-else>Verification Pending</strong>
      
      <p v-if="Number(mentorProfile.is_verified) === 1">Your profile is visible to international students.</p>
      <p v-else-if="Number(mentorProfile.is_verified) === 2">Mentor unapproved. Please upload another valid ID for re-review.</p>
      <p v-else>Please upload your ID below for admin review.</p>
    </div>
  </div>
</div>

    <div class="grid-layout">
  <div class="profile-card expanded-profile">
    <div class="header-with-action">
      <h2>Professional Profile</h2>
      <button @click="isEditingProfile = !isEditingProfile" class="btn-text">
        {{ isEditingProfile ? 'Cancel' : 'Edit Details' }}
      </button>
    </div>

    <div v-if="isEditingProfile" class="edit-form grid-form">
      
      <h3 class="form-section-title">1. Identity & Credentials</h3>
      <div class="form-row">
        <div class="form-group photo-edit-section">
          <label>Profile Photo</label>
          <div class="edit-photo-preview">
              <img v-if="mentorProfile.profile_photo_path" :src="`http://localhost:8000/storage/${mentorProfile.profile_photo_path}`" class="mentor-dashboard-img small">
              <div v-else class="photo-placeholder small">{{ user?.name?.charAt(0) }}</div>
              <FileUpload uploadType="photo" :profileId="mentorProfile.id" @upload-success="handlePhotoSuccess" />
          </div>
        </div>
        <div class="input-stack">
          <div class="form-group">
            <label>Current Role (e.g. Software Engineer)</label>
            <input v-model="profileForm.current_role" class="resched-input" placeholder="What is your title?">
          </div>
          <div class="form-group">
            <label>Company</label>
            <input v-model="profileForm.company" class="resched-input" placeholder="Where do you work?">
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>University</label>
          <select v-model="profileForm.university_id" class="resched-input uni-select-dropdown">
            <option v-for="uni in universities" :key="uni.id" :value="uni.id">{{ uni.name }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>Graduation Year</label>
          <input v-model="profileForm.graduation_year" type="number" class="resched-input" placeholder="YYYY">
        </div>
      </div>

      <h3 class="form-section-title">2. Your Narrative (The Bio)</h3>
      <div class="form-group">
        <label>The "Why" & Journey (Bio)</label>
        <textarea v-model="profileForm.bio" class="resched-input" rows="5" placeholder="Tell students about your pivot, your interests, and why you mentor..."></textarea>
      </div>
      <div class="form-group">
        <label>Human Tags (Interests)</label>
        <input v-model="profileForm.interests" class="resched-input" placeholder="e.g. Marathon runner, Sci-fi nerd, Amateur chef">
      </div>

      <h3 class="form-section-title">3. Roadmap & "Ask Me About"</h3>
      <div class="form-group">
        <label>Academic & Career Roadmap</label>
        <textarea v-model="profileForm.roadmap" class="resched-input" rows="3" placeholder="Major, minor, key societies or internships..."></textarea>
      </div>
      <div class="form-group">
        <label>Ask Me About... (Specific Topics)</label>
        <textarea v-model="profileForm.ask_me_about" class="resched-input" rows="3" placeholder="e.g. Choosing UX modules, balancing part-time jobs..."></textarea>
      </div>

      <button @click="saveProfile" class="save-btn-large">Save Professional Profile</button>
    </div>

    <div v-else class="profile-view-layout">
      <div class="profile-sidebar">
        <div class="mentor-photo-container">
          <img v-if="mentorProfile.profile_photo_path" :src="`http://localhost:8000/storage/${mentorProfile.profile_photo_path}`" class="mentor-dashboard-img">
          <div v-else class="photo-placeholder">{{ user?.name?.charAt(0) }}</div>
        </div>
        <div class="identity-badge">
          <p class="role-text">{{ mentorProfile.current_role }} @ {{ mentorProfile.company }}</p>
          <p class="uni-text">{{ mentorProfile.university?.name }} '{{ mentorProfile.graduation_year }}</p>
        </div>
      </div>

      <div class="profile-main-content">
        <section class="bio-section">
          <h4>About Me</h4>
          <p>{{ mentorProfile.bio || 'No bio provided.' }}</p>
          <div class="tag-cloud">
            <span v-for="tag in mentorProfile.interests?.split(',')" :key="tag" class="human-tag">
              #{{ tag.trim() }}
            </span>
          </div>
        </section>

        <section class="roadmap-section">
          <h4>🛣️ Career Roadmap</h4>
          <p>{{ mentorProfile.roadmap || 'Not set' }}</p>
        </section>

        <section class="expertise-section">
          <h4>💡 Ask Me About</h4>
          <div class="expertise-box">
            {{ mentorProfile.ask_me_about || 'Not set' }}
          </div>
        </section>
      </div>
    </div>
  </div>

      <div class="verification-card">
        <h2>Identity Verification</h2>
        <p>To become a verified mentor, please upload a valid ID document. This helps us maintain a trustworthy community for international students.</p>
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
  <div class="tabs-header">
    <h2>Confirmed Meetings</h2>
    <div class="tab-buttons">
      <button :class="{ active: activeTab === 'thisWeek' }" @click="activeTab = 'thisWeek'">This Week</button>
      <button :class="{ active: activeTab === 'future' }" @click="activeTab = 'future'">Future</button>
      <button :class="{ active: activeTab === 'past' }" @click="activeTab = 'past'">Past</button>
    </div>
  </div>

  <div v-if="activeTab === 'past'" class="past-filters">
    <select v-model="pastSearchName" class="resched-input small uni-select-dropdown">
        <option value="">All Students</option>
        <option v-for="name in uniquePastStudents" :key="name" :value="name">
            {{ name }}
        </option>
    </select>

    <input v-model="pastSearchDate" type="date" class="resched-input small">
    
    <button v-if="pastSearchName || pastSearchDate" @click="pastSearchName = ''; pastSearchDate = ''" class="btn-text danger small">
        Clear
    </button>
</div>

  <div v-if="filteredMeetings.length === 0" class="empty-state">
    No meetings found for this category.
  </div>

  <div v-for="meeting in filteredMeetings" :key="meeting.id" 
       class="meeting-item-card confirmed" :class="{ 'past-card': activeTab === 'past' }">
    
    <div class="booking-content">
      <div class="meeting-info">
        <strong>{{ meeting.student_name }}</strong>
        
        <div v-if="activeTab === 'past' || editingMeetingId !== meeting.id">
          {{ formatLongDate(meeting.date) }} <br> 
          {{ format12Hour(meeting.start_time) }} – {{ format12Hour(meeting.end_time) }}
          <div class="duration-label">
            ⏱️ {{ calculateDuration(meeting.start_time, meeting.end_time) }}
          </div>
        </div>
        
        <div v-else-if="activeTab !== 'past' && editingMeetingId === meeting.id" class="edit-inputs">
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
              <option value="00">00</option><option value="15">15</option>
              <option value="30">30</option><option value="45">45</option>
            </select>
            <select v-model="startPeriod" @change="syncEditData" class="time-select period">
              <option value="AM">AM</option><option value="PM">PM</option>
            </select>
          </div>

          <label class="edit-label">End Time</label>
          <div class="custom-time-picker">
            <select v-model="endHour" @change="syncEditData" class="time-select">
              <option v-for="h in allowedHours.filter(h => h.period === endPeriod)" :key="h.value" :value="h.display">{{ h.display }}</option>
            </select>
            <span class="separator">:</span>
            <select v-model="endMinute" @change="syncEditData" class="time-select">
              <option value="00">00</option><option value="15">15</option>
              <option value="30">30</option><option value="45">45</option>
            </select>
            <select v-model="endPeriod" @change="syncEditData" class="time-select period">
              <option value="AM">AM</option><option value="PM">PM</option>
            </select>
          </div>
        </div>
      </div>

      <div class="action-row-horizontal">
        <template v-if="activeTab !== 'past'">
          <template v-if="editingMeetingId !== meeting.id">
            <button @click="startEdit(meeting)" class="btn-text">Reschedule</button>
            <button @click="cancelMeeting(meeting)" class="btn-text">Cancel</button>
          </template>
          <template v-else>
            <button @click="saveReschedule(meeting.id)" class="btn-text success">Save</button>
            <button @click="editingMeetingId = null" class="btn-text">Back</button>
          </template>
        </template>
        
        <template v-else>
          <div v-if="meeting.review" class="review-badge">
            <div class="stars">
              <span v-for="i in 5" :key="i" :class="{ 'gold': i <= meeting.review.rating }">★</span>
            </div>
            <p class="review-comment">"{{ meeting.review.comment }}"</p>
          </div>
          <div v-else class="no-review">No feedback left yet.</div>
        </template>
      </div>
    </div>

    <div class="booking-footer" v-if="activeTab !== 'past' && editingMeetingId !== meeting.id">
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

.tabs-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.tab-buttons {
  display: flex;
  gap: 10px;
  background: #f0f2f5;
  padding: 5px;
  border-radius: 8px;
}

.tab-buttons button {
  border: none;
  background: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.8rem;
  transition: 0.3s;
}

.tab-buttons button.active {
  background: white;
  color: #3e55f8;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.past-filters {
  display: flex;
  gap: 10px;
  margin-bottom: 1rem;
  
}

.resched-input.small {
  font-size: 0.8rem;
  padding: 5px 10px;
}

.review-badge {
  background: #f8faff;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #dee4fe;
  max-width: 250px;
}

.stars { color: #ccc; font-size: 1.1rem; }
.stars .gold { color: #f4bd3f; }

.review-comment {
  font-size: 0.75rem;
  font-style: italic;
  margin-top: 5px;
  color: #555;
}

.past-card { opacity: 0.85; border-style: dashed; }

.past-filters select.small {
    max-width: 180px;
    cursor: pointer;
}

.banner.rejected {
    background: #fee2e2;
    border-color: #ef4444;
    color: #991b1b;
}

.form-section-title {
  margin: 1.5rem 0 0.5rem 0;
  font-size: 1.1rem;
  color: #3e55f8;
  border-bottom: 1px solid #DEE4FE;
  padding-bottom: 5px;
}

.profile-view-layout {
  display: flex;
  gap: 2rem;
}

.profile-sidebar {
  flex: 0 0 200px;
  text-align: center;
}

.identity-badge {
  margin-top: 10px;
  font-size: 0.9rem;
}

.role-text { font-weight: 800; color: #1a1f3c; margin: 0; }
.uni-text { color: #6b7280; font-style: italic; }

.human-tag {
  display: inline-block;
  background: #f0f2ff;
  color: #3e55f8;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 700;
  margin-right: 8px;
  margin-top: 8px;
}

.expertise-box {
  background: #f8faff;
  border-left: 4px solid #2DE1AA;
  padding: 15px;
  border-radius: 4px;
  font-style: italic;
}

.save-btn-large {
  background: #3e55f8;
  color: white;
  width: 100%;
  padding: 12px;
  border-radius: 8px;
  font-weight: bold;
  margin-top: 20px;
  cursor: pointer;
  border: none;
}
</style>