@foreach (URL::alternateFullUrls($alternateParametersByLocale ?? []) as $locale => $url)
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}" />
@endforeach
