<template>
    <section class="panel about-panel">
        <div class="panel-heading"><div><span class="card-label">Profile studio</span><h2>About me & resume</h2></div><button class="text-button" @click="saveProfile">Save profile ↗</button></div>
        <div class="profile-grid"><label>Display name<input v-model="profile.name" type="text"></label><label>Role<input v-model="profile.role" type="text"></label><label>Location<input v-model="profile.location" type="text"></label><label>Website<input v-model="profile.website" type="url"></label></div>
        <label class="profile-bio">Bio<textarea v-model="profile.bio" rows="4"></textarea></label>
        <div class="resume-list"><div class="panel-heading"><div><span class="card-label">Selected work</span><h2>Resume highlights</h2></div><button class="text-button" @click="addHighlight">Add highlight +</button></div><div v-for="(highlight, index) in profile.highlights" :key="index" class="management-row"><input v-model="profile.highlights[index]" type="text"><button class="dots-button" title="Remove highlight" aria-label="Remove highlight" @click="profile.highlights.splice(index, 1)">×</button></div></div><p v-if="saved" class="save-note">Profile saved locally for this demo.</p>
    </section>
</template>
<script>
export default {
    name: 'AboutPanel',
    data() {
        return { saved: false, profile: JSON.parse(window.localStorage.getItem('profile') || 'null') || { name: '', role: '', location: '', website: '', bio: '', highlights: ['Built resilient Laravel systems', 'Designed realtime operations tooling'] } };
    },
    methods: {
        addHighlight() { this.profile.highlights.push('New project highlight'); },
        saveProfile() { window.localStorage.setItem('profile', JSON.stringify(this.profile)); this.saved = true; window.setTimeout(() => { this.saved = false; }, 1800); },
    },
};
</script>
