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
}" x-on:open-search.window="open = true; $nextTick(() => $refs.searchInput.focus())" x-on:keydown.escape.window="open = false; query = ''; results = []" x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50" style="display: none;">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm" @click="open = false; query = ''; results = []"></div>
    <div class="fixed inset-x-0 top-20 max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
            <div class="flex items-center px-4 border-b border-gray-200">
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input x-ref="searchInput" x-model="query" @input="search()" @keydown="navigate($event)" type="text" placeholder="Search posts..." class="w-full px-4 py-4 text-lg text-gray-900 placeholder-gray-400 focus:outline-none">
                <button @click="open = false; query = ''; results = []" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div x-show="results.length > 0" class="max-h-96 overflow-y-auto">
                <template x-for="(result, index) in results" :key="result.slug">
                    <a :href="'/' + result.slug" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100" :class="{ 'bg-gray-50': selected === index }">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-900" x-text="result.title"></p>
                                <p class="text-xs text-gray-500 mt-1" x-text="result.excerpt"></p>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <span x-show="result.tag" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-emerald-600" x-text="result.tag"></span>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
            <div x-show="query.length >= 2 && results.length === 0 && !loading" class="px-4 py-8 text-center text-gray-500">
                No results found.
            </div>
            <div x-show="loading" class="px-4 py-8 text-center text-gray-400">
                Searching...
            </div>
        </div>
    </div>
</div>
