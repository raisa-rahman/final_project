<script setup>
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const props = defineProps(['mentor', 'isFavorited']);
const emit = defineEmits(['toggle-fav']);

const handleFavorite = async () => {
    try {
        const res = await axios.post(`http://localhost:8000/api/mentors/${props.mentor.id}/favorite`);
        emit('toggle-fav', props.mentor.id); 
    } catch (err) {
        console.error("Favorite toggle failed", err);
    }
};
</script>

<template>
  <div class="mentor-card">
    <button @click.stop="handleFavorite" class="fav-btn">
  <span :class="{ 'heart-active': isFavorited }">♥</span>
  </button>

    <div class="mentor-avatar">
      <img 
        :src="mentor.profile_photo_path ? `http://localhost:8000/storage/${mentor.profile_photo_path}` : 'https://via.placeholder.com/80'" 
        alt="Mentor"
      />
      <span class="verify-tag">Verified ✅</span>
    </div>

    <div class="mentor-info">
    <h3>{{ mentor.user?.name }}</h3>
    
    <div class="rating-display" v-if="mentor.rating_avg && Number(mentor.rating_avg) > 0">
    <span class="stars">★</span> 
    <strong>{{ Number(mentor.rating_avg).toFixed(1) }}</strong> 
    <span class="review-count">({{ mentor.reviews_count || 0 }} reviews)</span>
</div>
<div v-else class="no-rating">No reviews yet</div>

    <p class="expertise">{{ mentor.subject_expertise }}</p>
    <button @click="router.push(`/mentor/profile/${mentor.id}`)" class="connect-btn">
        View Profile
    </button>
</div>
  </div>
</template>

<style scoped>
.mentor-card { 
    position: relative; 
    background: white; 
    border-radius: 12px; 
    padding: 1.5rem; 
    box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
    border: 1px solid #DEE4FE; 
    text-align: center; 
    font-family: 'SF-UI Text', sans-serif;
}

.fav-btn { 
    position: absolute; 
    top: 10px; 
    right: 10px; 
    background: none; 
    border: none; 
    font-size: 1.5rem; 
    cursor: pointer; 
    color: #d1d5db; 
    transition: color 0.2s; 
}

.heart-active { color: #ef4444; }

.mentor-avatar img { 
    width: 80px; 
    height: 80px; 
    border-radius: 50%; 
    object-fit: cover; 
    border: 3px solid #3e55f8; 
}

.verify-tag { 
    background: #2DE1AA; 
    color: white; 
    font-size: 0.7rem; 
    padding: 2px 8px; 
    border-radius: 20px; 
    font-family: 'Red Hat Display', sans-serif;
    font-weight: 800; 
    text-transform: uppercase;
}

h3 {
    font-family: 'Red Hat Display', sans-serif;
    font-weight: 700;
    color: #1a1f3c; 
    margin: 0.5rem 0;
}

.expertise { 
    color: #3e55f8; 
    font-weight: 700; 
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.connect-btn { 
    background: #3e55f8; 
    color: white; 
    border: none; 
    padding: 10px; 
    border-radius: 8px; 
    width: 100%; 
    cursor: pointer; 
    margin-top: 10px; 
    font-family: 'Red Hat Display', sans-serif;
    font-weight: 700;
    transition: opacity 0.2s;
}

.connect-btn:hover {
    opacity: 0.9;
}
</style>