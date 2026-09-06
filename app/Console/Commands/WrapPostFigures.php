<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Support\Markdown;
use Illuminate\Console\Command;

class WrapPostFigures extends Command
{
    protected $signature = 'posts:wrap-figures {--dry-run : List affected posts without saving}';

    protected $description = 'Turn titled images in saved post HTML into figures with captions';

    public function handle(): int
    {
        $count = 0;

        Post::whereNotNull('html')->where('html', 'like', '%title=%')->each(function (Post $post) use (&$count): void {
            $html = Markdown::wrapFigures($post->html);

            if ($html === $post->html) {
                return;
            }

            $this->line("{$post->type} #{$post->id} {$post->slug}");

            if (! $this->option('dry-run')) {
                $post->update(['html' => $html, 'plaintext' => strip_tags($html)]);
            }

            $count++;
        });

        $this->info(($this->option('dry-run') ? 'Would update ' : 'Updated ').$count.' '.str('post')->plural($count).'.');

        return self::SUCCESS;
    }
}
