<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, RouterLink, RouterView } from 'vue-router';

const route = useRoute();
const isDashboard = computed(() => route.path === '/dashboard' || route.path === '/adminlogin/home');
</script>

<template>
  <div class="app-wrapper">
    
    <header v-if="!isDashboard" class="main-header">
      <div class="header-content">
        <div class="logo-section">
          <RouterLink to="/" class="brand-logo">STUDENT CIRCUS</RouterLink>
        </div>
        
        <nav class="nav-container">
  <div class="nav-group">
    <RouterLink to="/student/login" class="nav-item">Students</RouterLink>
  </div>

  <div class="divider">|</div>

  <div class="nav-group">
    <RouterLink to="/mentor/login" class="nav-item">Mentors</RouterLink>
  </div>

  <div class="divider">|</div>

  <div class="staff-section">
    <RouterLink to="/staff-portal" class="staff-link">Staff Portal</RouterLink>
  </div>
</nav>
      </div>
    </header>

    <main>
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
.main-header {
  background-color: #1a1f3c; /* Brand Navy */
  width: 100%;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1000;
}

.header-content {
  max-width: 1400px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 40px;
  height: 80px;
}

.brand-logo {
  color: white;
  font-weight: 800;
  text-decoration: none;
  font-size: 1.2rem;
  letter-spacing: 1px;
}

.nav-container {
  display: flex;
  align-items: center;
  gap: 1.5rem; /* Gap between groups and dividers */
}

.nav-group {
  display: flex;
  gap: 1rem; /* Gap between Sign up and Log In */
}

.nav-item {
  color: white;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 500;
  white-space: nowrap;
}

.nav-item:hover {
  color: #3e55f8; /* Brand Blue on hover */
}

.divider {
  color: rgba(255, 255, 255, 0.3);
  font-weight: 300;
  user-select: none;
}

.staff-link {
  color: #9ca3af; /* Muted gray for internal staff portal */
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 500;
}

main {
  padding-top: 80px; /* Offset for the fixed header */
}
</style>