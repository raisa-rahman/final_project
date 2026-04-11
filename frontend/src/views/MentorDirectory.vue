<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

/**
 * MentorDirectory component manages the searchable list of mentors.
 * It handles real-time filtering by subject and location, fetching
 * updated results whenever the user modifies their search criteria.
 */

 // Reactive state for the mentor list and loading indicator
const mentors = ref([]);
const loading = ref(true);
// Search parameters used to filter the directory results
const filters = ref({
    subject: '',
    country: ''
});

/**
 * Fetch the list of verified mentors from the backend API.
 * Uses the current filter values as query parameters to narrow down results.
 */
const fetchMentors = async () => {
    loading.value = true;
    try {
        const response = await axios.get('http://localhost:8000/api/mentors/directory', {
            params: {
                subject: filters.value.subject,
                country: filters.value.country
            }
        });
        mentors.value = response.data;
    } catch (error) {
        console.error("Directory Load Error:", error);
    } finally {
        loading.value = false;
    }
};

/**
 * Watcher: Automatically re-trigger the fetch process whenever 
 * any property within the 'filters' object changes.
 * { deep: true } ensures that nested property updates (like subject or country) are detected.
 */
watch(filters, () => fetchMentors(), { deep: true });

/**
 * Initialise the directory list as soon as the component is mounted to the DOM.
 */
onMounted(fetchMentors);
</script>

<template>
  <div class="directory-container">
    <aside class="filters">
      <h3>Search Mentors</h3>
      <input v-model="filters.subject" placeholder="Subject (e.g. Finance)" class="filter-input">
      <input v-model="filters.country" placeholder="Country (e.g. UK)" class="filter-input">
    </aside>

    <main class="results-grid">
      <div v-if="loading" class="loading">Finding verified mentors...</div>
      
      <div v-else-if="mentors.length === 0" class="no-results">
        No verified mentors found matching your search.
      </div>

      <div v-for="mentor in mentors" :key="mentor.id" class="mentor-card">
        <div class="card-header">
          <img :src="mentor.profile_photo_path ? `http://localhost:8000/storage/${mentor.profile_photo_path}` : '/default-avatar.png'" alt="Mentor photo">
          <span class="verify-badge">Verified ✅</span>
        </div>
        <h4>{{ mentor.user.name }}</h4>
        <p class="expertise">{{ mentor.subject_expertise }}</p>
        <p class="bio">{{ mentor.bio }}</p>
        <button class="book-btn">Book Session</button>
      </div>
    </main>
  </div>
</template>

<style scoped>
.directory-container { 
    display: grid; 
    grid-template-columns: 250px 1fr; 
    gap: 2rem; 
    padding: 2rem; 
    font-family: 'SF-UI Text', 'Arial', sans-serif;
    background-color: #fbfcff;
}

.filters { 
    background: #ffffff; 
    padding: 1.5rem; 
    border-radius: 12px; 
    height: fit-content; 
    border: 1px solid #DEE4FE;
    box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}

h2, h3 {
    font-family: 'Red Hat Display', sans-serif;
    font-weight: 700;
    color: #1a1f3c; 
}

.filter-input { 
    width: 100%; 
    padding: 10px; 
    margin-bottom: 1rem; 
    border: 1px solid #DEE4FE; 
    border-radius: 8px; 
    font-family: 'SF-UI Text', sans-serif;
}

.filter-input:focus {
    outline: none;
    border-color: #3e55f8;
}

.results-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }

.mentor-card { 
    background: white; 
    border: 1px solid #DEE4FE; 
    border-radius: 12px; 
    padding: 1.5rem; 
    transition: transform 0.2s, box-shadow 0.2s; 
}

.mentor-card:hover { 
    transform: translateY(-5px); 
    box-shadow: 0 10px 20px rgba(0,0,0,0.05); 
}

.expertise { 
    color: #3e55f8; 
    font-weight: 700; 
    margin: 0.5rem 0; 
    font-family: 'SF-UI Text', sans-serif;
}

.rating-stars {
    color: #F4BD3F;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.book-btn { 
    width: 100%; 
    background: #3e55f8; 
    color: white; 
    border: none; 
    padding: 12px; 
    border-radius: 8px; 
    cursor: pointer; 
    font-family: 'Red Hat Display', sans-serif;
    font-weight: 700;
    transition: opacity 0.2s;
}

.book-btn:hover {
    opacity: 0.9;
}
</style>