<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps<{
  uploadType: 'photo' | 'document';
  profileId: number;
}>();

const emit = defineEmits(['upload-success']);
const selectedFile = ref<File | null>(null);
const uploading = ref(false);

const onFileSelect = (event: any) => {
  selectedFile.value = event.target.files[0];
};

const uploadFile = async () => {
    if (!selectedFile.value) return;
    uploading.value = true;
    
    const formData = new FormData();
    formData.append('file', selectedFile.value);
    formData.append('type', props.uploadType);
    
    try {
        await axios.post(`http://localhost:8000/api/mentors/${props.profileId}/upload`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        emit('upload-success');
        selectedFile.value = null; 
    } catch (err) {
        console.error("Upload failed", err);
        alert("Upload failed. Ensure the file is a valid image or PDF.");
    } finally {
        uploading.value = false;
    }
};
</script>

<template>
  <div class="upload-container">
    <input 
      type="file" 
      @change="onFileSelect" 
      class="file-input"
      :accept="uploadType === 'photo' ? 'image/*' : '.pdf,image/*'"
    />
    
    <button 
      @click="uploadFile" 
      :disabled="!selectedFile || uploading" 
      class="upload-btn"
    >
      {{ uploading ? 'Uploading...' : 'Click to Upload' }}
    </button>
  </div>
</template>

<style scoped>
.upload-container { margin-top: 10px; }
.file-input { display: block; margin-bottom: 8px; font-size: 0.8rem; }
.upload-btn {
  background: #3e55f8;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  width: 100%;
}
.upload-btn:disabled { background: #ccc; cursor: not-allowed; }
</style>