<div x-data="{
    title: {{ Js::from(old('title', $page->title ?? '')) }},
    slug: {{ Js::from(old('slug', $page->slug ?? '')) }},
    slugManual: {{ Js::from((bool) old('slug', $page->slug ?? '')) }},
    contentFormat: {{ Js::from(old('content_format', ($page->markdown ?? null) ? 'markdown' : 'html')) }},
    async generateSlug() {
        if (this.slugManual && this.slug) return;
        const response = await fetch({{ Js::from(route('dashboard.api.slug-check')) }}, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({ title: this.title, exclude_id: {{ Js::from($page->id ?? '') }} })
        });
        const data = await response.json();
        this.slug = data.slug;
    }
}">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card>
                <div class="space-y-4">
                    <x-ui.input name="title" label="Title" x-model="title" @input.debounce.500ms="generateSlug()" required />
                    <x-ui.input name="slug" label="Slug" x-model="slug" @input="slugManual = true" class="font-mono" required />
                    <div>
                        <p class="block text-sm font-medium text-ink-800 dark:text-ink-100 mb-1.5">Content format</p>
                        <div class="flex gap-4">
                            <x-ui.radio name="content_format" value="html" label="HTML" x-model="contentFormat" />
                            <x-ui.radio name="content_format" value="markdown" label="Markdown" x-model="contentFormat" />
                        </div>
                    </div>
                    <x-ui.textarea name="content" label="Content" rows="20" mono>{{ old('content', ($page->markdown ?? null) ?: ($page->html ?? '')) }}</x-ui.textarea>
                    <x-ui.textarea name="custom_excerpt" label="Custom excerpt" rows="3">{{ old('custom_excerpt', $page->custom_excerpt ?? '') }}</x-ui.textarea>
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card title="Publish">
                <div class="space-y-4">
                    <x-ui.select name="status" label="Status"
                        :options="['draft' => 'Draft', 'published' => 'Published']"
                        :selected="old('status', $page->status ?? 'draft')" />
                    <x-ui.input name="feature_image" label="Feature image URL" :value="old('feature_image', $page->feature_image ?? '')" />
                    <x-ui.input name="feature_image_alt" label="Feature image alt text" :value="old('feature_image_alt', $page->feature_image_alt ?? '')" />
                    <x-ui.input name="feature_image_caption" label="Feature image caption" :value="old('feature_image_caption', $page->feature_image_caption ?? '')" />
                </div>
            </x-ui.card>

            <x-ui.card title="SEO">
                <div class="space-y-3">
                    <x-ui.input name="meta_title" label="Meta title" :value="old('meta_title', $page->meta_title ?? '')" />
                    <x-ui.textarea name="meta_description" label="Meta description" rows="2">{{ old('meta_description', $page->meta_description ?? '') }}</x-ui.textarea>
                </div>
            </x-ui.card>

            <x-ui.button type="submit" variant="primary" class="w-full">
                {{ isset($page) && $page->exists ? 'Save changes' : 'Create page' }}
            </x-ui.button>
        </div>
    </div>
</div>
