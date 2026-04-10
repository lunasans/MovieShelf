@foreach ($movies as $movie)
    @include('tenant.movies.partials.streaming-grid-item', ['movie' => $movie])
@endforeach
