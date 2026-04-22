@extends('layouts.brand-system')

@section('title', 'Brand System')

@section('content')
<div class="flex min-h-screen">
    {{-- Side nav --}}
    <aside class="sticky top-0 hidden lg:flex h-screen w-60 flex-col border-r border-surface-200 bg-white dark:bg-ink-900 dark:border-ink-700">
        <div class="px-6 py-6 border-b border-surface-200 dark:border-ink-700">
            <p class="text-xs uppercase tracking-wider text-ink-500 dark:text-ink-400 font-semibold">SysAdmin Journal</p>
            <h1 class="mt-1 text-lg font-bold text-ink-900 dark:text-ink-50">Brand System</h1>
        </div>
        <nav class="flex-1 overflow-y-auto py-3 text-sm">
            @php
                $sections = [
                    'foundations' => 'Foundations',
                    'palette' => 'Palette',
                    'typography' => 'Typography',
                    'elevation' => 'Shadows & radius',
                    'buttons' => 'Buttons',
                    'forms' => 'Form controls',
                    'badges' => 'Badges',
                    'alerts' => 'Alerts',
                    'toasts' => 'Toasts',
                    'cards' => 'Cards',
                    'stats' => 'Stat cards',
                    'modals' => 'Modals',
                    'dropdowns' => 'Dropdowns',
                    'tabs' => 'Tabs',
                    'tables' => 'Tables',
                    'breadcrumbs' => 'Breadcrumbs',
                    'pagination' => 'Pagination',
                    'empty-states' => 'Empty states',
                    'skeletons' => 'Skeletons',
                    'dashboard-preview' => 'Dashboard preview',
                    'blog-preview' => 'Blog preview',
                ];
            @endphp
            @foreach ($sections as $id => $label)
                <a href="#{{ $id }}" class="block px-6 py-1.5 text-ink-600 hover:text-accent-700 hover:bg-surface-50 dark:text-ink-300 dark:hover:bg-ink-800 dark:hover:text-accent-400 transition-colors">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
        <div class="p-6 border-t border-surface-200 dark:border-ink-700">
            <button @click="dark = !dark; localStorage.theme = dark ? 'dark' : 'light'"
                class="inline-flex items-center gap-2 text-sm text-ink-600 hover:text-ink-900 dark:text-ink-300 dark:hover:text-ink-50">
                <svg x-show="!dark" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
                <svg x-show="dark" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464l-.707-.707a1 1 0 00-1.414 1.414l.707.707a1 1 0 001.414-1.414zM2 10a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1zm8 8a1 1 0 01-1-1v-1a1 1 0 112 0v1a1 1 0 01-1 1zm-5.657-2.343a1 1 0 00-1.414-1.414l-.707.707a1 1 0 101.414 1.414l.707-.707zm11.314-.707a1 1 0 011.414 1.414l-.707.707a1 1 0 01-1.414-1.414l.707-.707z" clip-rule="evenodd"/></svg>
                <span x-text="dark ? 'Dark mode' : 'Light mode'"></span>
            </button>
        </div>
    </aside>

    {{-- Content --}}
    <main class="flex-1 min-w-0">
        {{-- Hero --}}
        <section id="foundations" class="border-b border-surface-200 dark:border-ink-800 bg-white dark:bg-ink-900">
            <div class="max-w-5xl mx-auto px-6 lg:px-10 py-16">
                <p class="text-xs uppercase tracking-wider font-semibold text-accent-700 dark:text-accent-400">v1.0 — Preview</p>
                <h1 class="mt-3 text-4xl md:text-5xl font-extrabold tracking-tight text-ink-900 dark:text-ink-50">
                    A brand system for SysAdmin&nbsp;Journal.
                </h1>
                <p class="mt-4 text-lg text-ink-600 dark:text-ink-300 max-w-2xl font-serif">
                    Every token, surface and control the backoffice and public blog will draw from.
                    Flip dark mode in the sidebar to pressure-test every component in both schemes.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <x-ui.button variant="primary" href="#buttons">Browse components</x-ui.button>
                    <x-ui.button variant="secondary" href="#palette">See palette</x-ui.button>
                    <x-ui.badge tone="success" dot>Token-driven</x-ui.badge>
                    <x-ui.badge tone="info" dot>Dark mode ready</x-ui.badge>
                </div>
            </div>
        </section>

        <div class="max-w-5xl mx-auto px-6 lg:px-10 py-12 space-y-16">

            {{-- Palette --}}
            <section id="palette">
                <x-brand.section-header eyebrow="Color" title="Palette" description="The five brand hexes, expanded into usable ramps. Click a swatch hex to copy." />
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    @php
                        $ramps = [
                            'Ink — Primary (#27374D)' => ['50' => 'bg-ink-50', '100' => 'bg-ink-100', '200' => 'bg-ink-200', '300' => 'bg-ink-300', '400' => 'bg-ink-400', '500' => 'bg-ink-500', '600' => 'bg-ink-600', '700' => 'bg-ink-700', '800' => 'bg-ink-800', '900' => 'bg-ink-900', '950' => 'bg-ink-950'],
                            'Accent — Success (#10b981)' => ['50' => 'bg-accent-50', '100' => 'bg-accent-100', '200' => 'bg-accent-200', '300' => 'bg-accent-300', '400' => 'bg-accent-400', '500' => 'bg-accent-500', '600' => 'bg-accent-600', '700' => 'bg-accent-700', '800' => 'bg-accent-800', '900' => 'bg-accent-900'],
                            'Surface (#DDE6ED)' => ['50' => 'bg-surface-50', '100' => 'bg-surface-100', '200' => 'bg-surface-200', '300' => 'bg-surface-300', '400' => 'bg-surface-400', '500' => 'bg-surface-500'],
                            'Danger (#EA5455)' => ['50' => 'bg-danger-50', '100' => 'bg-danger-100', '200' => 'bg-danger-200', '300' => 'bg-danger-300', '400' => 'bg-danger-400', '500' => 'bg-danger-500', '600' => 'bg-danger-600', '700' => 'bg-danger-700', '900' => 'bg-danger-900'],
                            'Warning (#F66B0E)' => ['50' => 'bg-warning-50', '100' => 'bg-warning-100', '200' => 'bg-warning-200', '300' => 'bg-warning-300', '400' => 'bg-warning-400', '500' => 'bg-warning-500', '600' => 'bg-warning-600', '700' => 'bg-warning-700', '900' => 'bg-warning-900'],
                        ];
                    @endphp
                    @foreach ($ramps as $name => $steps)
                        <x-ui.card :title="$name" padding="sm">
                            <div class="grid grid-cols-6 md:grid-cols-11 gap-1">
                                @foreach ($steps as $step => $class)
                                    <div class="flex flex-col items-center">
                                        <div class="{{ $class }} h-12 w-full rounded-sm ring-1 ring-black/5"></div>
                                        <span class="mt-1 text-[10px] text-ink-500 dark:text-ink-400">{{ $step }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </x-ui.card>
                    @endforeach
                </div>

                {{-- Semantic swatches --}}
                <div class="mt-6 grid grid-cols-2 md:grid-cols-5 gap-4">
                    @php
                        $brand = [
                            ['#27374D', 'Ink', 'bg-ink-800', 'text-white'],
                            ['#10b981', 'Accent', 'bg-accent-500', 'text-white'],
                            ['#DDE6ED', 'Surface', 'bg-surface-200', 'text-ink-800'],
                            ['#EA5455', 'Danger', 'bg-danger-500', 'text-white'],
                            ['#F66B0E', 'Warning', 'bg-warning-500', 'text-white'],
                        ];
                    @endphp
                    @foreach ($brand as [$hex, $label, $bg, $fg])
                        <div class="{{ $bg }} {{ $fg }} rounded-lg p-5 shadow-sm">
                            <p class="text-xs uppercase tracking-wide opacity-80">{{ $label }}</p>
                            <p class="mt-2 font-mono text-sm">{{ $hex }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Typography --}}
            <section id="typography">
                <x-brand.section-header eyebrow="Text" title="Typography" description="Instrument Sans for UI. Lora serif for long-form reading. JetBrains Mono for code." />
                <x-ui.card padding="lg" class="mt-6 space-y-6">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-ink-500 dark:text-ink-400">Display · 48px / 800</p>
                        <p class="text-4xl md:text-5xl font-extrabold tracking-tight text-ink-900 dark:text-ink-50">Run a command. Ship a post.</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-ink-500 dark:text-ink-400">Heading 1 · 30px / 700</p>
                        <h1 class="text-3xl font-bold tracking-tight text-ink-900 dark:text-ink-50">The quick brown sysadmin journals a post</h1>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-ink-500 dark:text-ink-400">Heading 2 · 24px / 600</p>
                        <h2 class="text-2xl font-semibold text-ink-900 dark:text-ink-50">Migrations, monitoring, and Mondays</h2>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-ink-500 dark:text-ink-400">Lead · 18px / 400 · serif</p>
                        <p class="font-serif text-lg text-ink-600 dark:text-ink-300">The subtle art of running a Linux box you only SSH into once a quarter.</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-ink-500 dark:text-ink-400">Body · 18px / 400 · serif · leading 1.75</p>
                        <p class="font-serif text-lg leading-[1.75] text-ink-800 dark:text-ink-100 max-w-[68ch]">
                            Serif body text at this scale eases long-form reading. At the openSUSE booth in Nuremberg last spring, I convinced myself that the only
                            real measure of a good blog post is whether a tired oncall engineer can finish it at 3 a.m. without squinting.
                        </p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-ink-500 dark:text-ink-400">UI · 14px / 500</p>
                        <p class="text-sm font-medium text-ink-700 dark:text-ink-200">Create post · Save draft · Schedule</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-ink-500 dark:text-ink-400">Micro · 12px uppercase tracking-wide</p>
                        <p class="text-xs uppercase tracking-wide text-ink-500 dark:text-ink-400">Published · 22 April 2026</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-ink-500 dark:text-ink-400">Mono</p>
                        <p class="font-mono text-sm text-ink-800 dark:text-ink-100">$ systemctl status nginx</p>
                    </div>
                </x-ui.card>
            </section>

            {{-- Elevation --}}
            <section id="elevation">
                <x-brand.section-header eyebrow="Surface" title="Shadows & radius" description="Tinted with ink so shadows feel like the same material, never grey fog." />
                <div class="mt-6 grid grid-cols-2 md:grid-cols-5 gap-4">
                    @foreach (['xs' => 'shadow-xs', 'sm' => 'shadow-sm', 'md' => 'shadow-md', 'lg' => 'shadow-lg', 'xl' => 'shadow-xl'] as $name => $class)
                        <div class="flex flex-col items-center gap-2">
                            <div class="{{ $class }} h-24 w-full rounded-lg bg-white dark:bg-ink-900"></div>
                            <span class="text-xs font-medium text-ink-600 dark:text-ink-300">shadow-{{ $name }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-8 grid grid-cols-2 md:grid-cols-5 gap-4">
                    @foreach (['xs', 'sm', 'md', 'lg', 'xl'] as $r)
                        <div class="flex flex-col items-center gap-2">
                            <div class="rounded-{{ $r }} h-24 w-full bg-ink-800"></div>
                            <span class="text-xs font-medium text-ink-600 dark:text-ink-300">rounded-{{ $r }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Buttons --}}
            <section id="buttons">
                <x-brand.section-header eyebrow="Controls" title="Buttons" description="One primary action per view. Danger is for destructive only." />
                <x-ui.card padding="lg" class="mt-6 space-y-6">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-ink-500 dark:text-ink-400 font-medium mb-3">Variants · size md</p>
                        <div class="flex flex-wrap items-center gap-3">
                            <x-ui.button variant="primary">Primary action</x-ui.button>
                            <x-ui.button variant="secondary">Secondary</x-ui.button>
                            <x-ui.button variant="ghost">Ghost</x-ui.button>
                            <x-ui.button variant="ink">Ink</x-ui.button>
                            <x-ui.button variant="warning">Warning</x-ui.button>
                            <x-ui.button variant="danger">Delete permanently</x-ui.button>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-ink-500 dark:text-ink-400 font-medium mb-3">Sizes</p>
                        <div class="flex flex-wrap items-center gap-3">
                            <x-ui.button variant="primary" size="sm">Small</x-ui.button>
                            <x-ui.button variant="primary" size="md">Medium</x-ui.button>
                            <x-ui.button variant="primary" size="lg">Large</x-ui.button>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-ink-500 dark:text-ink-400 font-medium mb-3">With icons</p>
                        <div class="flex flex-wrap items-center gap-3">
                            <x-ui.button variant="primary">
                                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/></svg>
                                New post
                            </x-ui.button>
                            <x-ui.button variant="secondary">
                                Preview
                                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </x-ui.button>
                            <x-ui.button variant="danger">
                                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z" clip-rule="evenodd"/></svg>
                                Delete
                            </x-ui.button>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-ink-500 dark:text-ink-400 font-medium mb-3">States</p>
                        <div class="flex flex-wrap items-center gap-3">
                            <x-ui.button variant="primary" disabled>Disabled</x-ui.button>
                            <x-ui.button variant="primary" loading>Saving…</x-ui.button>
                            <x-ui.button variant="secondary" disabled>Disabled</x-ui.button>
                        </div>
                    </div>
                </x-ui.card>
            </section>

            {{-- Forms --}}
            <section id="forms">
                <x-brand.section-header eyebrow="Controls" title="Form controls" description="Every input supports label, hint, error, and dark mode." />
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.card title="Text & friends" padding="lg">
                        <div class="space-y-4">
                            <x-ui.input name="title" label="Post title" value="Migrating from Ghost to Laravel" hint="Shown in listings and OG metadata." />
                            <x-ui.input name="slug" label="Slug" value="migrating-from-ghost" :prefix="'/posts/'" />
                            <x-ui.input name="url" label="Canonical URL" type="url" value="https://sysadmin-journal.com" :suffix="'.com'" />
                            <x-ui.input name="email" label="Email" type="email" value="" error="Enter a valid email address." />
                            <x-ui.input name="disabled" label="Read-only slug" value="permalocked" disabled />
                            <x-ui.textarea name="excerpt" label="Excerpt" hint="Plain text, 160 characters max." rows="3">A short hands-on guide to swapping your Ghost install for Laravel without dropping any URLs.</x-ui.textarea>
                            <x-ui.select name="status" label="Status" :options="['draft' => 'Draft', 'scheduled' => 'Scheduled', 'published' => 'Published', 'archived' => 'Archived']" selected="published" />
                        </div>
                    </x-ui.card>
                    <x-ui.card title="Choices" padding="lg">
                        <div class="space-y-6">
                            <div>
                                <p class="text-sm font-medium text-ink-800 dark:text-ink-100 mb-2">Checkbox group — Tags</p>
                                <div class="space-y-2">
                                    <x-ui.checkbox name="tags[linux]" label="Linux" hint="System administration, distros, command line." checked />
                                    <x-ui.checkbox name="tags[sanskrit]" label="Sanskrit" hint="Language essays and translations." />
                                    <x-ui.checkbox name="tags[openSUSE]" label="openSUSE" hint="Advocacy and conference notes." checked />
                                    <x-ui.checkbox name="tags[devops]" label="DevOps" />
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-ink-800 dark:text-ink-100 mb-2">Radio group — Content format</p>
                                <div class="flex flex-wrap gap-4">
                                    <x-ui.radio name="format" value="html" label="HTML" />
                                    <x-ui.radio name="format" value="markdown" label="Markdown" checked />
                                    <x-ui.radio name="format" value="mdx" label="MDX" />
                                </div>
                            </div>
                            <div class="space-y-3">
                                <p class="text-sm font-medium text-ink-800 dark:text-ink-100">Toggles</p>
                                <x-ui.toggle name="featured" label="Feature on homepage" hint="Pins this post above the chronological feed." checked />
                                <x-ui.toggle name="comments" label="Allow comments" />
                                <x-ui.toggle name="newsletter" label="Send to newsletter" hint="Queues on save. Cannot be undone." />
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </section>

            {{-- Badges --}}
            <section id="badges">
                <x-brand.section-header eyebrow="Feedback" title="Badges" description="Statuses, tags, and categorical labels." />
                <x-ui.card padding="lg" class="mt-6 space-y-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-ui.badge>Neutral</x-ui.badge>
                        <x-ui.badge tone="success">Published</x-ui.badge>
                        <x-ui.badge tone="warning">Scheduled</x-ui.badge>
                        <x-ui.badge tone="danger">Deleted</x-ui.badge>
                        <x-ui.badge tone="info">Draft</x-ui.badge>
                        <x-ui.badge tone="ink">Pinned</x-ui.badge>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <x-ui.badge tone="success" dot>Live</x-ui.badge>
                        <x-ui.badge tone="warning" dot>Queued</x-ui.badge>
                        <x-ui.badge tone="danger" dot>Failed</x-ui.badge>
                        <x-ui.badge tone="info" dot>Draft</x-ui.badge>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <x-ui.badge size="sm" tone="success">sm</x-ui.badge>
                        <x-ui.badge size="md" tone="success">md</x-ui.badge>
                        <x-ui.badge size="lg" tone="success">lg</x-ui.badge>
                    </div>
                </x-ui.card>
            </section>

            {{-- Alerts --}}
            <section id="alerts">
                <x-brand.section-header eyebrow="Feedback" title="Alerts" description="Inline messages. Replace the flash divs in the current dashboard layout." />
                <div class="mt-6 space-y-3">
                    <x-ui.alert tone="success" title="Post published">
                        Your post was saved and is now live at <span class="font-mono">/migrating-from-ghost</span>.
                    </x-ui.alert>
                    <x-ui.alert tone="warning" title="Scheduled for later">
                        This post will go live on 25 April 2026 at 09:00 UTC. You can still edit it until then.
                    </x-ui.alert>
                    <x-ui.alert tone="danger" title="Something broke" dismissible>
                        We couldn't reach the media bucket. Check the <span class="underline">storage credentials</span> and retry.
                    </x-ui.alert>
                    <x-ui.alert tone="info">
                        You have <strong>3 drafts</strong> older than a month. Tidy them from the Posts screen.
                    </x-ui.alert>
                </div>
            </section>

            {{-- Toasts --}}
            <section id="toasts">
                <x-brand.section-header eyebrow="Feedback" title="Toasts" description="Ephemeral, auto-dismissing. Trigger from any Alpine context." />
                <x-ui.card padding="lg" class="mt-6">
                    <div class="flex flex-wrap gap-3">
                        <x-ui.button variant="primary" x-on:click="$dispatch('toast-success')">Fire success</x-ui.button>
                        <x-ui.button variant="warning" x-on:click="$dispatch('toast-warning')">Fire warning</x-ui.button>
                        <x-ui.button variant="danger" x-on:click="$dispatch('toast-danger')">Fire error</x-ui.button>
                        <x-ui.button variant="secondary" x-on:click="$dispatch('toast-info')">Fire info</x-ui.button>
                    </div>
                    <p class="mt-3 text-xs text-ink-500 dark:text-ink-400">Appears bottom-right. Auto-dismisses after 4.5s.</p>
                </x-ui.card>
                <div class="fixed bottom-6 right-6 z-50 space-y-3 pointer-events-none">
                    <x-ui.toast tone="success" title="Saved" message="Your draft was saved 2 seconds ago." on="toast-success" />
                    <x-ui.toast tone="warning" title="Heads up" message="You're editing a published post." on="toast-warning" />
                    <x-ui.toast tone="danger" title="Save failed" message="Network dropped. Your text is in localStorage." on="toast-danger" />
                    <x-ui.toast tone="info" title="Autosave on" message="We'll back you up every 30s." on="toast-info" />
                </div>
            </section>

            {{-- Cards --}}
            <section id="cards">
                <x-brand.section-header eyebrow="Layout" title="Cards" description="The primary container for chunks of content." />
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.card padding="md">
                        <p class="text-ink-700 dark:text-ink-200">Simple content card. No header, no footer, just padding.</p>
                    </x-ui.card>
                    <x-ui.card title="With title + actions" subtitle="Description sits under the title.">
                        <x-slot:actions>
                            <x-ui.button size="sm" variant="ghost">Cancel</x-ui.button>
                            <x-ui.button size="sm" variant="primary">Save</x-ui.button>
                        </x-slot:actions>
                        <p class="text-ink-700 dark:text-ink-200">A typical form container. Drops action buttons into the header.</p>
                    </x-ui.card>
                    <x-ui.card title="With footer" hoverable>
                        <p class="text-ink-700 dark:text-ink-200">Hover me — subtle elevation lift.</p>
                        <x-slot:footer>
                            <div class="flex items-center justify-between text-xs text-ink-500 dark:text-ink-400">
                                <span>Last edited 4 minutes ago</span>
                                <a href="#" class="text-accent-700 hover:underline dark:text-accent-400">Revision history</a>
                            </div>
                        </x-slot:footer>
                    </x-ui.card>
                    <x-ui.card title="Post preview">
                        <article>
                            <div class="flex items-center gap-2 mb-2">
                                <x-ui.badge tone="success" size="sm">Linux</x-ui.badge>
                                <span class="text-xs text-ink-500">3 min read</span>
                            </div>
                            <h3 class="text-lg font-semibold text-ink-900 dark:text-ink-50">How I survived an out-of-band kernel panic at 2am</h3>
                            <p class="mt-2 text-sm text-ink-600 dark:text-ink-300">The story of a single SSH session, three coffees, and the sysctl flag I should have set years ago.</p>
                        </article>
                    </x-ui.card>
                </div>
            </section>

            {{-- Stat cards --}}
            <section id="stats">
                <x-brand.section-header eyebrow="Dashboard" title="Stat cards" description="Headline numbers with trend." />
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <x-ui.stat-card label="Posts" value="184" change="+12 this month" trend="up" />
                    <x-ui.stat-card label="Drafts" value="7" change="+3 this week" trend="flat" />
                    <x-ui.stat-card label="Broken links" value="2" change="−4 this week" trend="down" />
                    <x-ui.stat-card label="Monthly readers" value="42.1k" change="+8.6%" trend="up" />
                </div>
            </section>

            {{-- Modal --}}
            <section id="modals">
                <x-brand.section-header eyebrow="Overlay" title="Modals" description="Alpine-powered, opens via window event. Esc / backdrop-click to close." />
                <x-ui.card padding="lg" class="mt-6">
                    <div class="flex flex-wrap gap-3">
                        <x-ui.button variant="danger" x-on:click="$dispatch('open-modal-delete')">Delete post…</x-ui.button>
                        <x-ui.button variant="secondary" x-on:click="$dispatch('open-modal-preview')">Open preview</x-ui.button>
                    </div>
                </x-ui.card>

                <x-ui.modal name="delete" title="Delete this post?">
                    <p>This permanently removes <span class="font-semibold text-ink-900 dark:text-ink-50">"Migrating from Ghost"</span> and every redirect pointing at it. This cannot be undone.</p>
                    <x-slot:footer>
                        <x-ui.button variant="ghost" x-on:click="$dispatch('close-modal-delete')">Cancel</x-ui.button>
                        <x-ui.button variant="danger">Yes, delete permanently</x-ui.button>
                    </x-slot:footer>
                </x-ui.modal>

                <x-ui.modal name="preview" title="Preview in responsive frame" size="lg">
                    <div class="rounded-md border border-surface-200 bg-surface-50 h-64 flex items-center justify-center text-ink-400 dark:bg-ink-800 dark:border-ink-700">
                        <span class="font-mono text-sm">[ iframe preview ]</span>
                    </div>
                    <x-slot:footer>
                        <x-ui.button variant="ghost" x-on:click="$dispatch('close-modal-preview')">Close</x-ui.button>
                        <x-ui.button variant="primary">Publish</x-ui.button>
                    </x-slot:footer>
                </x-ui.modal>
            </section>

            {{-- Dropdown --}}
            <section id="dropdowns">
                <x-brand.section-header eyebrow="Overlay" title="Dropdowns" description="For row actions, user menus, and filters." />
                <x-ui.card padding="lg" class="mt-6">
                    <div class="flex flex-wrap items-center gap-4">
                        <x-ui.dropdown>
                            <x-slot:trigger>
                                <x-ui.button variant="secondary">
                                    Actions
                                    <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.38a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                </x-ui.button>
                            </x-slot:trigger>
                            <x-ui.dropdown-item href="#">Edit post</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#">Duplicate</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#">View public page</x-ui.dropdown-item>
                            <div class="my-1 border-t border-surface-200 dark:border-ink-700"></div>
                            <x-ui.dropdown-item tone="danger">Delete…</x-ui.dropdown-item>
                        </x-ui.dropdown>

                        <x-ui.dropdown align="left" width="56">
                            <x-slot:trigger>
                                <button type="button" class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white px-2 py-1 text-sm hover:border-ink-300 dark:bg-ink-900 dark:border-ink-700">
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-ink-800 text-white text-xs font-semibold">IS</span>
                                    <span class="text-ink-800 dark:text-ink-100 pr-1">Ish Sookun</span>
                                </button>
                            </x-slot:trigger>
                            <x-ui.dropdown-item href="#">Profile</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#">API tokens</x-ui.dropdown-item>
                            <div class="my-1 border-t border-surface-200 dark:border-ink-700"></div>
                            <x-ui.dropdown-item tone="danger">Sign out</x-ui.dropdown-item>
                        </x-ui.dropdown>
                    </div>
                </x-ui.card>
            </section>

            {{-- Tabs --}}
            <section id="tabs">
                <x-brand.section-header eyebrow="Navigation" title="Tabs" description="For switching between sibling views." />
                <x-ui.card padding="lg" class="mt-6">
                    <x-ui.tabs :tabs="['editor' => 'Editor', 'preview' => 'Preview', 'seo' => 'SEO', 'settings' => 'Settings']" default="editor">
                        <div x-show="active === 'editor'">
                            <p class="text-sm text-ink-700 dark:text-ink-200">Markdown editor sits here. Swap easily to the preview tab to eyeball rendering.</p>
                        </div>
                        <div x-show="active === 'preview'" x-cloak>
                            <p class="text-sm text-ink-700 dark:text-ink-200">Live-rendered HTML of your current markdown.</p>
                        </div>
                        <div x-show="active === 'seo'" x-cloak>
                            <p class="text-sm text-ink-700 dark:text-ink-200">Meta title, description, OG image, Twitter image.</p>
                        </div>
                        <div x-show="active === 'settings'" x-cloak>
                            <p class="text-sm text-ink-700 dark:text-ink-200">Slug, status, author, publish date.</p>
                        </div>
                    </x-ui.tabs>
                </x-ui.card>
            </section>

            {{-- Tables --}}
            <section id="tables">
                <x-brand.section-header eyebrow="Data" title="Tables" description="The workhorse of the backoffice. Sortable headers, row badges, row-end dropdown." />
                <div class="mt-6">
                    <x-ui.table :columns="['Title', 'Tags', 'Status', 'Updated', '']">
                        @php
                            $rows = [
                                ['Migrating from Ghost to Laravel', ['Linux', 'DevOps'], 'success', 'Published', '2 hours ago'],
                                ['openSUSE Leap 16 at a glance', ['openSUSE'], 'success', 'Published', 'Yesterday'],
                                ['Configuring nftables for a tiny VPS', ['Linux'], 'warning', 'Scheduled', 'in 3 days'],
                                ['Why Sanskrit teaches you systems thinking', ['Sanskrit'], 'info', 'Draft', 'Last week'],
                                ['Post-mortem: the 2am kernel panic', ['Linux'], 'danger', 'Archived', '3 weeks ago'],
                            ];
                        @endphp
                        @foreach ($rows as [$title, $tags, $tone, $status, $updated])
                            <tr class="hover:bg-surface-50 dark:hover:bg-ink-800/60 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-ink-900 dark:text-ink-50">{{ $title }}</p>
                                    <p class="text-xs text-ink-500 dark:text-ink-400 font-mono mt-0.5">/{{ \Illuminate\Support\Str::slug($title) }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($tags as $t)
                                            <x-ui.badge tone="neutral" size="sm">{{ $t }}</x-ui.badge>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3"><x-ui.badge :tone="$tone" dot>{{ $status }}</x-ui.badge></td>
                                <td class="px-4 py-3 text-ink-500 dark:text-ink-400 whitespace-nowrap">{{ $updated }}</td>
                                <td class="px-4 py-3 text-right">
                                    <x-ui.dropdown>
                                        <x-slot:trigger>
                                            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-ink-500 hover:bg-surface-100 dark:text-ink-400 dark:hover:bg-ink-800" aria-label="Row actions">
                                                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4z"/></svg>
                                            </button>
                                        </x-slot:trigger>
                                        <x-ui.dropdown-item>Edit</x-ui.dropdown-item>
                                        <x-ui.dropdown-item>Duplicate</x-ui.dropdown-item>
                                        <x-ui.dropdown-item tone="danger">Delete</x-ui.dropdown-item>
                                    </x-ui.dropdown>
                                </td>
                            </tr>
                        @endforeach
                        <x-slot:footer>
                            <x-ui.pagination :current="1" :total="9" />
                        </x-slot:footer>
                    </x-ui.table>
                </div>
            </section>

            {{-- Breadcrumbs --}}
            <section id="breadcrumbs">
                <x-brand.section-header eyebrow="Navigation" title="Breadcrumbs" description="Shows hierarchy for deep screens." />
                <x-ui.card padding="lg" class="mt-6">
                    <x-ui.breadcrumbs :items="[
                        ['label' => 'Dashboard', 'href' => '#'],
                        ['label' => 'Posts', 'href' => '#'],
                        ['label' => 'Migrating from Ghost to Laravel'],
                    ]" />
                </x-ui.card>
            </section>

            {{-- Pagination --}}
            <section id="pagination">
                <x-brand.section-header eyebrow="Navigation" title="Pagination" description="Under tables and lists." />
                <x-ui.card padding="lg" class="mt-6">
                    <x-ui.pagination :current="3" :total="12" />
                </x-ui.card>
            </section>

            {{-- Empty state --}}
            <section id="empty-states">
                <x-brand.section-header eyebrow="Feedback" title="Empty states" description="Tell the user what's missing and how to move forward." />
                <div class="mt-6">
                    <x-ui.empty-state title="No scheduled posts" description="Schedule a draft to have it auto-publish at a future date. We'll email you when it goes live.">
                        <x-slot:action>
                            <x-ui.button variant="primary">Create post</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                </div>
            </section>

            {{-- Skeleton --}}
            <section id="skeletons">
                <x-brand.section-header eyebrow="Feedback" title="Skeletons" description="While content loads — one row per visual element, subtle pulse." />
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.card padding="md">
                        <div class="flex items-start gap-3">
                            <x-ui.skeleton shape="avatar" />
                            <div class="flex-1 space-y-2">
                                <x-ui.skeleton shape="heading" />
                                <x-ui.skeleton shape="line" />
                                <x-ui.skeleton shape="line" width="75%" />
                            </div>
                        </div>
                    </x-ui.card>
                    <x-ui.card padding="md">
                        <x-ui.skeleton shape="thumbnail" />
                        <div class="mt-4 space-y-2">
                            <x-ui.skeleton shape="heading" />
                            <x-ui.skeleton shape="line" />
                            <x-ui.skeleton shape="line" width="60%" />
                        </div>
                    </x-ui.card>
                </div>
            </section>

            {{-- Dashboard preview --}}
            <section id="dashboard-preview">
                <x-brand.section-header eyebrow="Application" title="Dashboard preview" description="How the new palette lands on the existing dashboard shell." />
                <div class="mt-6 rounded-xl overflow-hidden border border-surface-200 dark:border-ink-700 shadow-lg">
                    <div class="grid grid-cols-[220px_1fr] min-h-[520px]">
                        <aside class="bg-ink-900 text-white">
                            <div class="px-6 py-5 border-b border-ink-800">
                                <p class="text-sm font-extrabold">SysAdmin Journal</p>
                            </div>
                            <nav class="py-3 text-sm">
                                <x-ui.nav-item href="#" :active="true" :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6\'/></svg>'">
                                    Dashboard
                                </x-ui.nav-item>
                                <x-ui.nav-item href="#" :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z\'/></svg>'">
                                    Posts
                                </x-ui.nav-item>
                                <x-ui.nav-item href="#" :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\'/></svg>'">
                                    Pages
                                </x-ui.nav-item>
                                <x-ui.nav-item href="#" :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'/></svg>'">
                                    Galleries
                                </x-ui.nav-item>
                                <x-ui.nav-item href="#" :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z\'/></svg>'">
                                    Tags
                                </x-ui.nav-item>
                                <x-ui.nav-item href="#" :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z\'/></svg>'">
                                    Users
                                </x-ui.nav-item>
                            </nav>
                        </aside>
                        <div class="bg-surface-50 dark:bg-ink-950">
                            <header class="bg-white dark:bg-ink-900 border-b border-surface-200 dark:border-ink-800 px-6 py-4 flex items-center justify-between">
                                <div>
                                    <x-ui.breadcrumbs :items="[['label' => 'Dashboard', 'href' => '#'], ['label' => 'Posts']]" />
                                    <h2 class="mt-1 text-lg font-semibold text-ink-900 dark:text-ink-50">Posts</h2>
                                </div>
                                <div class="flex items-center gap-3">
                                    <x-ui.button size="sm" variant="secondary">Export CSV</x-ui.button>
                                    <x-ui.button size="sm" variant="primary">
                                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/></svg>
                                        New post
                                    </x-ui.button>
                                </div>
                            </header>
                            <div class="p-6 space-y-4">
                                <div class="grid grid-cols-3 gap-4">
                                    <x-ui.stat-card label="Published" value="184" change="+12" trend="up" />
                                    <x-ui.stat-card label="Drafts" value="7" change="+3" trend="flat" />
                                    <x-ui.stat-card label="This week" value="4" change="+1" trend="up" />
                                </div>
                                <x-ui.alert tone="success" dismissible>
                                    Post <strong>"Migrating from Ghost"</strong> was published.
                                </x-ui.alert>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Blog preview --}}
            <section id="blog-preview">
                <x-brand.section-header eyebrow="Public site" title="Blog frontend preview" description="Serif body + ink chrome + accent links. Reading progress bar sits under the header." />
                <div class="mt-6 rounded-xl overflow-hidden border border-surface-200 dark:border-ink-700 shadow-lg">
                    {{-- Mock header --}}
                    <div class="relative bg-ink-900 text-white">
                        <div class="max-w-3xl mx-auto px-6 py-4 flex items-center justify-between">
                            <span class="font-extrabold">SysAdmin Journal</span>
                            <div class="flex items-center gap-6 text-sm">
                                <a href="#" class="hover:text-accent-400">About</a>
                                <a href="#" class="hover:text-accent-400">Linux</a>
                                <a href="#" class="hover:text-accent-400">Sanskrit</a>
                                <x-ui.button variant="primary" size="sm">Get openSUSE</x-ui.button>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 h-0.5 bg-accent-500" style="width: 34%"></div>
                    </div>

                    {{-- Post --}}
                    <article class="bg-white dark:bg-ink-900 px-6 py-12">
                        <div class="max-w-[68ch] mx-auto">
                            <div class="flex items-center gap-3 text-xs uppercase tracking-wide text-ink-500 dark:text-ink-400">
                                <x-ui.badge tone="success" size="sm">Linux</x-ui.badge>
                                <span>22 April 2026 · 6 min read</span>
                            </div>
                            <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-ink-900 dark:text-ink-50">
                                Migrating from Ghost to Laravel without losing a single URL
                            </h1>
                            <p class="mt-4 font-serif text-lg text-ink-600 dark:text-ink-300 leading-relaxed">
                                A tactical, boring, un-glamorous guide. The kind sysadmins deserve.
                            </p>
                            <div class="mt-8 space-y-5 font-serif text-lg leading-[1.75] text-ink-800 dark:text-ink-100">
                                <p class="first-letter:float-left first-letter:mr-3 first-letter:text-6xl first-letter:font-bold first-letter:font-serif first-letter:text-ink-800 first-letter:leading-none dark:first-letter:text-ink-100">
                                    I had put off the migration for three years. Ghost was fine. The writing still reached people. But every time I wanted to change something small — a heading style, an RSS field, the comment system — I had to dig through a platform that wasn't mine.
                                </p>
                                <p>
                                    So on a Sunday afternoon in April, with nothing on the calendar and a fresh pot of coffee, I started. The ground rules were simple: <strong>zero broken permalinks</strong>, one command to deploy, and a stack I could debug in my sleep.
                                </p>
                                <blockquote class="border-l-4 border-accent-500 pl-4 text-ink-700 dark:text-ink-200 italic">
                                    The measure of a good tool is whether you can still fix it when you're tired.
                                </blockquote>
                                <p>
                                    The old posts were in <code class="font-mono text-[0.9em] bg-ink-50 text-ink-800 px-1.5 py-0.5 rounded dark:bg-ink-800 dark:text-ink-100">mobiledoc</code> format, which is nobody's idea of a good time. I wrote a tiny converter:
                                </p>
                                <pre class="bg-ink-900 text-ink-50 rounded-lg p-5 overflow-x-auto text-sm font-mono"><code>$ php artisan posts:import \
    --from=ghost.json \
    --strategy=preserve-slugs \
    --backfill-redirects</code></pre>
                            </div>
                        </div>
                    </article>
                </div>

                {{-- Post card preview --}}
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ([
                        ['Linux', 'How I survived an out-of-band kernel panic at 2am', 'The story of a single SSH session, three coffees, and the sysctl flag I should have set years ago.'],
                        ['openSUSE', 'openSUSE Leap 16 at a glance', 'A field report from the conference booth and my own workstation upgrade.'],
                    ] as [$tag, $title, $excerpt])
                        <article class="group rounded-lg overflow-hidden border border-surface-200 bg-white hover:shadow-md transition-shadow duration-[var(--duration-base)] dark:bg-ink-900 dark:border-ink-700">
                            <div class="aspect-[16/9] bg-gradient-to-br from-ink-700 to-ink-900 flex items-center justify-center text-ink-300">
                                <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M7 8l3 3-3 3M13 14h4"/></svg>
                            </div>
                            <div class="p-5">
                                <x-ui.badge tone="success" size="sm">{{ $tag }}</x-ui.badge>
                                <h3 class="mt-3 text-xl font-semibold text-ink-900 dark:text-ink-50 group-hover:text-accent-700 transition-colors">{{ $title }}</h3>
                                <p class="mt-2 text-sm text-ink-500 dark:text-ink-300">{{ $excerpt }}</p>
                                <div class="mt-4 flex items-center gap-2 text-xs text-ink-500">
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-ink-800 text-white text-[10px] font-semibold">IS</span>
                                    <span class="font-medium text-ink-700 dark:text-ink-200">Ish Sookun</span>
                                    <span aria-hidden="true">·</span>
                                    <span>4 min read</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            {{-- Footer --}}
            <footer class="pt-10 pb-16 border-t border-surface-200 dark:border-ink-800 text-center text-xs text-ink-500 dark:text-ink-400">
                <p>SysAdmin Journal brand system · v1.0 preview · Ink · Accent · Surface · Danger · Warning</p>
            </footer>
        </div>
    </main>
</div>
@endsection
