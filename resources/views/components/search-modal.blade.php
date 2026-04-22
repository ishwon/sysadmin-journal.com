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
    }
}"
    x-on:open-search.window="open = true; $nextTick(() => $refs.searchInput.focus())"
    x-on:keydown.escape.window="open = false; query = ''; results = []"
    x-show="open" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50">
    <div class="fixed inset-0 bg-ink-900/75 backdrop-blur-sm" @click="open = false; query = ''; results = []"></div>
    <div class="fixed inset-x-0 top-20 max-w-2xl mx-auto px-4">
        <div class="bg-white dark:bg-ink-900 rounded-lg shadow-xl overflow-hidden border border-surface-200 dark:border-ink-700">
            <div class="flex items-center px-4 border-b border-surface-200 dark:border-ink-700">
                <svg class="h-5 w-5 text-ink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input x-ref="searchInput" x-model="query" @input="search()" @keydown="navigate($event)" type="text" placeholder="Search posts..."
                    class="w-full bg-transparent px-4 py-4 text-lg text-ink-900 dark:text-ink-50 placeholder:text-ink-400 focus:outline-none">
                <button @click="open = false; query = ''; results = []" class="text-ink-400 hover:text-ink-700 dark:hover:text-ink-200 transition-colors" aria-label="Close">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div x-show="results.length > 0" class="max-h-96 overflow-y-auto">
                <template x-for="(result, index) in results" :key="result.slug">
                    <a :href="'/' + result.slug"
                        class="block px-4 py-3 border-b border-surface-100 dark:border-ink-800 hover:bg-surface-50 dark:hover:bg-ink-800 transition-colors"
                        :class="{ 'bg-surface-50 dark:bg-ink-800': selected === index }">
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-ink-900 dark:text-ink-50" x-text="result.title"></p>
                                <p class="text-xs text-ink-500 dark:text-ink-300 mt-1 truncate" x-text="result.excerpt"></p>
                            </div>
                            <div class="shrink-0">
                                <span x-show="result.tag" class="inline-flex items-center gap-1 rounded-full bg-accent-50 dark:bg-accent-900/30 px-2 py-0.5 text-xs font-medium text-accent-700 dark:text-accent-300 ring-1 ring-inset ring-accent-600/20">
                                    <span x-text="result.tag"></span>
                                </span>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
            <div x-show="query.length >= 2 && results.length === 0 && !loading" class="px-4 py-10 text-center text-sm text-ink-500 dark:text-ink-400">
                No results found.
            </div>
            <div x-show="loading" class="px-4 py-10 text-center text-sm text-ink-400 dark:text-ink-500">
                Searching…
            </div>
        </div>
    </div>
</div>
