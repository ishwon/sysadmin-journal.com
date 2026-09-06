{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:media="http://search.yahoo.com/mrss/">
    <channel>
        <title>{{ $title }}</title>
        <link>{{ url('/') }}</link>
        <description>{{ $description }}</description>
        <language>en</language>
        <lastBuildDate>{{ $lastBuildDate->toRssString() }}</lastBuildDate>
        <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />
        <ttl>60</ttl>
@foreach ($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ url('/'.$post->slug) }}</link>
            <guid isPermaLink="true">{{ url('/'.$post->slug) }}</guid>
            <pubDate>{{ $post->published_at->toRssString() }}</pubDate>
@foreach ($post->authors as $author)
            <dc:creator>{{ $author->name }}</dc:creator>
@endforeach
@foreach ($post->tags as $tag)
            <category>{{ $tag->name }}</category>
@endforeach
            <description>{{ $post->plain_excerpt }}</description>
@if ($post->feature_image)
            <media:content url="{{ $post->feature_image }}" medium="image" />
@endif
            <content:encoded><![CDATA[{!! $post->html !!}]]></content:encoded>
        </item>
@endforeach
    </channel>
</rss>
