@foreach($movies as $movie)
    @include('trailers.partials.movie-card', ['movie' => $movie])
@endforeach
