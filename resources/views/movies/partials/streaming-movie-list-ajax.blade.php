@foreach ($movies as $movie)
    @include('movies.partials.streaming-grid-item', ['movie' => $movie])
@endforeach
