<?php

use App\Models\Post;
use App\Models\User;

it('renders titled markdown images as captioned figures when saving a post', function () {
    $this->actingAs(User::factory()->create())->post(route('dashboard.posts.store'), [
        'title' => 'Captions',
        'slug' => 'captions',
        'content' => "Intro\n\n![Alt text](/content/images/a.jpg \"A caption\")",
        'content_format' => 'markdown',
        'status' => 'draft',
        'custom_excerpt' => '',
        'feature_image' => '',
        'feature_image_alt' => '',
        'feature_image_caption' => '',
        'meta_title' => '',
        'meta_description' => '',
        'og_image' => '',
        'twitter_image' => '',
        'gallery_id' => '',
    ])->assertRedirect();

    $post = Post::where('slug', 'captions')->firstOrFail();

    expect($post->html)
        ->toContain('<figure><img src="/content/images/a.jpg" alt="Alt text" /><figcaption>A caption</figcaption></figure>')
        ->not->toContain('title=');
});

it('repairs titled images in already saved post HTML', function () {
    $post = Post::factory()->create(['html' => '<p><img src="/a.jpg" alt="A" title="Cap" /></p>']);
    $untouched = Post::factory()->create(['html' => '<p>Hello</p>']);

    $this->artisan('posts:wrap-figures')->expectsOutputToContain('Updated 1 post.')->assertExitCode(0);

    expect($post->fresh()->html)->toBe('<figure><img src="/a.jpg" alt="A" /><figcaption>Cap</figcaption></figure>');
    expect($untouched->fresh()->html)->toBe('<p>Hello</p>');
});
