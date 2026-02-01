<template>
  <div class="onboarding-container" style="max-width: 600px; margin: 2rem auto; font-family: sans-serif;">
    <h1>Join as a Mentor</h1>
    <p>Help prospective international students navigate their journey.</p>
    
    <form @submit.prevent style="display: flex; flex-direction: column; gap: 1rem; border: 1px solid #eee; padding: 2rem; border-radius: 8px;">
      <label>
        Full Name:
        <input type="text" placeholder="e.g. Jane Doe" style="width: 100%; padding: 8px; margin-top: 5px;">
      </label>

      <label>
        University:
        <input type="text" placeholder="e.g. University of Manchester" style="width: 100%; padding: 8px; margin-top: 5px;">
      </label>

      <label>
        LinkedIn Profile (for Verification):
        <input type="url" placeholder="https://linkedin.com/in/..." style="width: 100%; padding: 8px; margin-top: 5px;">
      </label>

      <label>
        Bio:
        <textarea placeholder="Tell us about your experience..." style="width: 100%; padding: 8px; margin-top: 5px; height: 100px;"></textarea>
      </label>

      <button type="submit" style="background: #007bff; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer;">
        Submit for Verification
      </button>
    </form>
  </div>
</template>