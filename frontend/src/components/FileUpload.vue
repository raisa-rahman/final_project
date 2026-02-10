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
    
    formData.append(props.uploadType === 'photo' ? 'profile_photo' : 'document', selectedFile.value);
    formData.append('profile_id', props.profileId.toString());
    
    try {
        await axios.post('http://localhost:8000/api/mentor/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'Accept': 'application/json' 
            }
        });
        emit('upload-success');
        selectedFile.value = null; 
    } catch (err: any) {
        console.error("Upload failed", err.response?.data);
        alert("Upload failed. Ensure the file is an image (jpg/png) and under 2MB.");
    } finally {
        uploading.value = false;
    }
};
</script>

<template>
  <div class="upload-box">
    <label class="upload-label">
      {{ uploadType === 'photo' ? 'Profile Picture' : 'Upload ID Document' }}
    </label>
    <input 
      type="file" 
      @change="onFileSelect" 
      class="file-input" 
      :accept="uploadType === 'photo' ? 'image/jpeg,image/png' : 'application/pdf,image/jpeg,image/png'"
    />
    <button @click="uploadFile" :disabled="!selectedFile || uploading" class="upload-btn">
      {{ uploading ? 'Uploading...' : 'Click to Upload' }}
    </button>
  </div>
</template>

<style scoped>
.upload-box { margin-top: 1rem; padding: 15px; border: 2px dashed #DEE4FE; border-radius: 8px; background: #f9faff; }
.upload-label { display: block; font-weight: 600; margin-bottom: 8px; color: #1a1f3c; }
.file-input { display: block; width: 100%; margin-bottom: 10px; }
.upload-btn { background: #3e55f8; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; width: 100%; font-weight: 700; }
.upload-btn:disabled { background: #ccc; cursor: not-allowed; }
</style>