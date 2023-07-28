@extends('layout')

@section('styles')
    <link rel="stylesheet" href="/css/single-game-style.css">
@endsection

@section('content')
    <div class="container">
        @if ($game->game_image)
        <div class="img-box">
            <img src="{{ asset('images/'.$game->game_image) }}" width="20%">
        </div>
        @endif
        <div class="text-box">
            <h1>Producent: {{ $game->game_producer }}</h1>
            <h2>{{ $game->game_name }}</h2>
            @auth
                <form action="{{ route('users.update-user-games', ['game' => $game->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn">Dodaj do kolekcji</button>
                </form>
            @endauth
            <h2>Wiek graczy: {{ $game->game_players_age }}</h2>
            <h2>Liczba graczy: {{ $game->game_players_num }}</h2>
            <h2>Opis</h2>
            <p>{{ $game->game_desc }}</p>
        </div>

    </div>

@endsection
