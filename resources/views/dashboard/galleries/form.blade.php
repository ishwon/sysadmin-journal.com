<div x-data="{
    images: {{ Js::from(old('images', isset($gallery) ? $gallery->images->map(fn ($i) => ['path' => $i->image_path, 'caption' => $i->caption, 'alt_text' => $i->alt_text])->toArray() : [])) }},
    addImage() { this.images.push({ path: '', caption: '', alt_text: '' }); },
    removeImage(index) { this.images.splice(index, 1); }
}">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card title="Details">
                <div class="space-y-4">
                    <x-ui.input name="title" label="Title" :value="old('title', $gallery->title ?? '')" required />
                    <x-ui.textarea name="description" label="Description" rows="3">{{ old('description', $gallery->description ?? '') }}</x-ui.textarea>
                    <x-ui.input name="cover_image" label="Cover image URL" :value="old('cover_image', $gallery->cover_image ?? '')" />
                </div>
            </x-ui.card>

            <x-ui.card title="Images">
                <x-slot:actions>
                    <x-ui.button type="button" variant="secondary" size="sm" @click="addImage()">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/></svg>
                        Add image
                    </x-ui.button>
                </x-slot:actions>
                <template x-for="(image, index) in images" :key="index">
                    <div class="border border-surface-200 dark:border-ink-700 rounded-md p-4 mb-3">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs uppercase tracking-wide font-semibold text-ink-500 dark:text-ink-400" x-text="'Image ' + (index + 1)"></span>
                            <button type="button" @click="removeImage(index)" class="text-xs font-medium text-danger-600 hover:text-danger-700 transition-colors">Remove</button>
                        </div>
                        <div class="space-y-2">
                            <input type="text" :name="'images[' + index + '][path]'" x-model="image.path" placeholder="Image path"
                                class="w-full rounded-md border border-ink-200 bg-white px-3 py-2 text-sm text-ink-900 placeholder:text-ink-400 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-500/20 dark:bg-ink-900 dark:border-ink-700 dark:text-ink-50">
                            <input type="text" :name="'images[' + index + '][caption]'" x-model="image.caption" placeholder="Caption"
                                class="w-full rounded-md border border-ink-200 bg-white px-3 py-2 text-sm text-ink-900 placeholder:text-ink-400 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-500/20 dark:bg-ink-900 dark:border-ink-700 dark:text-ink-50">
                            <input type="text" :name="'images[' + index + '][alt_text]'" x-model="image.alt_text" placeholder="Alt text"
                                class="w-full rounded-md border border-ink-200 bg-white px-3 py-2 text-sm text-ink-900 placeholder:text-ink-400 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-500/20 dark:bg-ink-900 dark:border-ink-700 dark:text-ink-50">
                        </div>
                    </div>
                </template>
                <div x-show="images.length === 0">
                    <x-ui.empty-state title="No images" description="Click ‘Add image’ to build the gallery." />
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            @if (isset($gallery) && $gallery->slug)
                <x-ui.card title="Embed shortcode">
                    <p class="text-sm text-ink-600 dark:text-ink-300">Paste this in any post:</p>
                    <code class="mt-2 inline-flex items-center gap-1 rounded bg-ink-50 dark:bg-ink-800 px-2 py-1 font-mono text-sm text-ink-800 dark:text-ink-100">[gallery:{{ $gallery->slug }}]</code>
                </x-ui.card>
            @endif

            <x-ui.button type="submit" variant="primary" class="w-full">
                {{ isset($gallery) && $gallery->exists ? 'Save changes' : 'Create gallery' }}
            </x-ui.button>
        </div>
    </div>
</div>
