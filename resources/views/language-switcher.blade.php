@forelse (URL::alternates(isset($alternateParameters) ? $alternateParameters : []) as $locale => $url)
    <a href="{{ $url }}" hreflang="{{ $locale }}">{{$locale}}</a>
@empty
    @foreach (Route::getAlternateLocales() as $locale)
        <a href="{{ route('home.'. $locale) }}" data-locale="{{ $locale }}" hreflang="{{ $locale }}">
            {{$locale}}
        </a>
    @endforeach
@endforelse
