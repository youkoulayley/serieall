@extends('layouts.app')

@section('pageTitle', 'Profil de ' . $data["user"]->username)

@section('content')
    <div class="ui ten wide column">
        <div class="ui center aligned">
            <div class="ui stackable compact pointing menu">
                <a class="item" href="{{ route('user.profile', $data["user"]->user_url ) }}">
                    <i class="user icon"></i>
                    Profil
                </a>
                <a class="item" href="{{ route('user.profile.rates', $data["user"]->user_url ) }}">
                    <i class="star icon"></i>
                    Notes
                </a>
                <a class="active item">
                    <i class="comment icon"></i>
                    Avis
                </a>
                <a class="item" href="{{ route('user.profile.shows', $data["user"]->user_url ) }}">
                    <i class="tv icon"></i>
                    Séries
                </a>
                <a class="item" href="{{ route('user.profile.ranking', $data["user"]->user_url ) }}">
                    <i class="ordered list icon"></i>
                    Classement
                </a>
                @if(Auth::check())
                    @if($data["user"]->username == Auth::user()->username)
                        <a class="item" href="{{ route('user.profile.planning', $data["user"]->user_url ) }}">
                            <i class="calendar icon"></i>
                            Mon planning
                        </a>
                        <a class="item" href="{{ route('user.profile.notifications', $data["user"]->user_url ) }}">
                            <i class="alarm icon"></i>
                            Notifications
                        </a>
                        <a class="item" href="{{ route('user.profile.parameters', $data["user"]->username ) }}">
                            <i class="settings icon"></i>
                            Paramètres
                        </a>
                    @endif
                @endif
            </div>
        </div>

        <div class="ui segment">
            <div class="ui grid stackable">
                <div class="eight wide column">
                    <div class="ui items">
                        <div class="item">
                            <span class="ui tiny image">
                                <img src="{{ Gravatar::src($data["user"]->email) }}" alt="Avatar de {{$data["user"]->username}}">
                            </span>
                            <div class="content">
                                <a class="header">{{ $data["user"]->username }}</a><br />
                                {!! roleUser($data["user"]->role) !!}
                                <div class="description">
                                    <p>"<i>{{ $data["user"]->edito }}"</i></p>
                                </div>
                            </div>
                        </div>
                        <div class="ui statistic">
                            <div class="label">
                                <i class="tv icon"></i>
                                {{ $data["watchTime"] }} devant l'écran
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ui center aligned eight wide column">
                    <div class="ui three statistics">
                        <div class="ui statistic">
                            <div class="label">
                                Moyenne
                            </div>
                            <div class="value">
                                {!! affichageNote($data["ratesSummary"]["avgRate"]) !!}
                            </div>
                        </div>
                        <div class="ui statistic">
                            <div class="label">
                                Nombre de notes
                            </div>
                            <div class="value">
                                {{$data["ratesSummary"]["ratesCount"]}}
                            </div>
                        </div>
                        <div class="ui statistic">
                            <div class="label">
                                Nombre d'avis
                            </div>
                            <div class="value">
                                {{$data["commentsSummary"]["count"]}}
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="ui mini three statistics">
                        <div class="statistic">
                            <div class="value">
                                <i class="green smile icon"></i>
                                {{$data["commentsSummary"]["positiveCount"]}}
                            </div>
                            <div class="label">
                                Favorables
                            </div>
                        </div>
                        <div class="statistic">
                            <div class="value">
                                <i class="grey meh icon"></i>
                                {{$data["commentsSummary"]["neutralCount"]}}
                            </div>
                            <div class="label">
                                Neutres
                            </div>
                        </div>
                        <div class="statistic">
                            <div class="value">
                                <i class="red frown icon"></i>
                                {{$data["commentsSummary"]["negativeCount"]}}
                            </div>
                            <div class="label">
                                Défavorables
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="chartMean column">
            {!! $data["chart"]->container() !!}
        </div>

        <div id="segmentShow" class="ui segment">
            <h1>Avis sur les séries</h1>
            @component('components.dropdowns.dropdown_filter_tri')
                filterShow
            @endcomponent
            <div id="cardsShows" class="ui basic segment">
                @include('users.comments_cards', ['data' => ['comments' => $data['comments']['show']]])
            </div>
        </div>

        <div id="segmentSeason" class="ui segment">
            <h1>Avis sur les saisons</h1>
            @component('components.dropdowns.dropdown_filter_tri')
                filterSeason
            @endcomponent
                <div id="cardsSeasons" class="ui basic segment">
                    @include('users.comments_cards', ['data' => ['comments' => $data['comments']['season']]])
                </div>
        </div>

        <div id="segmentEpisode" class="ui segment">
            <h1>Avis sur les épisodes</h1>
            @component('components.dropdowns.dropdown_filter_tri')
                filterEpisode
            @endcomponent
            <div id="cardsEpisodes" class="ui basic segment">
                @include('users.comments_cards', ['data' => ['comments' => $data['comments']['episode']]])
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{Html::script('/js/views/users/comments.js')}}
@endpush
<script src="//cdnjs.cloudflare.com/ajax/libs/highcharts/6.0.6/highcharts.js" charset="utf-8"></script>
{!! $data["chart"]->script() !!}