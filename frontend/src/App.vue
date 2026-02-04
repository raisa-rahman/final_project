<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, RouterLink, RouterView } from 'vue-router';

const route = useRoute();
// Day 5: Logic to hide the main nav when the user is logged into the dashboard
const isDashboard = computed(() => route.path === '/dashboard');
</script>

<template>
  <div class="app-wrapper">
    <header v-if="!isDashboard">
      <nav>
        <RouterLink to="/">Home</RouterLink>
        <RouterLink to="/mentors">Find a Mentor</RouterLink>
        <RouterLink to="/join">Become a Mentor</RouterLink>
        <RouterLink to="/login">Login</RouterLink>
      </nav>
    </header>

    <main>
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
/* Keep your existing styles here */
nav { padding: 1rem; text-align: center; }
nav a { padding: 0 1rem; text-decoration: none; color: #3e55f8; }
</style>