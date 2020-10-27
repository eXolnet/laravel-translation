@forelse (URL::alternateFullUrls($alternateParametersByLocale ?? []) as $locale => $url)
    <a href="{{ $url }}" lang="{{ $locale }}" hreflang="{{ $locale }}">
        {{ trans('translation::locales.' . $locale, [], $locale) }}
    </a>
@empty
    {{ $empty ?? '' }}
@endforelse
