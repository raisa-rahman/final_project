<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();

interface Mentor {
  id: number;
  profile_photo_path: string;
  subject_expertise: string;
  university: string;
  user: {
    name: string;
  };
}

const featuredMentors = ref<Mentor[]>([]);

onMounted(async () => {
  try {
    const response = await axios.get('/api/mentors/directory');
    featuredMentors.value = response.data.slice(0, 3); 
  } catch (error) {
    console.error("Failed to load mentor faces:", error);
  }
});
</script>

<template>
  <div class="page-wrapper">
    <main v-if="route.path === '/'" class="home-container">
      
      <div class="hero-section">
        <h1 class="welcome-title">Welcome to Student Circus</h1>
        
        <div class="video-container">
          <iframe 
            width="560" 
            height="315" 
            src="https://www.youtube.com/embed/LTeLtvWey8c?autoplay=1&mute=1&loop=1&playlist=LTeLtvWey8c" 
            title="Student Circus Hero Video" 
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen
            class="hero-video">
          </iframe>
        </div>
                
        <div class="intro-text">
          <h2 class="subhead">Verified Mentorship from Top UK Universities</h2>
          <p>
            Stop guessing your career path. Gain direct access to a network of verified professionals 
            from world-class institutions who understand the unique journey of international students.
          </p>
        </div>

        <div class="trust-section">
          <p class="trust-label">PARTNERING WITH TOP INSTITUTIONS</p>
          <div class="logo-grid">
            <img src="@/assets/kcl.png" alt="Kings College London" class="trust-logo" />
            <img src="@/assets/city.png" alt="City University of London" class="trust-logo" />
          </div>
        </div>

        <div class="cta-container">
          <h3 class="subhead-small">Ready to Unlock Your Global Career Potential?</h3>
          <a href="http://localhost:5173/student/signup" class="btn-primary">Find a Verified Mentor</a>
          <p class="cta-subtext">
            Are you an industry professional? 
            <a href="http://localhost:5173/mentor/signup" class="cta-link">Join our Mentor Network</a>
          </p>
        </div>
      </div>

      <section class="conversation-section">
        <div class="content-wrapper">
          <h2 class="subhead">We know the international student struggle. We’ve lived it.</h2>
          
          <div class="qa-grid">
            <div class="qa-item">
              <h3>"How do I know these mentors actually know about Tier 2 visas?"</h3>
              <p>Every mentor is manually verified. We prioritize professionals who were once international students themselves. You're getting a roadmap for the UK job market.</p>
            </div>

            <div class="qa-item">
              <h3>"Is this just another generic job board?"</h3>
              <p>No. Job boards tell you <i>where</i> to apply; our mentors tell you <i>how</i> to get hired, navigating the 'Grey Market' and cultural nuances.</p>
            </div>

            <div class="qa-item">
              <h3>"What if I'm not from a top-tier university?"</h3>
              <p>Your university is just one part of your story. Our mentors help you showcase the unique resilience that comes from moving countries to study.</p>
            </div>
          </div>
        </div>
      </section>

      <section class="team-showcase">
        <div class="content-wrapper">
          <h2 class="subhead">Guidance from Real Professionals</h2>
          <p class="section-intro">Our community is built by people who have walked in your shoes.</p>
          
          <div class="mentor-faces-grid">
            <div v-for="mentor in featuredMentors" :key="mentor.id" class="mentor-card-simple">
              <div class="face-container">
                <img :src="`http://localhost:8000/storage/${mentor.profile_photo_path}`" 
                     :alt="mentor.user.name" class="mentor-face" />
              </div>
              <div class="mentor-info">
                <h4>{{ mentor.user.name }}</h4>
                <p class="mentor-title">{{ mentor.subject_expertise }}</p>
                <p class="mentor-tenure">{{ mentor.university }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <RouterView v-else />
  </div>
</template>

<style scoped>

.page-wrapper {
  padding-top: 80px; 
  background-color: #fbfcff;
}

.home-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
}

.app-header {
  background: #1a1f3c; 
  color: white;
  padding: 1rem 5%;
  position: fixed; 
  top: 0;
  width: 100%;
  z-index: 2000; 
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.header-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1400px;
  margin: 0 auto;
}

.hero-section, 
.conversation-section, 
.team-showcase,
.trust-section {
  width: 100%;
  max-width: 1100px;
  padding: 4rem 20px;
  margin: 0 auto;
  text-align: center;
}

.welcome-title {
  font-family: 'Red Hat Display', sans-serif;
  font-size: 3rem;
  font-weight: 800;
  color: #1a1f3c;
  margin-bottom: 2rem;
}

.subhead {
  font-family: 'Red Hat Display', sans-serif;
  font-size: 2.2rem;
  font-weight: 800;
  color: #1a1f3c;
  margin-bottom: 1.5rem;
  line-height: 1.2;
}

.trust-section {
  background: #f8faff;
  border-radius: 20px;
  margin-bottom: 4rem;
}

.logo-grid {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 4rem;
  flex-wrap: wrap;
}

.trust-logo {
  height: 60px; 
  filter: grayscale(100%);
  opacity: 0.6;
  transition: all 0.3s ease;
}

.trust-logo:hover {
  filter: grayscale(0%);
  opacity: 1;
}

.conversation-section {
  background-color: #ffffff;
  border-top: 1px solid #DEE4FE;
  border-bottom: 1px solid #DEE4FE;
}

.qa-grid {
  display: flex; 
  flex-direction: column;
  gap: 3rem;
  max-width: 800px;
  margin: 3rem auto 0;
  text-align: left;
}

.mentor-faces-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 3rem;
  margin-top: 3rem;
}

.mentor-card-simple {
  background: white;
  padding: 2.5rem;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(26, 31, 60, 0.05);
  border: 1px solid #f0f3ff;
}

.face-container {
  width: 130px;
  height: 130px;
  margin: 0 auto 1.5rem;
  border-radius: 50%;
  overflow: hidden;
  border: 4px solid #DEE4FE; 
}

.btn-primary {
  background-color: #3e55f8;
  color: white;
  padding: 1.2rem 3rem;
  border-radius: 50px;
  font-family: 'Red Hat Display', sans-serif;
  font-weight: 800;
  text-decoration: none;
  box-shadow: 0 10px 20px rgba(62, 85, 248, 0.2);
  display: inline-block;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-3px);
  background-color: #1a1f3c;
}

.video-container {
  width: auto;
  max-width: 650px;
  margin: 0 auto 3rem;
  aspect-ratio: 16 / 9;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 20px 50px rgba(26, 31, 60, 0.2);
}
</style>