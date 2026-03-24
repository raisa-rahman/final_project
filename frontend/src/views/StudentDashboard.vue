<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import ReviewForm from '../components/ReviewForm.vue';

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
        const response = await axios.get('http://localhost:8000/api/student/profile');
        const data = response.data.favorites_json;
        favoriteIds.value = data ? JSON.parse(data) : [];
    } catch (err) {
        console.error("Failed to load favorites", err);
    }
};

const sentRequests = ref([]);

const fetchSentRequests = async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/student/meetings');
        sentRequests.value = response.data;
    } catch (err) {
        console.error("Failed to load sent requests", err);
    }
};

const addToGoogleCalendar = (request) => {
    const mentorName = encodeURIComponent(request.mentor_name);
    const start = request.date.replace(/-/g, '') + 'T' + request.start_time.replace(/:/g, '') + '00';
    const end = request.date.replace(/-/g, '') + 'T' + (parseInt(request.start_time.split(':')[0]) + 1) + request.start_time.split(':')[1] + '00';
    
    const url = `https://www.google.com/calendar/render?action=TEMPLATE&text=Mentorship+with+${mentorName}&dates=${start}/${end}&details=Student+Circus+Mentorship+Session`;
    
    window.open(url, '_blank');
};

const cancelMeeting = async (id) => {
    if (!confirm("Are you sure you want to cancel this mentorship session?")) return;
    try {
        await axios.post(`http://localhost:8000/api/meetings/${id}/cancel`);
        fetchSentRequests(); 
    } catch (err) {
        console.error("Cancellation failed", err);
    }
};

const initiateReschedule = async (meeting) => {
    const newDate = prompt("Step 1/2: Enter new date (YYYY-MM-DD):", meeting.date);
    if (!newDate) return;

    const newTime = prompt("Step 2/2: Enter new start time (HH:MM):", meeting.start_time.substring(0, 5));
    if (!newTime) return;
    
    try {
        await axios.post(`http://localhost:8000/api/meetings/${meeting.id}/reschedule`, {
            date: newDate,
            start_time: newTime
        });
        
        alert("Reschedule request sent! The mentor needs to approve the new time.");
        fetchSentRequests(); 
    } catch (err) {
        console.error(err);
        alert("Reschedule failed. Please ensure the date is today or in the future.");
    }
};

const formatLongDate = (dateStr) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', { 
        weekday: 'long', 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric' 
    }).replace(/(\d+)/, (day) => {
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

const editingMeetingId = ref(null);
const editData = ref({ date: '', start_time: '', end_time: '' });
const format12Hour = (timeStr) => {
    if (!timeStr) return '';
    const [hours, minutes] = timeStr.split(':');
    const suffix = hours >= 12 ? 'pm' : 'am';
    const h12 = hours % 12 || 12;
    return `${h12}:${minutes}${suffix}`;
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
        fetchSentRequests();
        alert("Reschedule request sent for approval.");
    } catch (err) {
        alert("Error saving: Ensure the date is today or later.");
    }
};

const currentTab = ref('upcoming'); 

const categorizedMeetings = computed(() => {
    const now = new Date();
    const upcoming = [];
    const past = [];

    sentRequests.value.forEach(req => {
        const meetingDateTime = new Date(`${req.date}T${req.start_time}`);
        
        if (meetingDateTime > now && req.status !== 'declined') {
            upcoming.push(req);
        } else if (meetingDateTime <= now || req.status === 'declined') {
            past.push(req);
        }
    });

    return { upcoming, past };
});

const activeReviewMeetingId = ref(null);
const toggleReviewForm = (meetingId) => {
    const meeting = sentRequests.value.find(r => r.id === meetingId);
    console.log("Checking meeting data for review:", meeting);
    activeReviewMeetingId.value = activeReviewMeetingId.value === meetingId ? null : meetingId;
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

const selectedUniversities = ref([]);

const universityOptions = computed(() => {
    return allUniversities.value.map(u => u.name).sort();
});

const filteredMentors = computed(() => {
  return mentors.value.filter(mentor => {
    const mentorUni = mentor.university?.name || mentor.target_university || '';
    
    const matchesSubject = !searchQuery.value.subject || 
        mentor.subject_expertise?.toLowerCase().includes(searchQuery.value.subject.toLowerCase());
        
    const matchesUniversity = selectedUniversities.value.length === 0 || 
        selectedUniversities.value.includes(mentorUni);

    return matchesSubject && matchesUniversity;
  });
});

const toggleUniversity = (uni) => {
    const index = selectedUniversities.value.indexOf(uni);
    if (index > -1) {
        selectedUniversities.value.splice(index, 1);
    } else {
        selectedUniversities.value.push(uni);
    }
};

const isUniSelected = (uni) => selectedUniversities.value.includes(uni);

const allUniversities = ref([]); 

const fetchUniversities = async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/universities');
        allUniversities.value = response.data;
    } catch (err) {
        console.error("Failed to load full university list", err);
    }
};



