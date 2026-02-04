<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, RouterLink, RouterView } from 'vue-router';

const route = useRoute();
const isDashboard = computed(() => route.path === '/dashboard' || route.path === '/adminlogin/home');
</script>

<template>
  <div class="app-wrapper">
    <header v-if="!isDashboard">
      <nav class="nav-links">
        <RouterLink to="/">Home</RouterLink>
        <RouterLink to="/mentors">Find a Mentor</RouterLink>
        <RouterLink to="/join">Become a Mentor</RouterLink>
        <RouterLink to="/login">Login</RouterLink>
        <RouterLink to="/adminlogin" class="admin-link">Staff Portal</RouterLink>
      </nav>
    </header>

    <main>
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
nav { padding: 1rem; text-align: center; }
nav a { padding: 0 1rem; text-decoration: none; color: #3e55f8; font-weight: 500; }

.admin-link {
  color: #6b7280; 
  margin-left: 20px;
  font-weight: 500;
  text-decoration: none;
  font-size: 0.9rem;
  border-left: 1px solid #DEE4FE; 
  padding-left: 20px !important;
}

.admin-link:hover {
  color: #3e55f8; 
}
</style>