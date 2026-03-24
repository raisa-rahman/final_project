<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, RouterLink, RouterView } from 'vue-router';

import fbLogo from '@/assets/fb.png';
import xLogo from '@/assets/x.png';
import inLogo from '@/assets/in.png';
import igLogo from '@/assets/ig.jpg';

const route = useRoute();

</script>

<template>
  <div class="app-wrapper">
    <header class="main-header">
      <div class="header-content">
        <div class="logo-section">
          <RouterLink to="/" class="brand-link">
            <span class="brand-text">STUDENT CIRCUS</span>
          </RouterLink>
        </div>
        
        <nav class="nav-container">
          <div class="nav-group">
            <a href="http://localhost:5173/student/login" class="nav-item">Students</a>
          </div>
          <div class="divider">|</div>
          <div class="nav-group">
            <a href="http://localhost:5173/mentor/login" class="nav-item">Mentors</a>
          </div>
          <div class="divider">|</div>
          <div class="staff-section">
            <RouterLink to="/staff-portal" class="staff-link">Staff Portal</RouterLink>
          </div>
        </nav>
      </div>
    </header>

    <main class="content-area">
      <RouterView />
    </main>

    <footer class="app-footer">
      <div class="footer-content">
        <div class="footer-section brand-info">
          <h2 class="footer-logo">STUDENT CIRCUS</h2>
          <p class="footer-description">
            The first and only job platform exclusively for international students in the UK.
          </p>
          <div class="trust-seals">
            <span class="trust-badge">Verified Partner</span>
            <span class="trust-badge">UK Careers Accredited</span>
          </div>
        </div>

        <div class="footer-section">
          <h3>Support & Portals</h3> <ul class="footer-links">
            <li><a href="http://localhost:5173/student/login">Student Login</a></li>
            <li><a href="http://localhost:5173/mentor/login">Mentor Login</a></li>
            <li><RouterLink to="/staff-portal">Staff Portal</RouterLink></li>
            <li class="contact-highlight">
              <a href="https://studentcircus.com/contact-us" target="_blank">Contact Us</a>
            </li>
          </ul>
        </div>

        <div class="footer-section">
          <h3>Follow Us</h3>
          <div class="social-icons">
            <a href="https://www.facebook.com/studentcircus/" target="_blank" aria-label="Facebook">
              <img :src="fbLogo" alt="Facebook" class="social-img" />
            </a>
            <a href="http://x.com/studentcircus" target="_blank" aria-label="X">
              <img :src="xLogo" alt="X" class="social-img" />
            </a>
            <a href="https://www.linkedin.com/company/student-circus/" target="_blank" aria-label="LinkedIn">
              <img :src="inLogo" alt="LinkedIn" class="social-img" />
            </a>
            <a href="https://www.instagram.com/studentcircus/?hl=en" target="_blank" aria-label="Instagram">
              <img :src="igLogo" alt="Instagram" class="social-img" />
            </a>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; 2026 Student Circus. All rights reserved.</p>
        <div class="legal-links">
          <a href="#">Privacy Policy</a>
          <a href="#">Terms of Service</a>
          <a href="#">Sitemap</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.main-header {
  background-color: #1a1f3c; 
  width: 100%;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1000;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

.brand-link {
  display: flex;
  align-items: center;
  text-decoration: none;
}

.brand-text {
  color: white;
  font-family: 'Red Hat Display', sans-serif;
  font-weight: 800;
  font-size: 1.2rem;
  letter-spacing: 1px;
}

.nav-container {
  display: flex;
  align-items: center;
  gap: 1.5rem; 
}

.nav-item {
  color: white;
  text-decoration: none;
  font-family: 'SF-UI Text', sans-serif;
  font-size: 0.9rem;
  font-weight: 600;
  transition: color 0.2s;
}

.nav-item:hover {
  color: #3e55f8; 
}

.divider {
  color: rgba(255, 255, 255, 0.3);
  font-weight: 300;
  user-select: none;
}

.staff-link {
  color: #DEE4FE; 
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 600;
}

main {
  padding-top: 80px; 
}

.no-padding {
  padding-top: 0 !important;
}

.main-header {
  background-color: #1a1f3c; 
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
  gap: 1.5rem; 
}

.nav-group {
  display: flex;
  gap: 1rem;
}

.nav-item {
  color: white;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 500;
  white-space: nowrap;
}

.nav-item:hover {
  color: #3e55f8;
}

.divider {
  color: rgba(255, 255, 255, 0.3);
  font-weight: 300;
  user-select: none;
}

.staff-link {
  color: #9ca3af;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 500;
}

main {
  padding-top: 80px; 
}

.main-header {
  background-color: #1a1f3c; 
  width: 100%;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1000;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

.brand-link {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
}

.nav-logo {
  height: 35px;
}

.brand-text {
  color: white;
  font-family: 'Red Hat Display', sans-serif; 
  font-weight: 800;
  font-size: 1.2rem;
  letter-spacing: 1px;
}

.nav-container {
  display: flex;
  align-items: center;
  gap: 1.5rem; 
}

.nav-item {
  color: white;
  text-decoration: none;
  font-family: 'SF-UI Text', sans-serif; 
  font-size: 0.9rem;
  font-weight: 600;
  transition: color 0.2s;
}

.nav-item:hover {
  color: #3e55f8;
}

.divider {
  color: rgba(255, 255, 255, 0.3);
  font-weight: 300;
}

.staff-link {
  color: #DEE4FE; 
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 600;
}

main {
  padding-top: 80px; 
}

.no-padding {
  padding-top: 0 !important;
}

.main-header {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 2000; 
  background-color: #1a1f3c;
  height: 80px;
}

.content-area {
  padding-top: 80px; 
  min-height: calc(100vh - 80px);
}


.app-footer {
  background-color: #1a1f3c; 
  color: #ffffff;
  padding: 4rem 40px 1rem;
  margin-top: auto;
  border-top: 4px solid #3e55f8; 
}

.footer-content {
  max-width: 1400px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 2fr;
  gap: 40px;
  padding-bottom: 3rem;
}

.footer-section h3 {
  font-family: 'Red Hat Display', sans-serif;
  font-size: 1.1rem;
  margin-bottom: 1.5rem;
  color: #dee4fe;
}

.footer-logo {
  font-family: 'Red Hat Display', sans-serif;
  font-weight: 800;
  letter-spacing: 2px;
  margin-bottom: 1rem;
}

.footer-description {
  font-size: 0.9rem;
  line-height: 1.6;
  color: #9ca3af;
  margin-bottom: 1.5rem;
}

.footer-links {
  list-style: none;
  padding: 0;
}

.footer-links li {
  margin-bottom: 0.8rem;
}

.footer-links a {
  color: #9ca3af;
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.2s;
}

.footer-links a:hover {
  color: #3e55f8;
}

.social-icons {
  display: flex;
  gap: 15px;
}

.social-icons a {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.1);
  width: 35px;
  height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  text-decoration: none;
  font-size: 0.8rem;
  font-weight: bold;
}

.newsletter-input {
  display: flex;
  gap: 10px;
}

.newsletter-input input {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 0.8rem;
  border-radius: 4px;
  color: white;
  width: 100%;
}

.btn-subscribe {
  background-color: #3e55f8;
  color: white;
  border: none;
  padding: 0.8rem 1.5rem;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
}

.footer-bottom {
  max-width: 1400px;
  margin: 0 auto;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.8rem;
  color: #6b7280;
}

.legal-links {
  display: flex;
  gap: 20px;
}

.legal-links a {
  color: #6b7280;
  text-decoration: none;
}

.trust-badge {
  display: inline-block;
  border: 1px solid #3e55f8;
  color: #3e55f8;
  font-size: 0.7rem;
  padding: 2px 8px;
  border-radius: 4px;
  margin-right: 10px;
  font-weight: 700;
  text-transform: uppercase;
}

@media (max-width: 768px) {
  .footer-content {
    grid-template-columns: 1fr;
  }
}

.social-icons a {
  width: 40px; 
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  text-decoration: none;
  overflow: hidden; 
  transition: transform 0.2s, background 0.2s;
}

.social-icons a:hover {
  background: #3e55f8;
  transform: translateY(-3px);
}

.social-img {
  width: 100%;
  height: 100%;
  object-fit: cover; 
}

.contact-highlight a {
  color: #2de1aa !important; 
  font-weight: 700;
}

.contact-highlight a:hover {
  color: #ffffff !important;
  text-decoration: underline;
}

.footer-links li {
  margin-bottom: 0.8rem;
  display: flex;
  align-items: center;
}
</style>