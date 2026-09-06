<?php

use App\Models\Post;

it('shows the latest post as featured plus nine more on the first page', function () {
    $posts = collect(range(1, 12))->map(fn (int $i) => Post::factory()->published()->create([
        'title' => "Post number {$i}",
        'published_at' => now()->subDays($i),
    ]));

    $first = $this->get('/')->assertOk();

    foreach (range(1, 10) as $i) {
        $first->assertSee("Post number {$i}");
    }
    $first->assertDontSee('Post number 11');
    expect(substr_count($first->getContent(), 'Post number 1<'))->toBe(1);

    $this->get('/?page=2')->assertOk()
        ->assertSee('Post number 11')
        ->assertSee('Post number 12')
        ->assertDontSee('Post number 1<', false)
        ->assertDontSee('Post number 10');
});
