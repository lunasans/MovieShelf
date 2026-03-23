<x-app-layout>
    @include('movies.partials.stats')

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.js" integrity="sha256-x+LL+wNI+ZAd7MSX8xbB/qhCAgm2EEEv+4j9PVFvnTA=" crossorigin="anonymous"></script>
    @endpush
</x-app-layout>
