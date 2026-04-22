<div x-data="{
    name: {{ Js::from(old('name', $user->name ?? '')) }},
    slug: {{ Js::from(old('slug', $user->slug ?? '')) }},
    slugManual: {{ Js::from((bool) old('slug', $user->slug ?? '')) }},
    generateSlug() {
        if (this.slugManual && this.slug) return;
        this.slug = this.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    }
}">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card title="Profile">
                <div class="space-y-4">
                    <x-ui.input name="name" label="Name" x-model="name" @input.debounce.500ms="generateSlug()" required />
                    <x-ui.input name="slug" label="Slug" x-model="slug" @input="slugManual = true" class="font-mono" required />
                    <x-ui.input name="email" type="email" label="Email" :value="old('email', $user->email ?? '')" required />
                    <x-ui.input name="password" type="password" label="Password"
                        :hint="isset($user) && $user->exists ? 'Leave blank to keep current.' : null"
                        :required="!(isset($user) && $user->exists)" />
                    <x-ui.textarea name="bio" label="Bio" rows="4">{{ old('bio', $user->bio ?? '') }}</x-ui.textarea>
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card title="Avatar & contact">
                <div class="space-y-3">
                    <x-ui.input name="profile_image" label="Profile image URL" :value="old('profile_image', $user->profile_image ?? '')" />
                    <x-ui.input name="website" label="Website" :value="old('website', $user->website ?? '')" />
                    <x-ui.input name="location" label="Location" :value="old('location', $user->location ?? '')" />
                </div>
            </x-ui.card>

            <x-ui.card title="Social">
                <div class="space-y-3">
                    <x-ui.input name="facebook" label="Facebook" :value="old('facebook', $user->facebook ?? '')" />
                    <x-ui.input name="twitter" label="Twitter" :value="old('twitter', $user->twitter ?? '')" />
                </div>
            </x-ui.card>

            <x-ui.button type="submit" variant="primary" class="w-full">
                {{ isset($user) && $user->exists ? 'Save changes' : 'Create user' }}
            </x-ui.button>
        </div>
    </div>
</div>
