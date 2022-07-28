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
                <a class="item" href="{{ route('user.profile.comments', $data["user"]->user_url ) }}">
                    <i class="comment icon"></i>
                    Avis
                </a>
                <a class="active item">
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
                        <a class="item" href="{{ route('user.profile.parameters', $data["user"]->user_url ) }}">
                            <i class="settings icon"></i>
                            Paramètres
                        </a>
                    @endif
                @endif
            </div>
        </div>

        <div class="ui segment">
            <div class="ui items">
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
                        </div>
                        <div class="ui statistic">
                            <div class="label">
                                <i class="tv icon"></i>
                                {{ $data["watchTime"] }} devant l'écran
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

            @if(!empty($data["user"]->facebook) || !empty($data["user"]->twitter) || !empty($data["user"]->website))
                <h3>Ses liens :</h3>
                @if(!empty($data["user"]->facebook))
                    <button class="ui facebook button" onclick="window.location.href='https://www.facebook.com/{{ $data["user"]->facebook }}'">
                        <i class="facebook icon"></i>
                        Facebook
                    </button>
                @endif

                @if(!empty($data["user"]->twitter))
                    <button class="ui twitter button" onclick="window.location.href='https://www.twitter.com/{{ $data["user"]->twitter }}'">
                        <i class="twitter icon"></i>
                        Twitter
                    </button>
                @endif

                @if(!empty($data["user"]->website))
                    <button class="ui grey button" onclick="window.location.href='{{ $data["user"]->website }}'">
                        <i class="at icon"></i>
                        Site Internet
                    </button>
                @endif
            @endif
        </div>
        <div class="ui segment">
            <h1 class="ui header t-darkBlueSA">
                Séries en cours
                <span class="sub header">
                    Ce sont les séries en cours de visionnage, qu'elles soient terminées ou non. Elles apparaissent dans votre planning personnalisé.
                </span>
            </h1>

            <div id="InProgressBox">
                @include('users.shows_cards', ['shows' => $data["followedShows"]["inProgress"]])
            </div>

            <p></p>

            @if(Auth::Check() && Auth::user()->user_url == $data["user"]->user_url)
                <form id="InProgressForm" class="ui form" method="POST" action="{{ route('user.followshow') }}">
                    {{ csrf_field() }}

                    <input name="state" type="hidden" value="1">

                    <div class="two fields">
                        <div class="ui fluid multiple search selection dropdown" id="InProgressDropdown">
                            <input name="shows" type="hidden">
                            <i class="dropdown icon"></i>
                            <div class="default text">Choisir une ou plusieurs séries</div>
                            <div class="menu">
                            </div>
                        </div>

                        <button type="submit" class="positive ui button">
                            Ajouter
                        </button>
                    </div>
                </form>
                <div id="InProgressMessage" class="ui orange message hidden"></div>
            @endif

            <h1 class="ui header t-darkBlueSA">
                Séries en pause
                <span class="sub header">
                    Ce sont les séries que vous suivez et qui vont reprendre prochainement.
                </span>
            </h1>

            <div id="OnBreakBox">
                @include('users.shows_cards', ['shows' => $data["followedShows"]["onBreak"]])
            </div>

            <p></p>

            @if(Auth::Check() && Auth::user()->user_url == $data["user"]->user_url)
                <form id="OnBreakForm" class="ui form" method="POST" action="{{ route('user.followshow') }}">
                    {{ csrf_field() }}

                    <input name="state" type="hidden" value="2">

                    <div class="two fields">
                        <div class="ui fluid multiple search selection dropdown" id="OnBreakDropdown">
                            <input name="shows" type="hidden">
                            <i class="dropdown icon"></i>
                            <div class="default text">Choisir une ou plusieurs séries</div>
                            <div class="menu">
                            </div>
                        </div>

                        <button type="submit" class="positive ui button">
                            Ajouter
                        </button>
                    </div>
                </form>
                <div id="OnBreakMessage" class="ui orange message hidden"></div>
            @endif

            <h1 class="ui header t-darkBlueSA">
                Séries terminées
                <span class="sub header">
                    Ce sont les séries terminées que vous avez regardées entièrement.
                </span>
            </h1>

            <div id="CompletedBox">
                @include('users.shows_cards', ['shows' => $data["followedShows"]["completed"]])
            </div>

            <p></p>

            @if(Auth::Check() && Auth::user()->user_url == $data["user"]->user_url)
                <form id="CompletedForm" class="ui form" method="POST" action="{{ route('user.followshow') }}">
                    {{ csrf_field() }}

                    <input name="state" type="hidden" value="3">

                    <div class="two fields">
                        <div class="ui fluid multiple search selection dropdown" id="CompletedDropdown">
                            <input name="shows" type="hidden">
                            <i class="dropdown icon"></i>
                            <div class="default text">Choisir une ou plusieurs séries</div>
                            <div class="menu">
                            </div>
                        </div>

                        <button type="submit" class="positive ui button">
                            Ajouter
                        </button>
                    </div>
                </form>
                <div id="CompletedMessage" class="ui orange message hidden"></div>
            @endif

            <h1 class="ui header t-darkBlueSA">
                Séries à voir
                <span class="sub header">
                     Ce sont les séries que vous avez prévu de regarder prochainement.
                </span>
            </h1>

            <div id="ToSeeBox">
                @include('users.shows_cards', ['shows' => $data["followedShows"]["toSee"]])
            </div>

            <p></p>

            @if(Auth::Check() && Auth::user()->user_url == $data["user"]->user_url)
                <form id="ToSeeForm" class="ui form" method="POST" action="{{ route('user.followshow') }}">
                    {{ csrf_field() }}

                    <input name="state" type="hidden" value="5">

                    <div class="two fields">
                        <div class="ui fluid multiple search selection dropdown" id="ToSeeDropdown">
                            <input name="shows" type="hidden">
                            <i class="dropdown icon"></i>
                            <div class="default text">Choisir une ou plusieurs séries</div>
                            <div class="menu">
                            </div>
                        </div>

                        <button type="submit" class="positive ui button">
                            Ajouter
                        </button>
                    </div>
                </form>
                <div id="ToSeeMessage" class="ui orange message hidden"></div>
            @endif

            <h1 class="ui header t-darkBlueSA">
                Séries abandonnées
                <span class="sub header">
                      Ce sont les séries que vous avez abandonnées avant la fin. Expliquez-nous la raison.
                </span>
            </h1>

            <div id="AbandonedBox">
                @include('users.shows_abandoned_cards', ['shows' => $data["followedShows"]["abandoned"]])
            </div>

            <p></p>

            @if(Auth::Check() && Auth::user()->user_url == $data["user"]->user_url)
                <form id="AbandonedForm" class="ui form" method="POST" action="{{ route('user.followshow') }}">
                    {{ csrf_field() }}

                    <input name="state" type="hidden" value="4">

                    <div class="two fields">
                        <div class="required field">
                            <div class="ui fluid search selection dropdown" id="AbandonedDropdown">
                                <input name="shows" type="hidden" required>
                                <i class="dropdown icon"></i>
                                <div class="default text">Choisir une ou plusieurs séries</div>
                                <div class="menu">
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <textarea name="message" rows="1"></textarea>
                        </div>

                        <button type="submit" class="positive ui button">
                            Ajouter
                        </button>
                    </div>
                </form>
                <div id="AbandonedMessage" class="ui orange message hidden"></div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    {{ Html::script('/js/views/users/shows.js') }}
@endpush