<template>
    <section class="panda-den" aria-labelledby="den-title">
        <header class="den-header">
            <div>
                <span class="den-kicker">The extremely unofficial trash panda department</span>
                <h1 id="den-title">Find the hidden pandas.</h1>
                <p>There are {{ eggs.length }} of them. They are small, sneaky, and absolutely not doing their jobs.</p>
            </div>
            <button class="achievement-toggle" type="button" @click="showAchievements = !showAchievements">
                <span aria-hidden="true">✦</span>
                {{ found.length }}/{{ eggs.length }} found
            </button>
        </header>

        <div class="den-stage" @click.self="discover('hello-world')">
            <div class="panda-scene" aria-hidden="true">
                <span class="trash-panda-ear left"></span>
                <span class="trash-panda-ear right"></span>
                <span class="trash-panda-mask"><span class="trash-panda-eye"></span><span class="trash-panda-eye"></span></span>
                <span class="trash-panda-nose"></span>
                <span class="panda-belly">🍕</span>
            </div>
            <span class="stage-note">The den is watching.</span>
            <button
                v-for="egg in eggs"
                :key="egg.key"
                class="easter-egg"
                :class="[`egg-${egg.key}`, { found: found.includes(egg.key) }]"
                type="button"
                :aria-label="found.includes(egg.key) ? `${egg.name} found` : `Inspect suspicious spot ${egg.number}`"
                @click="discover(egg.key)"
            >
                <span aria-hidden="true">{{ found.includes(egg.key) ? egg.icon : '?' }}</span>
            </button>
        </div>

        <Transition name="drawer">
            <aside v-if="showAchievements" class="achievement-drawer" aria-label="Trash panda achievements">
                <div class="drawer-heading">
                    <div><span class="den-kicker">Field notes</span><h2>Achievement junk drawer</h2></div>
                    <button class="close-drawer" type="button" aria-label="Close achievements" @click="showAchievements = false">×</button>
                </div>
                <div class="achievement-list">
                    <article v-for="egg in eggs" :key="`achievement-${egg.key}`" class="achievement" :class="{ found: found.includes(egg.key) }">
                        <span class="achievement-icon" aria-hidden="true">{{ found.includes(egg.key) ? egg.icon : '·' }}</span>
                        <span><strong>{{ found.includes(egg.key) ? egg.name : '???' }}</strong><small>{{ found.includes(egg.key) ? egg.description : 'Find this panda to identify it.' }}</small></span>
                    </article>
                </div>
            </aside>
        </Transition>
        <p v-if="toast" class="discovery-toast" role="status">{{ toast }}</p>
    </section>
</template>

<script>
const eggList = [
    ['hello-world', 'Hello, trash', 'A polite little menace.', '👋'], ['trash-taste', 'Trash taste', 'Has opinions about leftovers.', '🍕'],
    ['three-clicks', 'Three clicks deep', 'The commitment is admirable.', '🖱️'], ['night-shift', 'Night shift', 'Still awake. Still stealing.', '🌙'],
    ['tiny-bin', 'Tiny bin', 'Small container, huge ambition.', '🗑️'], ['corner-creep', 'Corner creep', 'Seen lurking in the margins.', '👀'],
    ['scroll-scout', 'Scroll scout', 'Went all the way down.', '↓'], ['keyboard-bandit', 'Keyboard bandit', 'Definitely not your shortcut.', '⌨️'],
    ['footer-foodie', 'Footer foodie', 'Found crumbs in the footer.', '🍪'], ['panda-paparazzi', 'Panda paparazzi', 'Caught in the act.', '📸'],
    ['sneaky-swipe', 'Sneaky swipe', 'A swipe-by discovery.', '✋'], ['moonwalk', 'Moonwalk', 'Danced past the obvious.', '🌘'],
    ['lucky-seven', 'Lucky seven', 'A suspiciously lucky spot.', '⑦'], ['recycle-raccoon', 'Recycle raccoon', 'Sorting the good stuff.', '♻️'],
    ['deep-dive', 'Deep dive', 'No surface left unchecked.', '🤿'], ['trash-treasure', 'Trash treasure', 'One panda’s junk is another panda’s prize.', '💎'],
    ['full-den', 'Full den', 'Every corner is accounted for.', '🏠'], ['panda-pro', 'Panda pro', 'The den salutes you.', '🏆'],
];

