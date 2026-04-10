<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

#[Signature('ghost:import {path? : Path to Ghost JSON export file}')]
#[Description('Import posts, pages, tags, and users from a Ghost JSON export')]
class ImportGhost extends Command
{
    public function handle(): int
    {
        $path = $this->argument('path') ?? $this->findExportFile();

        if (! $path || ! file_exists($path)) {
            $this->error('Ghost export file not found.');

            return self::FAILURE;
        }

        $this->info("Importing from: {$path}");

        $json = json_decode(file_get_contents($path), true);
        $data = $json['db'][0]['data'];

        $postsMeta = collect($data['posts_meta'] ?? [])->keyBy('post_id');

        DB::transaction(function () use ($data, $postsMeta) {
            $userMap = $this->importUsers($data['users'] ?? []);
            $tagMap = $this->importTags($data['tags'] ?? []);
            $postMap = $this->importPosts($data['posts'] ?? [], $postsMeta);
            $this->importPostTags($data['posts_tags'] ?? [], $postMap, $tagMap);
            $this->importPostAuthors($data['posts_authors'] ?? [], $postMap, $userMap);
        });

        $this->newLine();
        $this->info('Import complete!');
        $this->table(['Type', 'Count'], [
            ['Users', User::count()],
            ['Tags', Tag::count()],
            ['Posts', Post::posts()->count()],
            ['Pages', Post::pages()->count()],
        ]);

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array<string, mixed>>  $users
     * @return array<string, int>
     */
    private function importUsers(array $users): array
    {
        $map = [];
        $this->info('Importing users...');

        foreach ($users as $user) {
            $record = User::create([
                'name' => $user['name'],
                'slug' => $user['slug'],
                'email' => $user['email'],
                'password' => Hash::make('changeme123'),
                'bio' => $user['bio'],
                'profile_image' => $this->replaceGhostUrl($user['profile_image']),
                'website' => $user['website'],
                'location' => $user['location'],
                'facebook' => $user['facebook'],
                'twitter' => $user['twitter'],
                'ghost_id' => $user['id'],
            ]);

            $map[$user['id']] = $record->id;
        }

        $this->info("  Imported {$record->id} users.");

        return $map;
    }

    /**
     * @param  array<int, array<string, mixed>>  $tags
     * @return array<string, int>
     */
    private function importTags(array $tags): array
    {
        $map = [];
        $this->info('Importing tags...');

        foreach ($tags as $tag) {
            $record = Tag::create([
                'name' => $tag['name'],
                'slug' => $tag['slug'],
                'description' => $tag['description'],
                'feature_image' => $this->replaceGhostUrl($tag['feature_image']),
                'meta_title' => $tag['meta_title'],
                'meta_description' => $tag['meta_description'],
                'ghost_id' => $tag['id'],
            ]);

            $map[$tag['id']] = $record->id;
        }

        $this->info('  Imported '.count($map).' tags.');

        return $map;
    }

    /**
     * @param  array<int, array<string, mixed>>  $posts
     * @param  Collection<string, array<string, mixed>>  $postsMeta
     * @return array<string, int>
     */
    private function importPosts(array $posts, $postsMeta): array
    {
        $map = [];
        $this->info('Importing posts and pages...');

        foreach ($posts as $post) {
            $meta = $postsMeta->get($post['id'], []);

            $html = $this->replaceGhostUrl($post['html']);
            $featureImage = $this->replaceGhostUrl($post['feature_image']);

            $record = Post::create([
                'title' => $post['title'],
                'slug' => $post['slug'],
                'html' => $html,
                'plaintext' => $post['plaintext'],
                'custom_excerpt' => $post['custom_excerpt'],
                'feature_image' => $featureImage,
                'feature_image_alt' => $meta['feature_image_alt'] ?? null,
                'feature_image_caption' => $meta['feature_image_caption'] ?? null,
                'type' => $post['type'] ?? 'post',
                'status' => $post['status'] ?? 'draft',
                'reading_time' => Post::calculateReadingTime($post['plaintext']),
                'published_at' => $post['published_at'],
                'meta_title' => $meta['meta_title'] ?? null,
                'meta_description' => $meta['meta_description'] ?? null,
                'og_image' => $this->replaceGhostUrl($meta['og_image'] ?? null),
                'og_title' => $meta['og_title'] ?? null,
                'og_description' => $meta['og_description'] ?? null,
                'twitter_image' => $this->replaceGhostUrl($meta['twitter_image'] ?? null),
                'twitter_title' => $meta['twitter_title'] ?? null,
                'twitter_description' => $meta['twitter_description'] ?? null,
                'canonical_url' => $post['canonical_url'],
                'ghost_id' => $post['id'],
                'created_at' => $post['created_at'],
                'updated_at' => $post['updated_at'],
            ]);

            $map[$post['id']] = $record->id;
        }

        $this->info('  Imported '.count($map).' posts/pages.');

        return $map;
    }

    /**
     * @param  array<int, array<string, mixed>>  $postsTags
     * @param  array<string, int>  $postMap
     * @param  array<string, int>  $tagMap
     */
    private function importPostTags(array $postsTags, array $postMap, array $tagMap): void
    {
        $this->info('Importing post-tag relationships...');
        $count = 0;

        foreach ($postsTags as $pt) {
            $postId = $postMap[$pt['post_id']] ?? null;
            $tagId = $tagMap[$pt['tag_id']] ?? null;

            if ($postId && $tagId) {
                DB::table('post_tag')->insert([
                    'post_id' => $postId,
                    'tag_id' => $tagId,
                    'sort_order' => $pt['sort_order'] ?? 0,
                ]);
                $count++;
            }
        }

        $this->info("  Imported {$count} post-tag relationships.");
    }

    /**
     * @param  array<int, array<string, mixed>>  $postsAuthors
     * @param  array<string, int>  $postMap
     * @param  array<string, int>  $userMap
     */
    private function importPostAuthors(array $postsAuthors, array $postMap, array $userMap): void
    {
        $this->info('Importing post-author relationships...');
        $count = 0;

        foreach ($postsAuthors as $pa) {
            $postId = $postMap[$pa['post_id']] ?? null;
            $userId = $userMap[$pa['author_id']] ?? null;

            if ($postId && $userId) {
                DB::table('post_user')->insert([
                    'post_id' => $postId,
                    'user_id' => $userId,
                    'sort_order' => $pa['sort_order'] ?? 0,
                ]);
                $count++;
            }
        }

        $this->info("  Imported {$count} post-author relationships.");
    }

    private function replaceGhostUrl(?string $value): ?string
    {
        if (! $value) {
            return $value;
        }

        return str_replace('__GHOST_URL__', '', $value);
    }

    private function findExportFile(): ?string
    {
        $files = glob(base_path('ghost-nice/sysadmin-journal.ghost.*.json'));

        return $files ? $files[0] : null;
    }
}
