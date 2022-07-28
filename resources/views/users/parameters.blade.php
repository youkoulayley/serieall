@extends('layouts.app')

@section('pageTitle', 'Paramètres')

@section('content')
    <div class="ui eight wide column">

        <div class="ui center aligned">
            <div class="ui stackable compact pointing menu">
                <a class="item" href="{{ route('user.profile', $user->user_url) }}">
                    <i class="user icon"></i>
                    Profil
                </a>
                <a class="item" href="{{ route('user.profile.rates', $user->user_url ) }}">
                    <i class="star icon"></i>
                    Notes
                </a>
                <a class="item" href="{{ route('user.profile.comments', $user->user_url ) }}">
                    <i class="comment icon"></i>
                    Avis
                </a>
                <a class="item" href="{{ route('user.profile.shows', $user->user_url ) }}">
                    <i class="tv icon"></i>
                    Séries
                </a>
                <a class="item" href="{{ route('user.profile.ranking', $user->user_url ) }}">
                    <i class="ordered list icon"></i>
                    Classement
                </a>
                @if($user->username == Auth::user()->username)
                    <a class="item" href="{{ route('user.profile.planning', $user->user_url ) }}">
                        <i class="calendar icon"></i>
                        Mon planning
                    </a>
                    <a class="item" href="{{ route('user.profile.notifications', $user->user_url ) }}">
                        <i class="alarm icon"></i>
                        Notifications
                    </a>
                    <a class="active item">
                        <i class="settings icon"></i>
                        Paramètres
                    </a>
                @endif
            </div>
        </div>

        <div class="ui message">
            <div class="header">
                Changement d'avatar
            </div>
            <p>
                Série All utilise le système <a href="https://fr.gravatar.com/">Gravatar</a> pour les avatars de ses utilisateurs. <a href="https://fr.gravatar.com/">Gravatar</a> est un site qui permet d'associer votre avatar à votre adresse mail.<br />
                <br/>
                Ainsi sur n'importe quel site qui utilise <a href="https://fr.gravatar.com/">Gravatar</a>, il suffira de lui indiquer votre adresse mail afin qu'il affiche votre avatar. <br />
                <ul class="list">
                <li>Rendez-vous sur <a href="https://fr.gravatar.com/site/signup">cette page</a> pour créer votre compte Gravatar</li>
                    <li>Complétez l'adresse mail associée à votre gravatar dans votre profil</li>
                    <li>C'est tout !</li>
                </ul>
            </p>
        </div>

        <div class="ui segment">
            <h1 class="d-center">Modification de vos informations personnelles</h1>
            <br />

            <form class="ui form" method="POST" action="{{ route('user.profile.changeInfo') }}">
                {{ csrf_field() }}

                <input name="id" type="hidden" value="{{ $user->id }}">

                <div class="ui two fields">
                    <div class="ui required field {{ $errors->has('email') ? ' error' : '' }}">
                        <label>Adresse E-mail</label>
                        <input name="email" placeholder="Adresse e-mail" type="email" value="{{ $user->email }}">

                        @if ($errors->has('email'))
                            <div class="ui red message">
                                <strong>{{ $errors->first('email') }}</strong>
                            </div>
                        @endif
                    </div>

                    <div class="ui required field {{ $errors->has('antispoiler') ? ' error' : '' }}">
                        <label>Antispoiler</label>
                        <div id="dropdown-antispoiler" class="ui fluid search selection dropdown">
                            <input name="antispoiler" type="hidden" value="{{ $user->antispoiler }}">
                            <i class="dropdown icon"></i>
                            <span class="text">Choisir</span>
                            <div class="menu">
                                <div class="item" data-value="1">
                                    <i class="checkmark icon"></i>
                                    Oui
                                </div>
                                <div class="item" data-value="0">
                                    <i class="remove icon"></i>
                                    Non
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('antispoiler'))
                            <div class="ui red message">
                                <strong>{{ $errors->first('antispoiler') }}</strong>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="ui two fields">
                    <div class="ui field {{ $errors->has('twitter') ? ' error' : '' }}">
                        <label for="twitter">Twitter</label>
                        <input id="twitter" name="twitter" value="{{ $user->twitter }}">

                        @if ($errors->has('twitter'))
                            <div class="ui red message">
                                <strong>{{ $errors->first('twitter') }}</strong>
                            </div>
                        @endif
                    </div>

                    <div class="ui field {{ $errors->has('facebook') ? ' error' : '' }}">
                        <label for="facebook">Facebook</label>
                        <input id="facebook" name="facebook" value="{{ $user->facebook }}">

                        @if ($errors->has('facebook'))
                            <div class="ui red message">
                                <strong>{{ $errors->first('facebook') }}</strong>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="ui field {{ $errors->has('website') ? ' error' : '' }}">
                    <label for="website">Site Internet</label>
                    <input id="website" name="website" value="{{ $user->website }}">

                    @if ($errors->has('website'))
                        <div class="ui red message">
                            <strong>{{ $errors->first('website') }}</strong>
                        </div>
                    @endif
                </div>

                <div class="ui field {{ $errors->has('edito') ? ' error' : '' }}">
                    <label for="edito">Edito</label>
                    <textarea id="edito" name="edito">{{ $user->edito }}</textarea>
                    @if ($errors->has('edito'))
                        <div class="ui red message">
                            <strong>{{ $errors->first('edito') }}</strong>
                        </div>
                    @endif
                </div>

                <div class="d-center">
                    <button class="positive ui button">Valider</button>
                </div>
            </form>
        </div>

        <div class="ui segment">
            <h1 class="d-center">Modification de votre mot de passe</h1>
            <br />

            <form class="ui form" method="POST" action="{{ route('user.profile.changePassword') }}">
                {{ csrf_field() }}

                <div class="ui required field {{ $errors->has('password') ? ' error' : '' }}">
                    <label>Ancien mot de passe</label>
                    <input name="password" placeholder="Mot de passe" type="password" value="{{ old('password') }}">

                    @if ($errors->has('password'))
                        <div class="ui red message">
                            <strong>{{ $errors->first('password') }}</strong>
                        </div>
                    @endif
                </div>

                <div class="ui two fields">
                    <div class="ui required field {{ $errors->has('new_password') ? ' error' : '' }}">
                        <label>Nouveau mot de passe</label>
                        <input name="new_password" placeholder="Mot de passe" type="password" value="{{ old('new_password') }}">

                        @if ($errors->has('new_password'))
                            <div class="ui red message">
                                <strong>{{ $errors->first('new_password') }}</strong>
                            </div>
                        @endif
                    </div>

                    <div class="ui required field {{ $errors->has('new_password_confirmation') ? ' error' : '' }}">
                        <label>Confirmer le nouveau mot de passe</label>
                        <input name="new_password_confirmation" placeholder="Mot de passe" type="password" value="{{ old('new_password_confirmation') }}">

                        @if ($errors->has('new_password_confirmation'))
                            <div class="ui red message">
                                <strong>{{ $errors->first('new_password_confirmation') }}</strong>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-center">
                    <button class="positive ui button">Valider</button>
                </div>
            </form>
        </div>
    </div>
@endsection
