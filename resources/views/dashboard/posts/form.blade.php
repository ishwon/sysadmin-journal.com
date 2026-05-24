<script src="/js/post-form.js"></script>

<div x-data="postForm({
    title: {{ Js::from(old('title', $post->title ?? '')) }},
    slug: {{ Js::from(old('slug', $post->slug ?? '')) }},
    contentFormat: {{ Js::from(old('content_format', ($post->markdown ?? null) ? 'markdown' : 'html')) }},
    slugCheckUrl: {{ Js::from(route('dashboard.api.slug-check')) }},
    postId: {{ Js::from($post->id ?? '') }}
})">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main column --}}
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card padding="md">
                <div class="space-y-4">
                    <x-ui.input name="title" label="Title" x-model="title" @input.debounce.500ms="generateSlug()" required />
                    <x-ui.input name="slug" label="Slug" x-model="slug" @input="slugManual = true" class="font-mono" required />
                </div>

                {{-- Tabs --}}
                <div class="mt-6 border-b border-surface-200 dark:border-ink-700">
                    <nav class="flex gap-6 -mb-px">
                        <button type="button" @click="activeTab = 'editor'"
                            :class="activeTab === 'editor' ? 'border-accent-500 text-accent-700 dark:text-accent-400' : 'border-transparent text-ink-500 hover:text-ink-800 hover:border-ink-300 dark:text-ink-400 dark:hover:text-ink-100'"
                            class="py-2 px-1 border-b-2 text-sm font-medium transition-colors">
                            Editor
                        </button>
                        <button type="button" @click="showPreview()"
                            :class="activeTab === 'preview' ? 'border-accent-500 text-accent-700 dark:text-accent-400' : 'border-transparent text-ink-500 hover:text-ink-800 hover:border-ink-300 dark:text-ink-400 dark:hover:text-ink-100'"
                            class="py-2 px-1 border-b-2 text-sm font-medium transition-colors">
                            Preview
                        </button>
                    </nav>
                </div>

                {{-- Editor pane --}}
                <div x-show="activeTab === 'editor'" class="mt-4 space-y-4">
                    <div>
                        <p class="block text-sm font-medium text-ink-800 dark:text-ink-100 mb-1.5">Content format</p>
                        <div class="flex gap-4">
                            <x-ui.radio name="content_format" value="html" label="HTML" x-model="contentFormat" />
                            <x-ui.radio name="content_format" value="markdown" label="Markdown" x-model="contentFormat" />
                        </div>
                    </div>
                    <x-ui.textarea name="content" label="Content" rows="20" mono>{{ old('content', ($post->markdown ?? null) ?: ($post->html ?? '')) }}</x-ui.textarea>
                </div>

                {{-- Preview pane --}}
                <div x-show="activeTab === 'preview'" x-cloak class="mt-4">
                    <iframe x-ref="previewFrame" class="w-full rounded-md border border-surface-200 bg-white dark:border-ink-700" style="min-height: 600px;"></iframe>
                </div>

                <div class="mt-4">
                    <x-ui.textarea name="custom_excerpt" label="Custom excerpt" rows="3">{{ old('custom_excerpt', $post->custom_excerpt ?? '') }}</x-ui.textarea>
                </div>
            </x-ui.card>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <x-ui.card title="Publish">
                <div class="space-y-4">
                    <x-ui.select name="status" label="Status"
                        :options="['draft' => 'Draft', 'published' => 'Published']"
                        :selected="old('status', $post->status ?? 'draft')" />
                    <x-ui.input name="published_at" type="datetime-local" label="Publish date"
                        :value="old('published_at', $post->published_at?->format('Y-m-d\TH:i'))"
                        hint="Drives the date shown on the article. Leave empty to use publish time." />
                    <x-ui.input name="feature_image" label="Feature image URL" :value="old('feature_image', $post->feature_image ?? '')" />
                    <x-ui.input name="feature_image_alt" label="Feature image alt" :value="old('feature_image_alt', $post->feature_image_alt ?? '')" />
                    <x-ui.input name="feature_image_caption" label="Feature image caption" :value="old('feature_image_caption', $post->feature_image_caption ?? '')" />
                </div>
            </x-ui.card>

            @isset($tags)
                <x-ui.card title="Tags">
                    <div class="max-h-48 overflow-y-auto space-y-2">
                        @foreach ($tags as $tag)
                            <x-ui.checkbox
                                name="tags[]"
                                :value="$tag->id"
                                :id="'tag_' . $tag->id"
                                :label="$tag->name"
                                :checked="in_array($tag->id, old('tags', isset($post) ? $post->tags->pluck('id')->toArray() : []))" />
                        @endforeach
                    </div>
                </x-ui.card>
            @endisset

            @isset($galleries)
                <x-ui.card title="Gallery">
                    <x-ui.select name="gallery_id"
                        :options="$galleries->pluck('title', 'id')->toArray()"
                        :selected="old('gallery_id', $post->gallery_id ?? '')"
                        placeholder="None"
                        hint="Displayed at the bottom of the article." />
                </x-ui.card>
            @endisset

            <x-ui.card title="SEO" subtitle="Overrides for crawlers.">
                <div class="space-y-3">
                    <x-ui.input name="meta_title" label="Meta title" :value="old('meta_title', $post->meta_title ?? '')" />
                    <x-ui.textarea name="meta_description" label="Meta description" rows="2">{{ old('meta_description', $post->meta_description ?? '') }}</x-ui.textarea>
                    <x-ui.input name="og_image" label="OG image URL" :value="old('og_image', $post->og_image ?? '')" />
                    <x-ui.input name="twitter_image" label="Twitter image URL" :value="old('twitter_image', $post->twitter_image ?? '')" />
                </div>
            </x-ui.card>

            <x-ui.button type="submit" variant="primary" class="w-full">
                {{ isset($post) && $post->exists ? 'Save changes' : 'Create post' }}
            </x-ui.button>
        </div>
    </div>
</div>
