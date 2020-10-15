@foreach (URL::alternateUrls($alternateParametersByLocale ?? []) as $locale => $url)
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}" />
@endforeach