onMounted(() => {
    fetchUniversities();
    fetchMentors();
    fetchProfile();
    fetchMentors();
    fetchFavorites(); 
    fetchSentRequests(); 
});

</script>

<template>
  <div class="dashboard-container">
    <div class="booking-tracker-section">
      <div class="section-header">
        <h2>Your Meetings</h2>
        <div class="tab-toggle">
          <button @click="currentTab = 'upcoming'" :class="{ active: currentTab === 'upcoming' }" class="tab-btn">Upcoming</button>
          <button @click="currentTab = 'past'" :class="{ active: currentTab === 'past' }" class="tab-btn">Past Sessions</button>
        </div>
      </div>

      <div v-if="categorizedMeetings[currentTab].length === 0" class="empty-state-card">
        <p v-if="currentTab === 'upcoming'">No upcoming sessions. Time to connect with a mentor!</p>
        <p v-else>No past sessions found.</p>
      </div>

      <div v-else class="request-list">
        <div v-for="request in categorizedMeetings[currentTab]" :key="request.id" class="request-item">
          <div class="request-main">
            <div class="mentor-meta">
              <strong>{{ request.mentor_name }}</strong>
              
              <span v-if="editingMeetingId !== request.id" class="date-display">
                {{ formatLongDate(request.date) }} <br>
                {{ format12Hour(request.start_time) }} – {{ format12Hour(request.end_time) }}
                <div v-if="request.end_time" class="duration-badge-student">
                  ⏱️ {{ calculateDuration(request.start_time, request.end_time) }}
                </div>
              </span>
              
              <div v-else class="edit-inputs-row" style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 8px;">
                <label class="edit-label">Date</label>
                <input v-model="editData.date" type="date" class="resched-input">
                <div style="display: flex; gap: 10px;">
                  <div>
                    <label class="edit-label">Start</label>
                    <input v-model="editData.start_time" type="time" class="resched-input">
                  </div>
                  <div>
                    <label class="edit-label">End</label>
                    <input v-model="editData.end_time" type="time" class="resched-input">
                  </div>
                </div>
              </div>
            </div>
            <p class="session-topic" v-if="request.topic">Topic: {{ request.topic }}</p>
          </div>

          <div class="card-footer">
            <div class="action-row">
              <template v-if="currentTab === 'upcoming'">
                <template v-if="editingMeetingId !== request.id">
                  <button @click="startEdit(request)" class="btn-text">Reschedule</button>
                  <button @click="cancelMeeting(request.id)" class="btn-text danger">Cancel</button>
                </template>
                <template v-else>
                  <button @click="saveReschedule(request.id)" class="btn-text success">Save</button>
                  <button @click="editingMeetingId = null" class="btn-text">Back</button>
                </template>
              </template>
              <template v-else>
                <button v-if="request.status === 'approved'" @click="toggleReviewForm(request.id)" class="btn-text success">
                  {{ activeReviewMeetingId === request.id ? 'Close' : 'Leave Review' }}
                </button>
              </template>
            </div>
            <span class="status-pill-minimal" :class="request.status">{{ request.status.toUpperCase() }}</span>
          </div>

          <div v-if="activeReviewMeetingId === request.id" class="review-section">
            <ReviewForm 
    :meeting-id="request.id"
    :mentor-id="request.mentor_profile_id" 
    @review-submitted="() => { activeReviewMeetingId = null; fetchSentRequests(); }" 
