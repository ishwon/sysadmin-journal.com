<div x-data="{
    name: '{{ old('name', $user->name ?? '') }}',
    slug: '{{ old('slug', $user->slug ?? '') }}',
    slugManual: {{ old('slug', $user->slug ?? '') ? 'true' : 'false' }},
    generateSlug() {
        if (this.slugManual && this.slug) return;
        this.slug = this.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    }
}">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" id="name" x-model="name" @input.debounce.500ms="generateSlug()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500" required>
                </div>
                <div class="mb-4">
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" name="slug" id="slug" x-model="slug" @input="slugManual = true" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 font-mono text-sm" required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password {{ isset($user) && $user->exists ? '(leave blank to keep current)' : '' }}</label>
                    <input type="password" name="password" id="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500" {{ isset($user) && $user->exists ? '' : 'required' }}>
                </div>
                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                    <textarea name="bio" id="bio" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500">{{ old('bio', $user->bio ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="mb-4">
                    <label for="profile_image" class="block text-sm font-medium text-gray-700 mb-1">Profile Image URL</label>
                    <input type="text" name="profile_image" id="profile_image" value="{{ old('profile_image', $user->profile_image ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                </div>
                <div class="mb-4">
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                    <input type="text" name="website" id="website" value="{{ old('website', $user->website ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                </div>
                <div class="mb-4">
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" name="location" id="location" value="{{ old('location', $user->location ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                </div>
                <div class="mb-4">
                    <label for="facebook" class="block text-sm font-medium text-gray-700 mb-1">Facebook</label>
                    <input type="text" name="facebook" id="facebook" value="{{ old('facebook', $user->facebook ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                </div>
                <div>
                    <label for="twitter" class="block text-sm font-medium text-gray-700 mb-1">Twitter</label>
                    <input type="text" name="twitter" id="twitter" value="{{ old('twitter', $user->twitter ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                </div>
            </div>

            <button type="submit" class="w-full bg-emerald-500 text-white py-2 px-4 rounded-md font-medium hover:bg-emerald-600 transition">Save</button>
        </div>
    </div>
</div>
