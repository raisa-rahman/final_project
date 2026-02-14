import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/HomeView.vue';
import MentorDirectory from '../views/MentorDirectory.vue';
import StudentOnboarding from '../views/StudentOnboarding.vue';
import MentorOnboarding from '../views/MentorOnboarding.vue';

const guardByRole = (role: string, redirectPath: string) => (to: any, from: any, next: any) => {
  const userData = localStorage.getItem('user');
  const user = userData ? JSON.parse(userData) : null;

  if (user && user.role === role) {
    next();
  } else {
    if (role === 'admin') alert('Access Denied: Admins Only');
    next(redirectPath);
  }
};

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'home', component: HomeView },
    { path: '/find-a-mentor', name: 'directory', component: MentorDirectory },

    { path: '/student/login', component: StudentOnboarding, props: { initialMode: 'login' } },
    { path: '/student/signup', component: StudentOnboarding, props: { initialMode: 'signup' } },
    { 
      path: '/student/login/dashboard', 
      component: () => import('../views/StudentDashboard.vue'),
      beforeEnter: guardByRole('student', '/student/login')
    },

    { path: '/mentor/login', component: MentorOnboarding, props: { initialMode: 'login' } },
    { path: '/mentor/signup', component: MentorOnboarding, props: { initialMode: 'signup' } },
    { 
      path: '/mentor/login/dashboard', 
      component: () => import('../views/MentorDashboard.vue'),
      beforeEnter: guardByRole('mentor', '/mentor/login')
    },

    { path: '/staff-portal', name: 'AdminLogin', component: () => import('../views/AdminLogin.vue') },
    { 
      path: '/staff-portal/dashboard', 
      name: 'AdminDashboard', 
      component: () => import('../views/AdminDashboard.vue'),
      beforeEnter: guardByRole('admin', '/staff-portal')
    },
    {
      path: '/mentor/profile/:id',
      name: 'MentorProfile',
      component: () => import('../views/IndividualProfile.vue'),
      beforeEnter: guardByRole('student', '/student/login') 
    }
  ]
});

export default router;