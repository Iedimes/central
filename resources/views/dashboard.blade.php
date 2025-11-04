@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.admin-user.actions.index'))

@section('body')

<h3>Sistemas donde estás registrado:</h3>
<ul>
    @forelse($systems as $system)
        <li>
            <a href="{{ route('central.redirect-system', ['systemName' => $system]) }}">
                Ir a {{ $system }}
            </a>

        </li>
    @empty
        <li>No estás registrado en ningún sistema adicional.</li>
    @endforelse
</ul>
@endsection
