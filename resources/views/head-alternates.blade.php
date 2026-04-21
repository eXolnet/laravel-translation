@foreach (URL::alternateFullUrls($alternateParametersByLocale ?? [], $queryParameterOptions ?? []) as $locale => $url)
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}" />
@endforeach
