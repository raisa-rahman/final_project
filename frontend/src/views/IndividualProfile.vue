<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const mentor = ref(null);
const loading = ref(true);
const mentorId = route.params.id;

const showBookingModal = ref(false);
const bookingRequest = ref({
    mentor_profile_id: mentorId,
    request_date: '',
    start_time: '',
    end_time: '',
    message: ''
});

const fetchMentorDetails = async () => {
    loading.value = true; 
    try {
        const response = await axios.get(`http://localhost:8000/api/mentors/profile/${mentorId}`);
        mentor.value = response.data;
    } catch (err) {
        console.error("API Error:", err.response?.data);
        alert("Could not load mentor profile. They may no longer be available.");
        router.push('/dashboard'); 
    } finally {
        loading.value = false;
    }
};

const submitRequest = async () => {
    try {
        await axios.post('http://localhost:8000/api/meetings/request', bookingRequest.value);
        alert("Request sent successfully! You can view the status in your dashboard.");
        showBookingModal.value = false;
    } catch (err) {
        alert(err.response?.data?.message || "Error sending request. Please ensure all fields are filled.");
    }
};

onMounted(fetchMentorDetails);
</script>

<template>
  <div class="profile-page">
    <div v-if="loading" class="loading">Loading profile...</div>
    
    <div v-else-if="mentor" class="profile-container">
      <button @click="router.back()" class="back-link">← Back to Directory</button>
      
      <div class="profile-header">
        <div class="avatar-section">
          <img 
            :src="mentor.profile_photo_path ? `http://localhost:8000/storage/${mentor.profile_photo_path}` : 'https://via.placeholder.com/150'" 
            class="profile-img"
          >
          <div class="status-badge">Verified Mentor ✅</div>
        </div>
        
        <div class="info-section">
    <h1>{{ mentor.user?.name }}</h1>
    
    <div class="profile-rating-badge" v-if="mentor.reviews && mentor.reviews.length > 0">
        <span class="stars">★ ★ ★ ★ ★</span>
        <span class="rating-text">
            {{ Number(mentor.rating_avg || 0).toFixed(1) }} 
            ({{ mentor.reviews.length }} {{ mentor.reviews.length === 1 ? 'review' : 'reviews' }})
        </span>
    </div>
    
    <div v-else class="no-rating-badge" style="color: #6b7280; font-style: italic; margin: 0.5rem 0;">
        No reviews yet
    </div>

    <p class="expertise-tag">{{ mentor.subject_expertise }}</p>
    <p class="uni-name">{{ mentor.university?.name || mentor.target_university }}</p>
</div>
      </div>

      <div class="profile-content">
        <section class="bio-card">
          <h3>About Me</h3>
          <p>{{ mentor.bio || 'This mentor hasn’t added a bio yet.' }}</p>
        </section>

        <section class="expertise-card">
          <h3>Mentorship Focus</h3>
          <ul>
            <li>Application Strategy</li>
            <li>Cultural Adaptation in {{ mentor.target_country }}</li>
            <li>Subject Deep-dives</li>
          </ul>
          <button @click="showBookingModal = true" class="book-now-btn">Request a Session</button>
        </section>
      </div>

      <div v-if="showBookingModal" class="modal-overlay" @click.self="showBookingModal = false">
        <div class="booking-modal">
          <h2>Request a Mentorship Session</h2>
          <p class="subtitle">Suggest a time for Dr. {{ mentor.user?.name }} to review.</p>
          
          <div class="form-group">
            <label>Preferred Date</label>
            <input type="date" v-model="bookingRequest.request_date" :min="new Date().toISOString().split('T')[0]">
          </div>

          <div class="time-row">
            <div class="form-group">
                <label>Start Time</label>
                <input type="time" v-model="bookingRequest.start_time">
            </div>
            <div class="form-group">
                <label>End Time</label>
                <input type="time" v-model="bookingRequest.end_time">
            </div>
          </div>

          <div class="form-group">
            <label>Message (Optional)</label>
            <textarea v-model="bookingRequest.message" placeholder="What would you like to discuss?"></textarea>
          </div>

          <div class="modal-actions">
            <button @click="submitRequest" class="submit-btn">Send Request</button>
            <button @click="showBookingModal = false" class="cancel-btn">Cancel</button>
          </div>
        </div>
      </div>
      <section class="reviews-section" v-if="mentor.reviews && mentor.reviews.length > 0">
    <h3 class="section-title">Student Feedback</h3>
    <div v-for="review in mentor.reviews" :key="review.id" class="review-item">
        <div class="review-header">
            <span class="stars">★ {{ review.rating }}</span>
            <span class="review-date">{{ new Date(review.created_at).toLocaleDateString() }}</span>
        </div>
        <p class="review-comment">{{ review.comment || 'No comment provided.' }}</p>
    </div>