export default {
    data() {
        return {
            eggs: eggList.map(([key, name, description, icon], index) => ({ key, name, description, icon, number: index + 1 })),
            found: [], showAchievements: false, toast: '', toastTimer: null,
        };
    },
    mounted() { this.loadAchievements(); },
    beforeUnmount() { clearTimeout(this.toastTimer); },
    methods: {
        async loadAchievements() {
            const saved = window.localStorage.getItem('trash-panda-achievements');
            if (saved) {
                try { this.found = this.validKeys(JSON.parse(saved)); } catch (error) { window.localStorage.removeItem('trash-panda-achievements'); }
            }
            try {
                const response = await fetch('/user/achievements', { credentials: 'same-origin', headers: { Accept: 'application/json' } });
                if (response.ok) { this.found = this.validKeys((await response.json()).achievements); this.saveLocal(); }
            } catch (error) { /* Guests keep discoveries locally. */ }
        },
        validKeys(keys) { return [...new Set((Array.isArray(keys) ? keys : []).filter((key) => this.eggs.some((egg) => egg.key === key)))]; },
        saveLocal() { window.localStorage.setItem('trash-panda-achievements', JSON.stringify(this.found)); },
        async discover(key) {
            if (this.found.includes(key)) { this.showAchievements = true; return; }
            this.found.push(key); this.saveLocal();
            const egg = this.eggs.find((item) => item.key === key);
            this.toast = `${egg.icon} ${egg.name} unlocked`;
            clearTimeout(this.toastTimer); this.toastTimer = setTimeout(() => { this.toast = ''; }, 2600);
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                const response = await fetch('/user/achievements', { method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ key }) });
                if (response.ok) { this.found = this.validKeys((await response.json()).achievements); this.saveLocal(); }
            } catch (error) { /* Guest progress remains available locally. */ }
        },
    },
};
</script>

