<?php

namespace App\Support;

use League\CommonMark\CommonMarkConverter;

/**
 * Markdown → HTML for posts and pages.
 *
 * Extends CommonMark with one convention: an image with a title
 *   ![alt](/path.jpg "A caption")
 * becomes a <figure> with a <figcaption>, so the caption renders in the
 * mono register directly under the image (see .prose-brand figcaption).
 */
class Markdown
{
    public static function convert(?string $content): ?string
    {
        if (! $content) {
            return null;
        }

        $html = (new CommonMarkConverter)->convert($content)->getContent();

        return static::wrapFigures($html);
    }

    public static function wrapFigures(string $html): string
    {
        return (string) preg_replace_callback(
            '/<p>\s*(<img\b[^>]*\btitle="([^"]*)"[^>]*>)\s*<\/p>/i',
            function (array $m): string {
                $img = preg_replace('/\s*title="[^"]*"/i', '', $m[1]);

                return "<figure>{$img}<figcaption>{$m[2]}</figcaption></figure>";
            },
            $html
        );
    }
}
