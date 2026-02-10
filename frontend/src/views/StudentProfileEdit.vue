<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const profile = ref({
    target_university: '',
    target_country: ''
});
const loading = ref(false);
const message = ref('');

const fetchProfile = async () => {
    try {
        const response = await axios.get('/api/student/profile');
        if (response.data) {
            profile.value = response.data;
        }
    } catch (error) {
        console.error("Failed to load profile", error);
    }
};

const saveProfile = async () => {
    loading.value = true;
    try {
        await axios.put('/api/student/profile', profile.value);
        message.value = "Goals updated successfully!";
    } catch (error) {
        message.value = "Failed to save. Please try again.";
    } finally {
        loading.value = false;
    }
};

onMounted(fetchProfile);
</script>

<template>
    <div class="edit-profile-container">
        <h2>Your Educational Goals</h2>
        <p>This information helps us match you with the right alumni mentors.</p>
        
        <form @submit.prevent="saveProfile">
            <label>
                Target University:
                <input v-model="profile.target_university" type="text" placeholder="e.g. City St George's">
            </label>

            <label>
                Target Country:
                <input v-model="profile.target_country" type="text" placeholder="e.g. United Kingdom">
            </label>

            <button type="submit" :disabled="loading" class="submit-btn">
                {{ loading ? 'Saving...' : 'Update Profile' }}
            </button>
        </form>
        <p v-if="message" class="status-msg">{{ message }}</p>
    </div>
</template>

<style scoped>
/* Reuse your existing StudentOnboarding.vue styles for consistency */
.edit-profile-container {
    max-width: 600px;
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}
label { display: block; margin-bottom: 1rem; font-weight: 600; color: #1a1f3c; }
input { width: 100%; padding: 12px; margin-top: 8px; border: 1px solid #DEE4FE; border-radius: 8px; }
.submit-btn { 
    background-color: #3e55f8; color: white; padding: 12px 24px; 
    border: none; border-radius: 8px; cursor: pointer; font-weight: 700; 
}
.status-msg { margin-top: 1rem; color: #2DE1AA; font-weight: 600; }
</style>