<style scoped>
.panda-den { position: relative; min-height: 100vh; padding: 3rem clamp(1rem, 4vw, 4rem) 5rem; overflow: hidden; background: radial-gradient(circle at 50% 10%, rgb(255 255 255 / 70%), transparent 32rem), var(--theme-background); }
.den-header { display: flex; justify-content: space-between; gap: 2rem; max-width: 72rem; margin: 0 auto 2rem; }
.den-kicker { color: var(--theme-accent); font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.den-header h1 { max-width: 35rem; margin: .45rem 0 .7rem; font-size: clamp(2.7rem, 7vw, 6.5rem); line-height: .88; }
.den-header p { max-width: 31rem; margin: 0; color: var(--theme-accent); font-size: 1.1rem; }
.achievement-toggle, .close-drawer { border: 1px solid var(--theme-accent); background: var(--theme-surface); color: var(--theme-foreground); cursor: pointer; font: inherit; }
.achievement-toggle { align-self: flex-start; padding: .7rem 1rem; border-radius: 999px; white-space: nowrap; }
.den-stage { position: relative; max-width: 72rem; min-height: 29rem; margin: 0 auto; border: 1px solid var(--theme-border); background: linear-gradient(145deg, rgb(255 255 255 / 45%), transparent), var(--theme-surface); box-shadow: 1rem 1rem 0 var(--theme-border); }
.panda-scene { position: absolute; top: 50%; left: 50%; width: 14rem; height: 12rem; transform: translate(-50%, -42%); border-radius: 47% 47% 42% 42%; background: var(--panda-fur, #9ca8a2); box-shadow: inset 0 -1rem 0 rgb(0 0 0 / 8%); }
.trash-panda-ear { position: absolute; top: -1.25rem; width: 4.2rem; height: 4.2rem; border: 1rem solid var(--panda-fur-dark, #4c5953); border-radius: 50%; background: var(--panda-fur-light, #cbd3d0); }
.trash-panda-ear.left { left: -.2rem; } .trash-panda-ear.right { right: -.2rem; }
.trash-panda-mask { position: absolute; top: 3.1rem; left: 1rem; display: flex; justify-content: space-around; width: 12rem; padding: 1rem .8rem; border-radius: 50%; background: var(--panda-mask, #4c5953); }
.trash-panda-eye { width: 2rem; height: 2rem; border: .4rem solid var(--panda-eye-ring, #202923); border-radius: 50%; background: var(--panda-eye, #f5c66b); box-shadow: inset 0 0 0 .35rem var(--panda-eye-glint, #fff7d1); }
.trash-panda-nose { position: absolute; bottom: 2rem; left: 5.8rem; width: 2.2rem; height: 1.3rem; border-radius: 50%; background: var(--panda-nose, #202923); }
.panda-belly { position: absolute; right: 1rem; bottom: 1rem; font-size: 2rem; transform: rotate(12deg); } .stage-note { position: absolute; right: 1.2rem; bottom: 1rem; color: var(--theme-accent); font-size: .75rem; }
.easter-egg { position: absolute; width: 2.1rem; height: 2.1rem; padding: 0; border: 0; border-radius: 50%; background: transparent; color: var(--theme-accent); cursor: pointer; font-size: 1rem; opacity: .18; transition: opacity 160ms ease, transform 160ms ease; }
.easter-egg:hover, .easter-egg:focus-visible, .easter-egg.found { opacity: 1; transform: scale(1.2); outline: 2px solid var(--theme-accent); outline-offset: 3px; }
.egg-hello-world { top: 8%; left: 7%; } .egg-trash-taste { top: 18%; right: 12%; } .egg-three-clicks { top: 42%; left: 4%; } .egg-night-shift { top: 7%; right: 39%; } .egg-tiny-bin { bottom: 13%; left: 13%; } .egg-corner-creep { top: 4%; right: 3%; } .egg-scroll-scout { bottom: 5%; left: 50%; } .egg-keyboard-bandit { bottom: 17%; right: 8%; } .egg-footer-foodie { bottom: 5%; right: 27%; } .egg-panda-paparazzi { top: 34%; right: 5%; } .egg-sneaky-swipe { top: 67%; left: 7%; } .egg-moonwalk { top: 16%; left: 29%; } .egg-lucky-seven { top: 55%; right: 21%; } .egg-recycle-raccoon { bottom: 25%; left: 28%; } .egg-deep-dive { top: 75%; right: 35%; } .egg-trash-treasure { top: 29%; left: 18%; } .egg-full-den { bottom: 8%; right: 4%; } .egg-panda-pro { top: 5%; left: 48%; }
.achievement-drawer { position: fixed; z-index: 2; top: 1rem; right: 1rem; bottom: 1rem; width: min(27rem, calc(100vw - 2rem)); padding: 1.4rem; overflow-y: auto; border: 1px solid var(--theme-border); background: var(--theme-surface); box-shadow: -.8rem .8rem 0 var(--theme-accent); }
.drawer-heading { display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; } .drawer-heading h2 { margin: .25rem 0 0; font-size: 1.5rem; }
.close-drawer { width: 2rem; height: 2rem; border-radius: 50%; font-size: 1.4rem; line-height: 1; } .achievement-list { display: grid; gap: .45rem; }
.achievement { display: flex; align-items: center; gap: .7rem; min-height: 3.1rem; padding: .55rem; opacity: .45; border-bottom: 1px solid var(--theme-border); } .achievement.found { opacity: 1; }
.achievement-icon { display: grid; place-items: center; width: 2rem; height: 2rem; background: var(--theme-background); font-size: 1.2rem; } .achievement strong, .achievement small { display: block; } .achievement small { margin-top: .15rem; color: var(--theme-accent); }
.discovery-toast { position: fixed; z-index: 3; right: 1.5rem; bottom: 1.5rem; margin: 0; padding: .8rem 1rem; background: var(--theme-foreground); color: var(--theme-background); box-shadow: .35rem .35rem 0 var(--theme-accent); } .drawer-enter-active, .drawer-leave-active { transition: transform 180ms ease; } .drawer-enter-from, .drawer-leave-to { transform: translateX(110%); }
@media (max-width: 42rem) { .panda-den { padding-top: 2rem; } .den-header { display: block; } .achievement-toggle { margin-top: 1.2rem; } .den-stage { min-height: 25rem; box-shadow: .5rem .5rem 0 var(--theme-border); } .panda-scene { transform: translate(-50%, -42%) scale(.72); } .stage-note { display: none; } }
</style>
