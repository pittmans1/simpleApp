<template>
    <section class="admin-screen"><div class="screen-heading"><div><span class="card-label">Workspace administration</span><h2>Control room</h2><p>Manage members, review system interaction, and run approved diagnostics.</p></div><span class="admin-badge">{{ users.length }} members</span></div><div class="admin-grid"><section class="panel"><div class="panel-heading"><div><span class="card-label">People</span><h2>Tenant members</h2></div><button class="text-button" type="button" @click="loadUsers">Refresh ↗</button></div><div v-if="loading" class="loading-state">Loading members...</div><div v-for="user in users" v-else :key="user.id" class="management-row"><span><strong>{{ user.name }}</strong><small>{{ user.email }}</small></span><select :value="user.pivot?.role" @change="updateRole(user, $event.target.value)"><option value="member">Member</option><option value="admin">Admin</option><option value="owner">Owner</option></select></div></section><section class="panel"><div class="panel-heading"><div><span class="card-label">Diagnostics</span><h2>Approved commands</h2></div></div><p class="admin-copy">These commands are intentionally limited to read-only application diagnostics.</p><div class="command-list"><button v-for="command in commands" :key="command" type="button" @click="runCommand(command)">{{ command }} <span>↗</span></button></div><pre v-if="output">{{ output }}</pre></section></div></section>
</template>
<script>
export default {
    name: 'AdminPanel',
    props: { tenantId: { type: [String, Number], required: true } },
    data() { return { users: [], loading: false, output: '', commands: ['about', 'route:list', 'queue:monitor'] }; },
    mounted() { this.loadUsers(); },
    methods: {
        async loadUsers() {
            this.loading = true;
            try { const response = await fetch(`/tenants/${this.tenantId}/admin/users`, { headers: { Accept: 'application/json' } }); this.users = (await response.json()).data || []; } finally { this.loading = false; }
        },
        async updateRole(user, role) {
            await fetch(`/tenants/${this.tenantId}/admin/users/${user.id}`, { method: 'PATCH', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }, body: JSON.stringify({ role }) });
            user.pivot = { ...(user.pivot || {}), role };
        },
        async runCommand(command) {
            const response = await fetch(`/tenants/${this.tenantId}/admin/commands`, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }, body: JSON.stringify({ command }) });
            const data = await response.json();
            this.output = data.output || `Exit code: ${data.exit_code}`;
        },
    },
};
</script>
