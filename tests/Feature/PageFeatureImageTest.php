<?php

use App\Models\Post;
use App\Models\User;

it('shows alt text and caption fields for the feature image in the page editor', function () {
    $page = Post::factory()->page()->create(['feature_image_alt' => 'Existing alt', 'feature_image_caption' => 'Existing caption']);

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard.pages.edit', $page))
        ->assertOk()
        ->assertSee('name="feature_image_alt"', false)
        ->assertSee('name="feature_image_caption"', false)
        ->assertSee('Existing alt')
        ->assertSee('Existing caption');
});

it('saves the feature image alt text and caption when updating a page', function () {
    $page = Post::factory()->page()->create();

    $this->actingAs(User::factory()->create())->put(route('dashboard.pages.update', $page), [
        'title' => $page->title,
        'slug' => $page->slug,
        'content' => '<p>Hello</p>',
        'content_format' => 'html',
        'custom_excerpt' => '',
        'feature_image' => '/content/images/photo.jpg',
        'feature_image_alt' => 'Ish speaking on stage',
        'feature_image_caption' => 'Photo by Arwin Neil Baichoo',
        'status' => 'published',
        'meta_title' => '',
        'meta_description' => '',
    ])->assertRedirect(route('dashboard.pages.index'));

    expect($page->fresh())
        ->feature_image->toBe('/content/images/photo.jpg')
        ->feature_image_alt->toBe('Ish speaking on stage')
        ->feature_image_caption->toBe('Photo by Arwin Neil Baichoo');
});
