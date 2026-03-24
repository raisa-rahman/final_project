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
    const response = await axios.get('http://localhost:8000/api/mentors/directory');
    const rawData = Array.isArray(response.data) ? response.data : (response.data.mentors || []);
    featuredMentors.value = rawData.filter((m: any) => Number(m.is_verified) === 1);
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
  <div class="logo-marquee-container">
    <div class="logo-marquee-scroll">
      <div class="logo-grid">
        <img src="@/assets/kcl.png" alt="Kings College London" class="trust-logo" />
        <img src="@/assets/city.png" alt="City University of London" class="trust-logo" />
        <img src="@/assets/nott.png" alt="University of Nottingham" class="trust-logo" />
        <img src="@/assets/arden.png" alt="Arden University" class="trust-logo" />
        <img src="@/assets/hert.jpg" alt="University of Hertfordshire" class="trust-logo" />
        <img src="@/assets/bourne.png" alt="Bournemouth University" class="trust-logo" />
        <img src="@/assets/lse.png" alt="London School of Economics" class="trust-logo" />
      </div>
      <div class="logo-grid" aria-hidden="true">
        <img src="@/assets/kcl.png" alt="Kings College London" class="trust-logo" />
        <img src="@/assets/city.png" alt="City University of London" class="trust-logo" />
        <img src="@/assets/nott.png" alt="University of Nottingham" class="trust-logo" />
        <img src="@/assets/arden.png" alt="Arden University" class="trust-logo" />
        <img src="@/assets/hert.jpg" alt="University of Hertfordshire" class="trust-logo" />
        <img src="@/assets/bourne.png" alt="Bournemouth University" class="trust-logo" />
        <img src="@/assets/lse.png" alt="London School of Economics" class="trust-logo" />
      </div>
    </div>
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
              <p>Every mentor is manually verified. We prioritise professionals who were once international students themselves. You're getting a roadmap for the UK job market.</p>
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
          
          <div class="mentor-scroll-wrapper">
            <div class="mentor-faces-grid-horizontal">
              <div v-for="mentor in featuredMentors" :key="mentor.id" class="mentor-card-simple">
                <div class="face-container">
                  <img :src="mentor.profile_photo_path ? `http://localhost:8000/storage/${mentor.profile_photo_path}` : 'https://via.placeholder.com/150'" 
                      :alt="mentor.user?.name" class="mentor-face" />
                </div>
                <div class="mentor-info">
                  <h4>{{ mentor.user?.name }}</h4>
                  <p class="mentor-title">{{ mentor.subject_expertise }}</p>
                  <p class="mentor-tenure">{{ mentor.university?.name || mentor.university || 'Verified Mentor' }}</p>
                </div>
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

.cta-container {
  padding: 5rem 2rem;
  background: linear-gradient(135deg, #fbfcff 0%, #f0f4ff 100%);
  border-radius: 24px;
  margin: 4rem auto;
  max-width: 800px;
  text-align: center;
  border: 1px solid #dee4fe;
  box-shadow: 0 10px 30px rgba(26, 31, 60, 0.03);
}

.subhead-small {
  font-family: 'Red Hat Display', sans-serif;
  font-size: 2rem;
  font-weight: 800;
  color: #1a1f3c;
  margin-bottom: 2.5rem;
  line-height: 1.2;
}

.btn-primary {
  background-color: #3e55f8;
  color: white;
  padding: 1.2rem 3.5rem;
  border-radius: 50px;
  font-family: 'Red Hat Display', sans-serif;
  font-weight: 800;
  font-size: 1.1rem;
  text-decoration: none;
  display: inline-block;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 12px 24px rgba(62, 85, 248, 0.25);
  margin-bottom: 2rem;
}

/* Hover effects act as signifiers of interactivity [cite: 5966] */
.btn-primary:hover {
  transform: translateY(-4px);
  background-color: #1a1f3c;
  box-shadow: 0 15px 30px rgba(26, 31, 60, 0.3);
}

.btn-primary:active {
  transform: translateY(-1px);
}

.cta-subtext {
  font-family: 'SF-UI Text', sans-serif;
  font-size: 1rem;
  color: #6b7280;
  margin-top: 1rem;
}

.cta-link {
  color: #2de1aa; /* Using your verified brand green */
  font-weight: 700;
  text-decoration: none;
  border-bottom: 2px solid transparent;
  transition: all 0.2s ease;
  margin-left: 0.5rem;
}

.cta-link:hover {
  border-bottom-color: #2de1aa;
  color: #065f46;
}

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
.team-showcase{
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
  padding: 3rem 0;
  margin-bottom: 4rem;
  width: 100vw; 
  position: relative;
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
  overflow: hidden;
}

.trust-label {
  text-align: center;
  margin-bottom: 2rem;
  letter-spacing: 0.1em;
  font-weight: 700;
}

.logo-marquee-container {
  display: flex;
  overflow: hidden;
  user-select: none;
}

.logo-marquee-scroll {
  display: flex;
  flex-shrink: 0;
  min-width: 100%;
  gap: 4rem;
  animation: scroll-left 30s linear infinite; 
}

.logo-marquee-container:hover .logo-marquee-scroll {
  animation-play-state: paused;
}

.logo-grid {
  display: flex;
  justify-content: space-around;
  align-items: center;
  min-width: 100%;
  gap: 4rem;
}

.trust-logo {
  height: 80px;
  width: auto;
  opacity: 1;
  transition: all 0.2s ease;
  object-fit: contain;
}

@keyframes scroll-left {
  from {
    transform: translateX(0);
  }
  to {
    transform: translateX(-100%);
  }
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

.mentor-scroll-wrapper {
  width: 100%;
  overflow-x: auto; 
  padding: 2rem 0;
  scrollbar-width: thin; 
  scrollbar-color: #3e55f8 #f0f3ff;
}

.mentor-scroll-wrapper::-webkit-scrollbar {
  height: 8px;
}
.mentor-scroll-wrapper::-webkit-scrollbar-track {
  background: #f0f3ff;
  border-radius: 10px;
}
.mentor-scroll-wrapper::-webkit-scrollbar-thumb {
  background: #3e55f8;
  border-radius: 10px;
}

.mentor-faces-grid-horizontal {
  display: flex; 
  gap: 2rem;
  padding: 0 1rem;
  flex-wrap: nowrap; 
  justify-content: v-bind("featuredMentors.length > 3 ? 'flex-start' : 'center'");
}

.mentor-card-simple {
  background: white;
  padding: 2rem;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(26, 31, 60, 0.05);
  border: 1px solid #f0f3ff;
  flex: 0 0 280px; 
  transition: transform 0.3s ease;
}

.mentor-card-simple:hover {
  transform: translateY(-10px);
}

.mentor-face {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.mentor-tenure {
  font-size: 0.85rem;
  color: #6b7280;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
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
  width: 100%;
  max-width: 650px;
  margin: 0 auto 3rem;
  aspect-ratio: 16 / 9;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 20px 50px rgba(26, 31, 60, 0.2);
  background: #000;
  display: flex;
  justify-content: center;
  align-items: center;
}

.hero-video {
  width: 100%;
  height: 100%;
  display: block;
  border: none;
}
</style>