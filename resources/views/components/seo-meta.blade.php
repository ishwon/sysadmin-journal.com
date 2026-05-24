@php
    $seoTitle = ($seoTitle ?? null) ?: config('app.name', 'SysAdmin Journal');
    $seoDescription = ($seoDescription ?? null) ?: 'Thoughts, ideas and stories';
    $seoImage = ($seoImage ?? null);
    $seoUrl = ($seoUrl ?? null) ?: url()->current();
    $seoType = ($seoType ?? 'website');
    $twitterTitle = ($twitterTitle ?? null) ?: $seoTitle;
    $twitterDescription = ($twitterDescription ?? null) ?: $seoDescription;
    $twitterImage = ($twitterImage ?? null) ?: $seoImage;
    $canonicalUrl = ($canonicalUrl ?? null) ?: $seoUrl;
@endphp

<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
<link rel="canonical" href="{{ $canonicalUrl }}">

<meta property="og:site_name" content="SysAdmin Journal">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoUrl }}">
@if($seoImage)
<meta property="og:image" content="{{ url($seoImage) }}">
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $twitterTitle }}">
<meta name="twitter:description" content="{{ $twitterDescription }}">
@if($twitterImage)
<meta name="twitter:image" content="{{ url($twitterImage) }}">
@endif

@if(isset($post) && $post->type === 'post')
@php
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post->title,
        'description' => $post->plain_excerpt,
        'url' => url('/' . $post->slug),
        'datePublished' => $post->published_at?->toIso8601String(),
        'dateModified' => $post->updated_at?->toIso8601String(),
        'publisher' => ['@type' => 'Organization', 'name' => 'SysAdmin Journal'],
    ];
    if ($post->feature_image) {
        $jsonLd['image'] = url($post->feature_image);
    }
    if ($post->primaryAuthor()) {
        $jsonLd['author'] = ['@type' => 'Person', 'name' => $post->primaryAuthor()->name];
    }
@endphp
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
@endif
