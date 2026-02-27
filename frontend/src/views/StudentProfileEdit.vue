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
.auth-wrapper {
  background-color: #fbfcff; 
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding-top: 5rem;
  font-family: 'SF-UI Text', 'Arial', sans-serif;
}

.onboarding-container {
  width: 100%;
  max-width: 500px;
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
  border: 1px solid #DEE4FE;
  overflow: hidden;
}

.brand-header {
  text-align: center;
  padding: 2rem 0 1rem;
}

.logo { height: 50px; }

.tabs {
  display: flex;
  border-bottom: 1px solid #DEE4FE; 
}

.tabs button {
  flex: 1;
  padding: 1rem;
  background: none;
  border: none;
  font-family: 'SF-UI Text', sans-serif;
  font-weight: 700;
  color: #9ca3af;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tabs button.active {
  color: #3e55f8; 
  border-bottom: 3px solid #3e55f8;
}

.form-content { padding: 2rem 3rem; }

h1 {
  font-family: 'Red Hat Display', sans-serif;
  color: #1a1f3c; 
  margin-bottom: 0.5rem;
  font-size: 1.5rem;
  font-weight: 700;
}

p {
  font-family: 'SF-UI Text', sans-serif;
  color: #6b7280;
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
  line-height: 1.5;
}

label {
  display: block;
  font-weight: 600;
  font-size: 0.85rem;
  color: #1a1f3c;
  margin-bottom: 1rem;
}

input {
  width: 100%;
  padding: 12px;
  margin-top: 6px;
  border: 1px solid #DEE4FE; 
  border-radius: 8px;
  font-family: 'SF-UI Text', sans-serif;
  font-size: 0.95rem;
  transition: border-color 0.2s;
}

input:focus {
  outline: none;
  border-color: #3e55f8; 
}

.submit-btn {
  width: 100%;
  background-color: #3e55f8; 
  color: white;
  padding: 14px;
  border: none;
  border-radius: 8px;
  font-family: 'Red Hat Display', sans-serif;
  font-weight: 700;
  cursor: pointer;
  margin-top: 1rem;
  transition: background-color 0.3s ease;
}

.submit-btn:hover {
  background-color: #2DE1AA;
}

.institution-link {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.8rem;
  font-family: 'SF-UI Text', sans-serif;
}

.institution-link a { 
  color: #3e55f8; 
  text-decoration: none; 
  font-weight: 600; 
}
</style>