<?php

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

it('serves an RSS feed of published posts', function () {
    $author = User::factory()->create(['name' => 'Ish Sookun', 'slug' => 'ish']);
    $tag = Tag::factory()->create(['name' => 'Linux', 'slug' => 'linux']);
    $post = Post::factory()->published()->create([
        'title' => 'Hello & welcome',
        'slug' => 'hello-welcome',
        'html' => '<p>Body</p><img src="/content/images/a.jpg">[gallery:photos]',
        'feature_image' => '/content/images/cover.jpg',
    ]);
    $post->authors()->attach($author, ['sort_order' => 0]);
    $post->tags()->attach($tag, ['sort_order' => 0]);

    $response = $this->get('/rss');

    $response->assertOk()->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');

    $xml = $response->getContent();

    expect($xml)
        ->toStartWith('<?xml version="1.0" encoding="UTF-8"?>')
        ->toContain('<title>Hello &amp; welcome</title>')
        ->toContain('<link>'.url('/hello-welcome').'</link>')
        ->toContain('<dc:creator>Ish Sookun</dc:creator>')
        ->toContain('<category>Linux</category>')
        ->toContain('<media:content url="'.url('/content/images/cover.jpg').'" medium="image" />')
        ->toContain('src="'.url('/content/images/a.jpg').'"')
        ->not->toContain('[gallery:photos]');

    expect(simplexml_load_string($xml))->not->toBeFalse();
});

it('excludes drafts, scheduled posts and pages from the feed', function () {
    Post::factory()->create(['title' => 'Draft post']);
    Post::factory()->published()->create(['title' => 'Scheduled post', 'published_at' => now()->addDay()]);
    Post::factory()->published()->page()->create(['title' => 'About page']);

    $this->get('/rss')
        ->assertOk()
        ->assertDontSee('Draft post')
        ->assertDontSee('Scheduled post')
        ->assertDontSee('About page');
});
