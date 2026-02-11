<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const mentors = ref([]);
const loading = ref(true);
const filters = ref({
    subject: '',
    country: ''
});

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

watch(filters, () => fetchMentors(), { deep: true });

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
.directory-container { display: grid; grid-template-columns: 250px 1fr; gap: 2rem; padding: 2rem; }
.filters { background: #f8faff; padding: 1.5rem; border-radius: 12px; height: fit-content; }
.filter-input { width: 100%; padding: 10px; margin-bottom: 1rem; border: 1px solid #dee4fe; border-radius: 8px; }
.results-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
.mentor-card { background: white; border: 1px solid #eee; border-radius: 12px; padding: 1.5rem; transition: transform 0.2s; }
.mentor-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
.expertise { color: #3e55f8; font-weight: 700; margin: 0.5rem 0; }
.book-btn { width: 100%; background: #3e55f8; color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; }
</style>