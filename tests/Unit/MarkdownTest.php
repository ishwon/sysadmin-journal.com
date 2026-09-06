<?php

use App\Support\Markdown;

it('turns a titled image into a figure with a caption', function () {
    expect(Markdown::convert('![Alt](/a.jpg "Cap")'))
        ->toBe("<figure><img src=\"/a.jpg\" alt=\"Alt\" /><figcaption>Cap</figcaption></figure>\n");
});

it('leaves an image without a title as a plain paragraph', function () {
    expect(Markdown::convert('![Alt](/a.jpg)'))->toBe("<p><img src=\"/a.jpg\" alt=\"Alt\" /></p>\n");
});
