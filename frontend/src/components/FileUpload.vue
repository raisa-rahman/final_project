<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps<{
  uploadType: 'photo' | 'document';
  profileId: number;
}>();

const selectedFile = ref<File | null>(null);
const uploading = ref(false);

const onFileSelect = (event: any) => {
  selectedFile.value = event.target.files[0];
};

const uploadFile = async () => {
  if (!selectedFile.value) return;
  
  const formData = new FormData();
  formData.append('file', selectedFile.value);
  formData.append('type', props.uploadType);

  uploading.value = true;
  try {
    // Send to a new endpoint we will create in api.php
    await axios.post(`/api/mentors/${props.profileId}/upload`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    alert(`${props.uploadType} uploaded successfully!`);
  } catch (error) {
    alert('Upload failed.');
  } finally {
    uploading.value = false;
  }
};
</script>

<template>
  <div class="upload-box">
    <label>{{ uploadType === 'photo' ? 'Profile Picture' : 'University ID (PDF/JPG)' }}</label>
    <input type="file" @change="onFileSelect" :accept="uploadType === 'photo' ? 'image/*' : '.pdf,image/*'" />
    <button @click="uploadFile" :disabled="!selectedFile || uploading">
      {{ uploading ? 'Uploading...' : 'Upload' }}
    </button>
  </div>
</template>

<style scoped>
.upload-box { margin-top: 1rem; padding: 15px; border: 1px dashed #DEE4FE; border-radius: 8px; }
button { margin-top: 10px; background: #3e55f8; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; }
button:disabled { background: #ccc; }
</style>