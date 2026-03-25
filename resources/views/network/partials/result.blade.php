@if(session('result_title'))
    <div class="panel-card mt-4">
        <h4 class="{{ session('result_success') ? 'text-success' : 'text-warning' }}">
            {{ session('result_title') }}
        </h4>

        @if(session('result_output'))
            <pre class="p-3 rounded mt-3" style="background:#0f172a;color:#e2e8f0;white-space:pre-wrap;">{{ session('result_output') }}</pre>
        @endif
    </div>
@endif
