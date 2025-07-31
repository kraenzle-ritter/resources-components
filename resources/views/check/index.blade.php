@extends('resources-components::layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Resources Components Status</h1>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Datenbank Status</h5>
                </div>
                <div class="card-body">
                    @if($dbStatus['exists'])
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> {{ $dbStatus['message'] }}
                        </div>
                    @else
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i> {{ $dbStatus['message'] }}
                        </div>
                        <div class="mt-3">
                            <p>Um die benötigten Tabellen zu erstellen, führen Sie bitte folgende Befehle aus:</p>
                            <pre><code>php artisan vendor:publish --tag=resources-migrations
php artisan migrate</code></pre>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Konfiguration</h5>
                </div>
                <div class="card-body">
                    <p>Konfiguration gefunden: <strong>{{ count($results) }} Provider</strong></p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('resources.check.run-all-tests') }}" class="btn btn-primary">
                            <i class="fas fa-sync me-2"></i> Alle Provider testen
                        </a>
                        <a href="{{ route('resources.check.show-config') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-cog me-2"></i> Konfiguration anzeigen
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Provider Status</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Provider</th>
                            <th>Typ</th>
                            <th>Status</th>
                            <th>Nachricht</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $key => $result)
                            <tr>
                                <td>{{ $key }}</td>
                                <td>{{ config("resources-components.providers.$key.api-type") }}</td>
                                <td>
                                    @if($result['status'] === 'success')
                                        <span class="badge bg-success">Erfolg</span>
                                    @elseif($result['status'] === 'warning')
                                        <span class="badge bg-warning text-dark">Warnung</span>
                                    @else
                                        <span class="badge bg-danger">Fehler</span>
                                    @endif
                                </td>
                                <td>{{ $result['message'] }}</td>
                                <td>
                                    <a href="{{ route('resources.check.provider', ['provider' => $key]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-search me-1"></i> Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Hilfe & Dokumentation</h5>
        </div>
        <div class="card-body">
            <p>Für weitere Informationen über die Resources Components und ihre Konfiguration, siehe:</p>
            <ul>
                <li><a href="https://github.com/kraenzle-ritter/resources-components" target="_blank">GitHub Repository</a></li>
                <li><a href="https://github.com/kraenzle-ritter/resources-components#readme" target="_blank">README</a></li>
                <li><a href="https://github.com/kraenzle-ritter/resources-components/blob/master/config/resources-components.php" target="_blank">Konfigurations-Datei</a></li>
            </ul>
        </div>
    </div>
</div>
@endsection
