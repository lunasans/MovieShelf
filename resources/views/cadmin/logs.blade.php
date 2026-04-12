@extends('cadmin.layout')

@section('title', 'System-Logs - Global ACP')
@section('header_title', 'System-Logs')

@section('content')
<div class="glass rounded-3xl overflow-hidden border border-white/5 shadow-2xl animate-in fade-in zoom-in duration-500" 
     style="height: calc(100vh - 200px);">
    <iframe src="/cadmin/logs-viewer" 
            class="w-full h-full border-none" 
            title="Log Viewer"></iframe>
</div>
@endsection