/>
          </div>
          
          <div v-if="currentTab === 'upcoming' && request.status === 'approved' && request.meeting_link" class="link-bar">
            <a :href="request.meeting_link" target="_blank" class="join-pill">🎥 Join Jitsi</a>
            <button @click="addToGoogleCalendar(request)" class="sync-pill">🗓️ Sync</button>
          </div>
        </div>
      </div>
    </div>

    <main class="main-content">
      <header class="dashboard-header">
        <div class="header-main">
          <h1>Welcome, {{ user?.name }}</h1>
          <button @click="handleLogout" class="logout-btn">Log Out</button>
        </div>
      </header>

      <div class="directory-section">
        <div class="section-header">
          <h2>Verified Mentors ({{ filteredMentors.length }})</h2>
          <div class="search-filters">
            <div class="filter-group">
              <label class="filter-label">Expertise</label>
              <div class="search-wrapper">
                <span class="search-icon"></span>
                <input v-model="searchQuery.subject" placeholder="e.g. Computer Science, Finance..." class="search-input">
                <button v-if="searchQuery.subject" @click="searchQuery.subject = ''" class="clear-input">×</button>
              </div>
            </div>

            <div class="filter-group">
              <div class="filter-header">
                <label class="filter-label">Filter by University</label>
                <button v-if="selectedUniversities.length" @click="selectedUniversities = []" class="clear-link">Clear All</button>
              </div>
              
              <div class="checkbox-container">
                <label v-for="uni in universityOptions" :key="uni" class="checkbox-item">
                  <input 
                    type="checkbox" 
                    :value="uni" 
                    v-model="selectedUniversities"
                    class="hidden-checkbox"
                  >
                  <span class="custom-checkbox">
                    <span class="check-mark">✓</span>
                  </span>
                  <span class="checkbox-text">{{ uni }}</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <div v-if="mentorsLoading" class="loading-state">Finding mentors...</div>
        <div v-else class="mentor-grid">
          <div v-for="mentor in filteredMentors" :key="mentor.id" class="mentor-card">
            <div class="mentor-avatar">
              <img :src="mentor.profile_photo_path ? `http://localhost:8000/storage/${mentor.profile_photo_path}` : 'https://via.placeholder.com/100'">
              <span class="verify-tag">Verified ✅</span>
            </div>
            <div class="mentor-info">
              <h3>{{ mentor.user?.name }}</h3>
              <div class="rating-bar" v-if="mentor.rating_avg > 0">
                <span class="star-icon">★</span>
                <span class="rating-num">{{ Number(mentor.rating_avg).toFixed(1) }}</span>
                <span class="review-qty">({{ mentor.reviews_count || 0 }} reviews)</span>
              </div>
              <div v-else class="no-rating-pill">No reviews yet...</div>
              <p class="uni-name">{{ mentor.university?.name || mentor.target_university || 'University not set' }}</p>
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
.checkbox-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 15px;
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  user-select: none;
}

.hidden-checkbox {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

.custom-checkbox {
  width: 20px;
  height: 20px;
  border: 2px solid #dee4fe; 
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: white;
  transition: all 0.2s ease;
  flex-shrink: 0; 
}

.hidden-checkbox:checked + .custom-checkbox {
  background-color: #3e55f8; 
  border-color: #3e55f8;
}

.check-mark {
  color: white;
  font-size: 14px;
  display: none;
  line-height: 1;
}

.hidden-checkbox:checked + .custom-checkbox .check-mark {
  display: block;
}

.checkbox-text {
  font-family: 'SF-UI Text', sans-serif;
  font-size: 0.95rem;
  color: #1a1f3c; 
  font-weight: 500;
}

.clear-link {
  background: none;
  border: none;
  color: #3e55f8;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  padding: 0;
  text-decoration: underline;
}

.dashboard-container {
  display: flex;
  gap: 2rem;
  padding: 2rem;
  width: 100%; 
  max-width: 100vw; 
  margin: 0;
  align-items: flex-start;
  background-color: #fbfcff;
  min-height: 100vh;
  font-family: 'SF-UI Text', sans-serif;
}

h1, h2, h3, strong {
  font-family: 'Red Hat Display', sans-serif;
  color: #1a1f3c; 
}

.sidebar {
  flex: 0 0 320px;
  position: sticky;
  top: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

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
  font-family: 'Red Hat Display', sans-serif;
}

.profile-card, .saved-mentors-section, .booking-tracker-section, .mentor-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  border: 1px solid #DEE4FE;
}

