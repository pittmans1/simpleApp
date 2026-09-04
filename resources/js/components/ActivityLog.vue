<template>
    <section class="panel activity-log"><div class="panel-heading"><div><span class="card-label">{{ eyebrow }}</span><h2>{{ title }}</h2></div><button class="text-button" type="button" @click="$emit('refresh')">Refresh ↗</button></div><div v-if="loading" class="loading-state">Loading activity...</div><div v-else-if="!items.length" class="empty-state">Nothing to report yet.</div><div v-for="item in items" v-else :key="item.id" class="activity-row"><span class="activity-icon">{{ icon }}</span><span><strong>{{ item[eventField] || item.title || 'Workspace update' }}</strong><small>{{ item.created_at }}</small></span></div></section>
</template>
<script>
export default {
    name: 'ActivityLog',
    emits: ['refresh'],
    props: { title: { type: String, default: 'Activity log' }, eyebrow: { type: String, default: 'Event stream' }, items: { type: Array, default: () => [] }, loading: { type: Boolean, default: false }, eventField: { type: String, default: 'event' }, icon: { type: String, default: '↗' } },
};
</script>
