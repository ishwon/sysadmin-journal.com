<div x-data="{
    open: false,
    query: '',
    results: [],
    selected: -1,
    loading: false,
    debounceTimer: null,
    search() {
        clearTimeout(this.debounceTimer);
        if (this.query.length < 2) { this.results = []; return; }
        this.loading = true;
        this.debounceTimer = setTimeout(() => {
            fetch('/search?q=' + encodeURIComponent(this.query))
                .then(r => r.json())
                .then(data => { this.results = data; this.selected = -1; this.loading = false; })
                .catch(() => { this.loading = false; });
        }, 300);
    },
    navigate(e) {
        if (e.key === 'ArrowDown') { e.preventDefault(); this.selected = Math.min(this.selected + 1, this.results.length - 1); }
        if (e.key === 'ArrowUp') { e.preventDefault(); this.selected = Math.max(this.selected - 1, -1); }
        if (e.key === 'Enter' && this.selected >= 0) { window.location.href = '/' + this.results[this.selected].slug; }
    },
    close() { this.open = false; this.query = ''; this.results = []; }
}"
    x-on:open-search.window="open = true; $nextTick(() => $refs.searchInput.focus())"
    x-on:keydown.window="if (($event.metaKey || $event.ctrlKey) && $event.key === 'k') { $event.preventDefault(); $dispatch('open-search'); }"
    x-on:keydown.escape.window="close()"
    x-show="open" x-cloak
    x-transition:enter="transition ease-out duration-[var(--duration-base)]"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-[var(--duration-quick)]"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50">
    <div class="fixed inset-0 bg-ink-950/60" @click="close()"></div>
    <div class="fixed inset-x-0 top-24 max-w-[640px] mx-auto px-4">
        <div class="bg-white rounded-md shadow-xl overflow-hidden border border-ink-100">
            <div class="flex items-center gap-3 px-4 border-b border-ink-100">
                <span class="font-mono text-sm text-accent-500" aria-hidden="true">$</span>
                <input x-ref="searchInput" x-model="query" @input="search()" @keydown="navigate($event)" type="text" placeholder="search posts…"
                    class="w-full bg-transparent py-3.5 font-serif text-xl text-ink-900 placeholder:text-ink-300 focus:outline-none">
                <kbd class="font-mono text-[11px] text-ink-400 border border-surface-200 rounded-xs px-1.5 py-0.5">esc</kbd>
            </div>
            <div x-show="results.length > 0" class="max-h-96 overflow-y-auto">
                <template x-for="(result, index) in results" :key="result.slug">
                    <a :href="'/' + result.slug"
                        class="grid grid-cols-[minmax(0,1fr)_auto] gap-4 items-baseline px-4 py-3.5 border-b border-surface-50 hover:bg-surface-50 transition-colors"
                        :class="{ 'bg-surface-50': selected === index }">
                        <div class="min-w-0">
                            <p class="font-display text-lg text-ink-900" x-text="result.title"></p>
                            <p class="mt-1 font-serif text-sm text-ink-500 truncate" x-text="result.excerpt"></p>
                        </div>
                        <span x-show="result.tag" class="eyebrow" x-text="'/' + (result.tag || '').toLowerCase() + '/'"></span>
                    </a>
                </template>
            </div>
            <div x-show="query.length >= 2 && results.length === 0 && !loading" class="px-4 py-10 text-center font-mono text-xs text-ink-400">
                nothing found.
            </div>
            <div x-show="loading" class="px-4 py-10 text-center font-mono text-xs text-ink-300">
                searching…
            </div>
            <div class="px-4 py-2 flex gap-4 font-mono text-[11px] text-surface-400"><span>↑↓ navigate</span><span>↵ open</span></div>
        </div>
    </div>
</div>