.form-group { margin-bottom: 1rem; }
label { 
  display: block; 
  font-weight: 600; 
  margin-bottom: 0.3rem; 
  color: #1a1f3c; 
  font-size: 0.9rem; 
}

input { 
  width: 100%; 
  padding: 10px; 
  border: 1px solid #DEE4FE; 
  border-radius: 8px; 
  font-size: 0.9rem; 
  font-family: 'SF-UI Text', sans-serif;
}

.save-btn, .connect-btn, .submit-btn {
  background-color: #3e55f8;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  width: 100%;
  font-family: 'Red Hat Display', sans-serif;
  transition: opacity 0.2s;
}

.save-btn:hover, .connect-btn:hover { opacity: 0.9; }

.mentor-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.mentor-avatar img {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #3e55f8; 
  margin-bottom: 1rem;
}

.verify-tag, .btn-text.success, .status-pill-minimal.approved { 
  color: #2DE1AA; 
  font-weight: 800;
}

.approved { 
  background: #f0fdf4; 
  color: #15803d; 
  border: 1px solid #2DE1AA; 
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

.tab-btn {
  background: none;
  border: none;
  padding: 0.5rem 1rem;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  position: relative;
  font-family: 'Red Hat Display', sans-serif;
}

.tab-btn.active {
  color: #3e55f8;
}

.tab-btn.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 100%;
  height: 2px;
  background: #3e55f8;
}

.star-icon { color: #F4BD3F; font-size: 1.1rem; }

.status-pill.pending { 
  background: #fff7ed; 
  color: #c2410c; 
  border: 1px solid #F4BD3F; 
}

.join-pill {
  background: #e6fffa;
  color: #065f46;
  border: 1px solid #2DE1AA;
  padding: 6px 12px;
  border-radius: 20px;
  text-decoration: none;
  font-weight: 600;
}

.sync-pill {
  border: 1px solid #DEE4FE;
  padding: 6px 12px;
  border-radius: 20px;
  background: white;
  color: #3e55f8;
  cursor: pointer;
}

@media (max-width: 1024px) {
  .dashboard-container { flex-direction: column; padding: 1rem; }
  .sidebar { flex: 1; width: 100%; position: static; }
}

.uni-name {
    font-size: 0.9rem;
    color: #6b7280; 
    margin: 0.2rem 0;
    font-weight: 500;
}

.edit-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 700;
    color: #6b7280;
    margin-bottom: 2px;
}

.resched-input {
    border: 1px solid #DEE4FE;
    padding: 6px;
    border-radius: 6px;
    font-size: 0.9rem;
    width: 100%;
}

.duration-badge-student {
    display: inline-block;
    background: #DEE4FE; 
    color: #3e55f8;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 700;
    margin-top: 4px;
    border: 1px solid #3e55f8;
}

.date-display {
    line-height: 1.4;
}

.multi-select-container {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.multi-select {
    height: 80px; 
    padding: 5px !important;
    background-color: white;
}

.filter-hint {
    font-size: 0.7rem;
    color: #6b7280;
    margin-top: 4px;
    display: flex;
    justify-content: space-between;
}

.clear-link {
    background: none;
    border: none;
    color: #3e55f8;
    text-decoration: underline;
    cursor: pointer;
    font-size: 0.7rem;
    padding: 0;
}

.search-filters {
    display: flex;
    flex-direction: column; 
    gap: 1rem;
}

.checkbox-container {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 10px;
  max-height: 200px; 
  overflow-y: auto; 
  padding-right: 5px;
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  padding: 4px 0;
  transition: opacity 0.2s;
}

.checkbox-item:hover {
  opacity: 0.8;
}

.hidden-checkbox {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

.custom-checkbox {
  width: 20px;
  height: 20px;
  border: 2px solid #dee4fe;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: white;
  transition: all 0.2s ease;
}

.hidden-checkbox:checked + .custom-checkbox {
  background-color: #3e55f8; 
  border-color: #3e55f8;
}

.check-mark {
  color: white;
  font-size: 14px;
  display: none;
}

.hidden-checkbox:checked + .custom-checkbox .check-mark {
  display: block;
}

.checkbox-text {
  font-family: 'SF-UI Text', sans-serif;
  font-size: 0.9rem;
  color: #1a1f3c;
  font-weight: 500;
}
</style>