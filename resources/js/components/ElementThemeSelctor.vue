<template>
    <section class="theme-selector" aria-label="Theme selector">
        <span class="theme-label">Theme</span>
        <div v-if="secretUnlocked" class="theme-triangle" role="group" aria-label="Theme choices">
            <button
                v-for="option in themes"
                :key="option"
                class="theme-option"
                :class="[option, { active: theme === option }]"
                type="button"
                @click="setTheme(option)"
            >
                <span class="theme-icon" aria-hidden="true">{{ themeIcon(option) }}</span>
                <span>{{ optionLabel(option) }}</span>
            </button>
        </div>
        <button
            v-else
            class="theme-slider"
            :class="{ dark: theme === 'dark' }"
            type="button"
            role="switch"
            :aria-checked="theme === 'dark'"
            @click="toggleTheme"
        >
            <span class="theme-switch-label">☀</span>
            <span class="theme-track">
                <span class="theme-thumb"></span>
            </span>
            <span class="theme-switch-label">☾</span>
        </button>
    </section>
</template>
<script>
export default {
    data() {
        return {
            theme: 'light',
            themes: ['light', 'dark', 'trashpanda'],
            secretUnlocked: false,
            toggleCount: 0,
            toggleReset: null,
        };
    },
    mounted() {
        this.applySystemTheme();
        this.loadSavedTheme();
    },
    beforeUnmount() {
        clearTimeout(this.toggleReset);
    },
    methods: {
        optionLabel(theme) {
            return theme === 'trashpanda' ? 'Trash Panda' : theme[0].toUpperCase() + theme.slice(1);
        },
        themeIcon(theme) {
            return { light: '☀', dark: '☾', trashpanda: '🦝' }[theme];
        },
        toggleTheme() {
            this.toggleCount += 1;
            clearTimeout(this.toggleReset);
            this.toggleReset = setTimeout(() => {
                this.toggleCount = 0;
            }, 1200);

            if (this.toggleCount >= 3) {
                this.secretUnlocked = true;
                this.setTheme('trashpanda');
                return;
            }

            this.setTheme(this.theme === 'light' ? 'dark' : 'light');
        },
        applySystemTheme() {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.applyTheme(prefersDark ? 'dark' : 'light');
        },
        applyTheme(theme) {
            this.theme = theme;
            document.documentElement.dataset.theme = theme;
        },
        async loadSavedTheme() {
            const localTheme = window.localStorage.getItem('theme');

            if (this.themes.includes(localTheme)) {
                this.applyTheme(localTheme);
            }

            try {
                const response = await fetch('/user/theme', {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                if (this.themes.includes(data.theme)) {
                    this.applyTheme(data.theme);
                    window.localStorage.setItem('theme', data.theme);
                }
            } catch (error) {
                // Guests use local storage or the system preference.
            }
        },
        async setTheme(theme) {
            this.applyTheme(theme);
            window.localStorage.setItem('theme', theme);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            try {
                await fetch('/user/theme', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ theme }),
                });
            } catch (error) {
                // The local preference remains available if the user is logged out.
            }
        }
    }
};
</script>
<style scoped>
.theme-selector {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.theme-label {
    font-weight: 600;
}

.theme-slider,
.theme-option {
    border: 1px solid var(--theme-accent);
    color: var(--theme-foreground);
    background: transparent;
    cursor: pointer;
    font: inherit;
}

.theme-slider {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 8.25rem;
    padding: 0.3rem 0.55rem;
    border-radius: 999px;
}

.theme-switch-label {
    color: var(--theme-accent);
    font-size: 0.85rem;
    line-height: 1;
}

.theme-track {
    display: flex;
    align-items: center;
    width: 3.4rem;
    height: 1.45rem;
    padding: 0.15rem;
    border-radius: 999px;
    background: color-mix(in srgb, var(--theme-accent) 30%, var(--theme-background));
}

.theme-thumb {
    width: 1.15rem;
    height: 1.15rem;
    border-radius: 50%;
    background: var(--theme-background);
    box-shadow: 0 1px 3px rgb(0 0 0 / 25%);
    transition: transform 160ms ease;
}

.theme-slider.dark .theme-thumb {
    transform: translateX(1.95rem);
}

.theme-triangle {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    grid-template-rows: auto auto;
    gap: 0.35rem;
    width: 10rem;
}

.theme-option {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    min-width: 4.75rem;
    padding: 0.4rem 0.7rem;
    border-radius: 999px;
    transition: background-color 160ms ease, color 160ms ease, transform 160ms ease;
}

.theme-option.light {
    grid-column: 1;
    grid-row: 1;
}

.theme-option.dark {
    grid-column: 3;
    grid-row: 1;
}

.theme-option.trashpanda {
    grid-column: 2;
    grid-row: 2;
    min-width: 7rem;
}

.theme-icon {
    font-size: 1.1rem;
    line-height: 1;
}

.theme-option.active {
    background: var(--theme-accent);
    color: var(--theme-background);
}

.theme-option:hover {
    transform: translateY(-1px);
}
</style>