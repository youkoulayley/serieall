@extends('layouts.app')

@section('pageTitle', 'Profil de ' . $data["user"]->username)

@section('content')
    <div class="ui ten wide column">
        <div class="ui center aligned">
            <div class="ui stackable compact pointing menu">
                <a class="active item">
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

        <div class="ui grid stackable">
            <div class="eight wide column">
                @if($data["user"]->articles->count() > 0)
                    <div id="LeftBlock" class="ui segment profile">
                        <h1>Ses derniers articles</h1>

                        <div class="ui items">
                            @foreach($data["user"]->articles as $article)
                                <div class="article item">
                                    <div class="ol-{{ colorCategory($article->category_id) }} image article">
                                        <img src="{{ chooseImage(pathinfo($article->image)['filename'], "poster", "120_120") }}" alt="">
                                        <p>{{ $article->category->name }}</p>
                                    </div>
                                    <div class="content">
                                        <a href="{{  route('article.show', $article->article_url) }}" class="header">{{ $article->name }}</a>
                                        <div class="meta">
                                            <span>Le {!! formatDate('full', $article->published_at) !!}</span>
                                        </div>
                                        <div class="description">
                                            <p>{{ $article->intro }}</p>
                                        </div>
                                        <div class="extra">
                                            Par
                                            @foreach($article->users as $data["user"])
                                                @if($loop->last)
                                                    <img class="ui avatar image" src="{{ Gravatar::src($data["user"]->email) }}" alt="Avatar de {{$data["user"]->username}}">
                                                    <span>{{ $data["user"]->username }}</span>
                                                @else
                                                    <img class="ui avatar image" src="{{ Gravatar::src($data["user"]->email) }}" alt="Avatar de {{$data["user"]->username}}">
                                                    <span>{{ $data["user"]->username }}</span>,
                                                @endif
                                            @endforeach

                                            <div class="right floated">
                                                <i class="comment icon"></i>
                                                {{ $article->comments_count }}
                                            </div>
                                        </div>
                                     </div>
                                </div>
                                <div class="ui divider"></div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            <div class="eight wide column">
                <div id="RightBlock" class="ui segment profile">
                    <h1>Ses dernières notes</h1>
                    @foreach($data["lastRates"] as $rate)
                        {{ $rate['user']['username'] }} a mis {{ $rate->rate }} à <a href="{{ route('show.fiche', $rate->episode->show['show_url'] ) }}">{{ $rate->episode->show['name'] }}</a>/{!! afficheEpisodeName($rate->episode, true, true) !!} <br/>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
@endsection