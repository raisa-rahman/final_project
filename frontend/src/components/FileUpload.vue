<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';

/**
 * FileUpload component for handling profile imagery and verification documents.
 */
const props = defineProps<{
  uploadType: 'photo' | 'document';
  profileId: number;
}>();

// Emit an event to notify the parent component when the upload is complete
const emit = defineEmits(['upload-success']);

// Reactive state to hold the chosen file and track the request status
const selectedFile = ref<File | null>(null);
const uploading = ref(false);

/**
 * Handle the file input change event.
 * * Stores the first selected file into our reactive variable.
 */
const onFileSelect = (event: any) => {
  selectedFile.value = event.target.files[0];
};

/**
 * Perform the asynchronous upload to the backend.
 * * Uses FormData to package the binary file and metadata.
 */
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
        
        alert(`${props.uploadType === 'photo' ? 'Profile picture' : 'Verification document'} uploaded successfully!`); 
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