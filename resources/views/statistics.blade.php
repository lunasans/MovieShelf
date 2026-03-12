<x-app-layout>
    @include('movies.partials.stats')

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
</x-app-layout>
