@extends('brackets/admin-ui::admin.layout.default')

@section('title', 'Acceso a Sistemas - MUVH')

@section('body')

@php
    $navyColor = '#003366';
    $lightBlueAccent = '#007BFF';
    $lightBackground = '#f4f7f9';
    $hoverBackground = '#e0f0ff';
@endphp

<div style="
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    background: #ffffff;
    border: 2px solid {{ $navyColor }};
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    overflow: hidden;
">

    <div style="
        background-color: {{ $navyColor }};
        padding: 30px;
        text-align: center;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    ">
        <h2 style="
            color: #ffffff;
            font-size: 28px;
            font-weight: 600;
            margin: 0;
        ">
            ACCESO A SISTEMAS - MUVH
        </h2>
    </div>

    <div style="padding: 40px;">
        <ul style="
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        ">
            @forelse($systems as $system)
                <li style="width: 100%; max-width: 280px;">
                    <a href="{{ route('central.redirect-system', ['systemName' => $system]) }}"
                       style="
                            display: block;
                            text-align: center;
                            padding: 14px;
                            border: 2px solid {{ $lightBlueAccent }};
                            border-radius: 8px;
                            text-decoration: none;
                            color: {{ $lightBlueAccent }};
                            font-weight: 500;
                            font-size: 18px;
                            background: {{ $hoverBackground }}; /* Asegúrate de que esta línea tenga el símbolo de dólar */
                            transition: background 0.3s ease, transform 0.2s ease;
                            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
                       "
                       onmouseover="this.style.background='{{ $lightBlueAccent }}'; this.style.color='#fff'; this.style.transform='translateY(-4px)';"
                       onmouseout="this.style.background='{{ $hoverBackground }}'; this.style.color='{{ $lightBlueAccent }}'; this.style.transform='translateY(0)';">
                        {{ strtoupper($system) }}
                    </a>
                </li>
            @empty
                <li style="width: 100%; text-align: center; color: #6c757d; font-size: 16px; margin-top: 20px;">
                    ¡Hola! Parece que no estás registrado en ningún sistema actualmente. Por favor, contacta al administrador.
                </li>
            @endforelse
        </ul>
    </div>
</div>

@endsection
