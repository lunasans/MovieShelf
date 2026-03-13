@foreach ($movies as $movie)
    <template x-if="viewMode === 'grid'">
        @include('movies.partials.grid-item', ['movie' => $movie])
    </template>
    <template x-if="viewMode === 'list'">
        @include('movies.partials.list-item', ['movie' => $movie])
    </template>
@endforeach
