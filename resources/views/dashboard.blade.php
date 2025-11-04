@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.admin-user.actions.index'))

@section('body')

<h3>Sistemas donde estás registrado:</h3>
<ul>
    @forelse($systems as $system)
        <li>
            {{ $system }} -
            <a href="{{ route('central.connect-system', $system) }}">Conectar</a>
        </li>
    @empty
        <li>No estás registrado en ningún sistema adicional.</li>
    @endforelse
</ul>
@endsection
