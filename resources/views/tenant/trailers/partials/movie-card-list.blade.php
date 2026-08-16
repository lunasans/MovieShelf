@foreach($movies as $movie)
    @include('tenant.trailers.partials.movie-card', ['movie' => $movie])
@endforeach