</section>
    </div>
  </div>
</template>

<style scoped>
.profile-page { 
    padding: 3rem 5%; 
    margin: 0 auto; 
    font-family: 'SF-UI Text', sans-serif; 
    background-color: #fbfcff;
}

.profile-header { 
    display: flex; 
    gap: 3rem; 
    align-items: center; 
    margin: 2rem 0; 
    background: white; 
    padding: 2rem; 
    border-radius: 15px; 
    box-shadow: 0 4px 20px rgba(0,0,0,0.05); 
    border: 1px solid #DEE4FE;
}

h1, h2, .section-title {
    font-family: 'Red Hat Display', sans-serif; 
    font-weight: 700;
    color: #1a1f3c;
}

.profile-img { 
    width: 150px; 
    height: 150px; 
    border-radius: 50%; 
    object-fit: cover; 
    border: 4px solid #3e55f8; 
}

.expertise-tag { 
    color: #3e55f8; 
    font-family: 'Red Hat Display', sans-serif;
    font-weight: 700; 
    font-size: 1.2rem; 
    margin: 0.5rem 0; 
}

.profile-content { display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem; }

.bio-card, .expertise-card, .reviews-section { 
    background: white; 
    padding: 2rem; 
    border-radius: 12px; 
    box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
    border: 1px solid #DEE4FE;
}

.book-now-btn, .submit-btn { 
    width: 100%; 
    background: #3e55f8; 
    color: white; 
    border: none; 
    padding: 15px; 
    border-radius: 8px; 
    font-family: 'Red Hat Display', sans-serif;
    font-weight: 700; 
    cursor: pointer; 
    margin-top: 1rem; 
    transition: opacity 0.2s;
}

.book-now-btn:hover, .submit-btn:hover {
    opacity: 0.9;
}

.back-link { 
    background: none; 
    border: none; 
    color: #6b7280; 
    cursor: pointer; 
    font-weight: 600; 
    font-family: 'SF-UI Text', sans-serif;
}

.modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(26, 31, 60, 0.8); 
    display: flex;
    justify-content: center; align-items: center; z-index: 1000;
}

.booking-modal {
    background: white; padding: 2.5rem; border-radius: 15px;
    width: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    font-family: 'SF-UI Text', sans-serif;
}

label { 
    display: block; 
    font-weight: 600; 
    margin-bottom: 0.5rem; 
    color: #1a1f3c; 
}

input, textarea { 
    width: 100%; 
    padding: 12px; 
    border: 1px solid #DEE4FE; 
    border-radius: 8px; 
    font-family: 'SF-UI Text', sans-serif;
}

.profile-rating-badge {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0.5rem 0;
    padding: 6px 14px;
    background: #e6fffa; 
    border-radius: 20px;
    width: fit-content;
}

.stars {
    color: #F4BD3F; 
    letter-spacing: 2px;
    font-size: 0.9rem;
}

.rating-text {
    font-weight: 700;
    color: #065f46; 
    font-size: 0.9rem;
}

.section-title {
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #DEE4FE;
}

.review-comment {
    color: #4b5563;
    line-height: 1.6;
    font-family: 'SF-UI Text', sans-serif;
}
</style>