import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import MentorDirectory from '../views/MentorDirectory.vue'
// Import the new views you'll create next
import MentorProfile from '../views/MentorProfile.vue'
import BookingCalendar from '../views/BookingCalendar.vue'
import Onboarding from '../views/Onboarding.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { 
      path: '/', 
      name: 'home',
      component: HomeView 
    },
    { 
      path: '/mentors', 
      name: 'directory',
      component: MentorDirectory 
    },
    { 
      // Dynamic route to show a specific mentor
      path: '/mentors/:id', 
      name: 'mentor-profile',
      component: MentorProfile 
    },
    { 
      path: '/book/:id', 
      name: 'booking',
      component: BookingCalendar 
    },
    { 
      path: '/join', 
      name: 'onboarding',
      component: Onboarding 
    },
    {
      path:'/login',
      name: 'login',
      component: () => import('../views/Login.vue')
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../views/Dashboard.vue'),
      beforeEnter: (to, from, next) => {
        next();
      }
    },
  ]
})

export default router