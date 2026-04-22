<div x-data="{
    name: {{ Js::from(old('name', $tag->name ?? '')) }},
    slug: {{ Js::from(old('slug', $tag->slug ?? '')) }},
    slugManual: {{ Js::from((bool) old('slug', $tag->slug ?? '')) }},
    generateSlug() {
        if (this.slugManual && this.slug) return;
        this.slug = this.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    }
}">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card title="Details">
                <div class="space-y-4">
                    <x-ui.input name="name" label="Name" x-model="name" @input.debounce.500ms="generateSlug()" required />
                    <x-ui.input name="slug" label="Slug" x-model="slug" @input="slugManual = true" class="font-mono" required />
                    <x-ui.textarea name="description" label="Description" rows="4">{{ old('description', $tag->description ?? '') }}</x-ui.textarea>
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card title="Assets">
                <x-ui.input name="feature_image" label="Feature image URL" :value="old('feature_image', $tag->feature_image ?? '')" />
            </x-ui.card>

            <x-ui.card title="SEO">
                <div class="space-y-3">
                    <x-ui.input name="meta_title" label="Meta title" :value="old('meta_title', $tag->meta_title ?? '')" />
                    <x-ui.textarea name="meta_description" label="Meta description" rows="2">{{ old('meta_description', $tag->meta_description ?? '') }}</x-ui.textarea>
                </div>
            </x-ui.card>

            <x-ui.button type="submit" variant="primary" class="w-full">
                {{ isset($tag) && $tag->exists ? 'Save changes' : 'Create tag' }}
            </x-ui.button>
        </div>
    </div>
</div>
