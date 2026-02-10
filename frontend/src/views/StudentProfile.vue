<template>
  <div class="profile-container" v-if="user">
    <h1>Edit Your Student Profile</h1>
    <form @submit.prevent="updateProfile" class="profile-form">
      <div class="grid">
        <label>Full Name
          <input v-model="user.name" type="text">
        </label>
        <label>Target Country
          <input v-model="profile.target_country" type="text" placeholder="e.g. UK, Australia">
        </label>
        <label>Target University
          <input v-model="profile.target_university" type="text">
        </label>
        <label>Current Study Level
          <select v-model="profile.study_level">
            <option value="undergraduate">Undergraduate</option>
            <option value="postgraduate">Postgraduate</option>
          </select>
        </label>
      </div>
      <button type="submit" class="save-btn">Save Details</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const user = ref(null);
const profile = ref({ target_country: '', target_university: '', study_level: '' });

onMounted(async () => {
  const res = await axios.get('/api/user');
  user.value = res.data;
  if (res.data.mentee_profile) profile.value = res.data.mentee_profile;
});

const updateProfile = async () => {
  try {
    await axios.put('/api/mentee/profile', { ...profile.value, name: user.value.name });
    alert("Profile updated successfully!");
  } catch (err) {
    console.error(err);
  }
};
</script>