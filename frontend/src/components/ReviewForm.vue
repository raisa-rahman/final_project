<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    meetingId: Number,
    mentorId: Number
});

const emit = defineEmits(['review-submitted']);

const rating = ref(0);
const hover = ref(0);
const comment = ref('');
const isSubmitting = ref(false);

const submitReview = async () => {
    if (rating.value === 0) return alert("Please select a star rating.");
    
    console.log("Submitting with:", {
        meeting_id: props.meetingId,
        mentor_profile_id: props.mentorId
    });

    isSubmitting.value = true;
    try {
await axios.post('http://localhost:8000/api/reviews', {
    meeting_id: props.meetingId,
    mentor_profile_id: props.mentorId, 
    rating: rating.value,       
    comment: comment.value       
});
        emit('review-submitted'); 
    } catch (err) {
        console.error("Detailed Error:", err.response?.data);
        alert(err.response?.data?.message || "Submission failed. Please try again.");
    } finally {
        isSubmitting.value = false;
    }
};

</script>

<template>
    <div class="review-card">
        <h3>Rate Your Session</h3>
        <p class="subtitle">Your feedback helps maintain community trust.</p>

        <div class="star-rating">
            <span 
                v-for="star in 5" 
                :key="star"
                class="star"
                :class="{ 'filled': (hover || rating) >= star }"
                @mouseover="hover = star"
                @mouseleave="hover = 0"
                @click="rating = star"
            >
                ★
            </span>
        </div>

        <textarea 
            v-model="comment" 
            placeholder="Share your experience with this mentor..."
            class="comment-area"
        ></textarea>

        <button 
            @click="submitReview" 
            :disabled="isSubmitting" 
            class="submit-review-btn"
        >
            {{ isSubmitting ? 'Saving...' : 'Submit Feedback' }}
        </button>
    </div>
</template>

<style scoped>
.review-card {
    background: #f8faff;
    padding: 1.5rem;
    border-radius: 12px;
    border: 1px solid #dee4fe;
    margin-top: 1rem;
}

.star-rating {
    font-size: 2rem;
    margin-bottom: 1rem;
    cursor: pointer;
}

.star { color: #e5e7eb; transition: color 0.2s; }
.star.filled { color: #F4BD3F; } 

.comment-area {
    width: 100%;
    height: 100px;
    padding: 10px;
    border: 1px solid #dee4fe;
    border-radius: 8px;
    font-family: inherit;
    margin-bottom: 1rem;
}

.submit-review-btn {
    background: #3e55f8;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    width: 100%;
}
</style>

