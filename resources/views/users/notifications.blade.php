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
                        <a class="active item">
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
                Notifications
            </h1>

            @if(count($unread_notifications) != 0)
                <button class="markAllasRead ui basic button">Tout marquer comme lu</button>
            @endif

            <div class="ui feed">
                @if($data["notifications"]->count() != 0)
                    @foreach($data["notifications"] as $notif)
                        <div class="event">
                        <div class="label">
                            <img src="{{ affichageAvatar($notif->data['user_id']) }}" alt="Avatar de {{ affichageUsername($notif->data['user_id']) }}">
                        </div>
                        <div class="content">
                            <div class="date">{!! formatDate('full', $notif->created_at)!!}</div>
                            <div class="summary">
                                @if(is_null($notif->read_at))
                                    <div class="ui red horizontal label">New</div>
                                @endif
                                    <a href="{{ route('user.profile', affichageUserUrl($notif->data['user_id'])) }}">{{ affichageUsername($notif->data['user_id']) }}</a>
                                    {{ $notif->data['title'] }}
                                |
                                <a class="markAsRead"  id="{{ $notif->id }}" href="{{ $notif->data['url'] }}">
                                    Voir le message
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <p></p>
                    <div class="d-center">
                        {{ $data["notifications"]->links() }}
                    </div>
                @else
                    <div class="ui placeholder segment">
                        <div class="ui icon header">
                            <i class="frown outline icon"></i>
                            Pas encore de notifications. Notez, commentez et échanger avec les autres membres pour remplir cette page !
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function(){
            $('.markAsRead').click(function(e) {
                e.preventDefault();

                link = $(this);

                $.ajax({
                    method: 'post',
                    url: '/notification',
                    data: {'_token': "{{csrf_token()}}", 'notif_id': link.attr('id'), 'markUnread': false},
                    dataType: "json"
                }).done(function () {
                    window.location.href = $(link).attr('href');
                });
            });
        });
    </script>
@endpush
