<script setup>
import axios from 'axios';
import {ref} from 'vue';

const form = ref ({
  name: '',
  email:'',
  password:'',
  password_confirmation:'',
  role:'mentor'
});

const handleRegister = async () => {
  await axios.get('http://localhost:8000/sanctum/csrf-cookie');

  try{
    await axios.post('http://localhost:8000/register', form.value);
    alert('Registration Successful! Your profile is pending verification.')
  }catch(error){
    console.error("Registration failed", error.response?.data || error.message);
  }
};
</script>

<template>
  <div class="onboarding-container" style="max-width: 600px; margin: 2rem auto; font-family: sans-serif;">
    <h1>Join as a Mentor</h1>
    <p>Help prospective international students navigate their journey.</p>
    
    <form @submit.prevent = "handleRegister" style="display: flex; flex-direction: column; gap: 1rem; border: 1px solid #eee; padding: 2rem; border-radius: 8px;">
      
      <label>
        Full Name:
        <input v-model = "form.name" type="text" required placeholder="e.g. Jane Doe">
      </label>

      <label>
        Email:
        <input v-model="form.email" type = "email" required placeholder = "email@example.com" >
      </label>

      <label>
        Password:
        <input v-model = "form.password" type = "password" required  >
      </label>

      <label>
        Confirm Password:
        <input v-model = "form.password_confirmation" type = "password" required >
      </label>

      <button type="submit">
        Submit for Verification
      </button>
    </form>
  </div>
</template>
<style scoped>
/* Importing the font from Google Fonts */
@import url('https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@700&display=swap');

.onboarding-container {
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  padding: 40px;
  /* Use SF-UI Text equivalent if not on Mac */
  font-family: 'SF Pro Text', 'Helvetica Neue', sans-serif; 
}

h1 {
  font-family: 'Red Hat Display', sans-serif;
  color: #3e55f8; /* Your Blue Theme */
  margin-bottom: 0.5rem;
}

p {
  color: #6b7280;
  margin-bottom: 2rem;
}

label {
  font-weight: 500;
  font-size: 0.9rem;
  color: #374151;
}

input {
  width: 100%;
  padding: 12px;
  margin-top: 6px;
  border: 1px solid #DEE4FE; /* Your Light Purple Theme */
  border-radius: 8px;
  transition: border-color 0.2s;
}

input:focus {
  outline: none;
  border-color: #3e55f8; /* Blue focus */
}

button {
  background-color: #3e55f8; /* Your Blue Theme */
  color: white;
  padding: 14px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 1rem;
  transition: transform 0.1s, background-color 0.2s;
}

button:hover {
  background-color: #2DE1AA; /* Your Green Theme on hover */
}

button:active {
  transform: scale(0.98);
}
</style>