@extends('layouts.admin')

@section('breadcrumbs')
    <a href="{{ route('adminIndex') }}" class="section">
        Administration
    </a>
    <i class="right angle icon divider"></i>
    <a href="{{ route('adminShow.index') }}" class="section">
        Séries
    </a>
    <i class="right angle icon divider"></i>
    <div class="active section">
        Ajouter une série manuellement
    </div>
@endsection

@section('content')
    <h1 class="ui header" id="admin-titre">
        Ajouter une série manuellement
        <div class="sub header">
            Remplir le formulaire ci-dessous pour ajouter une nouvelle série
        </div>
    </h1>

    <form class="ui form" method="POST" action="{{ route('adminShow.storeManually') }}">
        {{ csrf_field() }}

        <div class="ui centered grid">
            <div class="ten wide column segment">
                <div class="ui pointing secondary menu">
                    <a class="item active" data-tab="first">Série</a>
                    <a class="item" data-tab="second">Acteurs</a>
                    <a class="item" data-tab="third">Saisons & épisodes</a>
                    <a class="item" data-tab="fourth">Rentrée</a>
                </div>
                <div class="ui tab active" data-tab="first">
                    <div class="ui teal segment">
                        <h4 class="ui dividing header">Informations générales sur la série</h4>
                        <div class="two fields">
                            <div class="field {{ $errors->has('name') ? ' error' : '' }}">
                                <label>Nom original de la série</label>
                                <input name="name" placeholder="Nom original de la série" type="text" value="{{ old('name') }}">

                                @if ($errors->has('name'))
                                    <div class="ui red message">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </div>
                                @endif
                            </div>

                            <div class="field {{ $errors->has('name_fr') ? ' error' : '' }}">
                                <label>Nom français de la série</label>
                                <input name="name_fr" placeholder="Nom français" type="text" value="{{ old('name_fr') }}">

                                @if ($errors->has('name_fr'))
                                    <div class="ui red message">
                                        <strong>{{ $errors->first('name_fr') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="two fields">
                            <div class="field {{ $errors->has('resume') ? ' error' : '' }}">
                                <label>Résumé</label>
                                <textarea name="resume" value="{{ old('resume') }}"></textarea>

                                @if ($errors->has('resume'))
                                    <div class="ui red message">
                                        <strong>{{ $errors->first('resume') }}</strong>
                                    </div>
                                @endif
                            </div>

                            <div class="two fields field">
                                <div class="field {{ $errors->has('format') ? ' error' : '' }}">
                                    <label>Format</label>
                                    <div class="ui left icon input">
                                        <input name="taux_erectile" placeholder="Format de la série..." type="number" value="{{ old('format') }}">
                                        <i class="tv icon"></i>
                                    </div>

                                    @if ($errors->has('format'))
                                        <div class="ui red message">
                                            <strong>{{ $errors->first('format') }}</strong>
                                        </div>
                                    @endif
                                </div>

                                <div class="field {{ $errors->has('encours') ? ' error' : '' }}">
                                    <label>Série en cours</label>
                                    <div id="dropdown-encours" class="ui fluid search selection dropdown">
                                        <i class="dropdown icon"></i>
                                        <span class="text">Choisir</span>
                                        <div class="menu">
                                            <div class="item">
                                                <i class="checkmark icon"></i>
                                                Oui
                                            </div>
                                            <div class="item">
                                                <i class="remove icon"></i>
                                                Non
                                            </div>
                                        </div>
                                    </div>

                                    @if ($errors->has('encours'))
                                        <div class="ui red message">
                                            <strong>{{ $errors->first('encours') }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="two fields">
                            <div class="field {{ $errors->has('diffusion_us') ? ' error' : '' }}">
                                <label>Date de la diffusion originale</label>
                                <div class="ui calendar" id="datepicker">
                                    <div class="ui input left icon">
                                        <i class="calendar icon"></i>
                                        <input name="diffusion_us" id="date-picker-us" type="date" placeholder="Date" value="{{ old('diffusion_us') }}">
                                    </div>
                                </div>
                                @if ($errors->has('diffusion_us'))
                                    <div class="ui red message">
                                        <strong>{{ $errors->first('diffusion_us') }}</strong>
                                    </div>
                                @endif
                            </div>

                            <div class="field {{ $errors->has('diffusion_fr') ? ' error' : '' }}">
                                <label>Date de la diffusion française</label>
                                <div class="ui calendar" id="datepicker">
                                    <div class="ui input left icon">
                                        <i class="calendar icon"></i>
                                        <input name="diffusion_fr" id="date-picker-fr" type="date" placeholder="Date" value="{{ old('diffusion_fr') }}">
                                    </div>
                                </div>
                                @if ($errors->has('diffusion_fr'))
                                    <div class="ui red message">
                                        <strong>{{ $errors->first('diffusion_fr') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="two fields">
                            <div class="field {{ $errors->has('chaine_fr') ? ' error' : '' }}">
                                <label>Chaine(s)</label>
                                <div id="dropdown-chaines" class="ui fluid multiple search selection dropdown">
                                    <input name="chaine_fr" type="hidden" value="{{ old('chaine_fr') }}">
                                    <i class="dropdown icon"></i>
                                    <div class="default text">Choisir</div>
                                    <div class="menu">
                                        @foreach($channels as $channel)
                                            <div class="item" data-value="{{ $channel->name }}">{{ $channel->name }}</div>
                                        @endforeach
                                    </div>
                                </div>

                                @if ($errors->has('chaine_fr'))
                                    <div class="ui red message">
                                        <strong>{{ $errors->first('chaine_fr') }}</strong>
                                    </div>
                                @endif
                            </div>

                            <div class="field {{ $errors->has('nationalities') ? ' error' : '' }}">
                                <label>Nationalité(s)</label>
                                <div id="dropdown-nationalities" class="ui fluid multiple search selection dropdown">
                                    <input name="nationalities" type="hidden" value="{{ old('nationalities') }}">
                                    <i class="dropdown icon"></i>
                                    <div class="default text">Choisir</div>
                                    <div class="menu">
                                        @foreach($nationalities as $nationality)
                                            <div class="item" data-value="{{ $nationality->name }}">{{ $nationality->name }}</div>
                                        @endforeach
                                    </div>
                                </div>

                                @if ($errors->has('nationalities'))
                                    <div class="ui red message">
                                        <strong>{{ $errors->first('nationalities') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="two fields">
                            <div class="field {{ $errors->has('creators') ? ' error' : '' }}">
                                <label>Créateur(s) de la série</label>
                                <div id="dropdown-creators" class="ui fluid multiple search selection dropdown">
                                    <input name="creators" type="hidden" value="{{ old('creators') }}">
                                    <i class="dropdown icon"></i>
                                    <div class="default text">Choisir</div>
                                    <div class="menu">
                                        @foreach($actors as $actor)
                                            <div class="item" data-value="{{ $actor->name }}">{{ $actor->name }}</div>
                                        @endforeach
                                    </div>
                                </div>

                                @if ($errors->has('creators'))
                                    <div class="ui red message">
                                        <strong>{{ $errors->first('creators') }}</strong>
                                    </div>
                                @endif
                            </div>

                            <div class="field {{ $errors->has('genres') ? ' error' : '' }}">
                                <label>Genre(s)</label>
                                <div id="dropdown-genres" class="ui fluid multiple search selection dropdown">
                                    <input name="genres" type="hidden" value="{{ old('genres') }}">
                                    <i class="dropdown icon"></i>
                                    <div class="default text">Choisir</div>
                                    <div class="menu">
                                        @foreach($genres as $genre)
                                            <div class="item" data-value="{{ $genre->name }}">{{ $genre->name }}</div>
                                        @endforeach
                                    </div>
                                </div>
                                @if ($errors->has('genres'))
                                    <div class="ui red message">
                                        <strong>{{ $errors->first('genres') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <button class="submit positive ui button" type="submit">Créer la série</button>
                </div>

                <div class="ui tab blue segment" data-tab="second">
                    <h4 class="ui dividing header">Ajouter un ou plusieurs acteurs</h4>
                    <p>
                        <button class="ui basic button add-actor">
                            <i class="user icon"></i>
                            Ajouter un acteur
                        </button>
                        <br />
                    </p>

                    <div class="div-actors">

                    </div>

                    <p></p>
                    <button class="submit positive ui button" type="submit">Créer la série</button>
                </div>

                <div class="ui tab red segment" data-tab="third">
                    <h4 class="ui dividing header">Ajouter les saisons et les épisodes</h4>
                    <p>
                        <div class="ui info message">
                            Vous pouvez utiliser l'icone <i class="hashtag icon"></i> pour changer l'ordre des éléments.
                        </div>

                        <button class="ui basic button add-season">
                            <i class="object group icon"></i>
                            Ajouter une saison
                        </button>
                        <br />
                    </p>


                    <div class="ui styled fluid accordion div-seasons sortable-season">

                    </div>



                    <p></p>
                    <button class="submit positive ui button" type="submit">Créer la série</button>
                </div>

                <div class="ui tab violet segment" data-tab="fourth">
                    <h4 class="ui dividing header">Informations sur la rentrée</h4>
                    <div class="two fields">
                        <div class="field {{ $errors->has('taux_erectile') ? ' error' : '' }}">
                            <label>Taux érectile</label>
                            <div class="ui left icon input">
                                <input name="taux_erectile" placeholder="Pourcentage..." type="number" value="{{ old('taux_erectile') }}">
                                <i class="percent icon"></i>
                            </div>

                            @if ($errors->has('taux_erectile'))
                                <div class="ui red message">
                                    <strong>{{ $errors->first('taux_erectile') }}</strong>
                                </div>
                            @endif
                        </div>

                        <div class="field {{ $errors->has('avis_rentree') ? ' error' : '' }}">
                            <label>Avis de la rédaction</label>
                            <textarea name="avis_rentree" value="{{ old('avis_rentree') }}"></textarea>

                            @if ($errors->has('avis_rentree'))
                                <div class="ui red message">
                                    <strong>{{ $errors->first('avis_rentree') }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                    <button class="submit positive ui button" type="submit">Créer la série</button>
                </div>
            </div>
        </div>
    </form>

    @section('scripts')
        <script>
            $('#dropdown-creators')
                    .dropdown({
                        allowAdditions: true,
                        forceSelection : false,
                        minCharacters: 4
                    })
            ;
            $('#dropdown-genres')
                    .dropdown({
                        allowAdditions: true,
                        forceSelection : false
                    })
            ;
            $('#dropdown-chaines')
                    .dropdown({
                        allowAdditions: true,
                        forceSelection : false
                    })
            ;

            $('#dropdown-nationalities')
                    .dropdown({
                        allowAdditions: true,
                        forceSelection : false
                    })
            ;

            $( '#date-picker-us' ).datepicker({
                showAnim: "blind",
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true
            });

            $( '#date-picker-fr' ).datepicker({
                showAnim: "blind",
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true
            });

            $('#dropdown-encours')
                    .dropdown({
                    })
            ;

            $('.menu .item')
                    .tab()
            ;

            $('.ui.styled.fluid.accordion.div-seasons')
                    .accordion({
                        selector: {
                            trigger: '.div-expandable'
                        }
                    })
            ;

            // Fonction de création et de suppression des nouveau acteurs
            $(function(){
                // Définition des variables
                var max_fields  =   50; // Nombre maximums de ligne sautorisées
                var actor_number  =  $('.div-actors').length; // Nombre d'acteurs
                var obj = $(this);

                // Suppression d'un acteur
                $(document).on('click', '.remove-actor', function(){
                    $(this).parents('.div-actor').remove();
                    });

                // Ajouter un acteur
                $('.add-actor').click(function(e) {
                    e.preventDefault();

                    if (actor_number < max_fields) {
                        var html = '<div class="ui segment div-actor">'
                                + '<button class="ui right floated negative basic circular icon button remove-actor">'
                                + '<i class="remove icon"></i>'
                                + '</button>'
                                + '<div class="two fields" id="line' + actor_number + '">'
                                + '<div class="field {{ $errors->has('name') ? ' error' : '' }}">'
                                + '<label class="name-label" for="name' + actor_number + '">Nom de l\'acteur</label>'
                                + '<input class="name-input" name="actors[' + actor_number + '][name]" placeholder="Nom de l\'acteur" type="text" value="{{ old('name') }}">'
                                + '@if ($errors->has('name'))'
                                + '<div class="ui red message">'
                                + '<strong>{{ $errors->first('name') }}</strong>'
                                + '</div>'
                                + '@endif'
                                + '</div>'
                                + '<div class="field {{ $errors->has('role') ? ' error' : '' }}">'
                                + '<label class="role-label" for="role' + actor_number + '">Rôle</label>'
                                + '<input class="role-input" name="actors[' + actor_number + '][role]" placeholder="Role de l\'acteur" type="text" value="{{ old('role') }}">'
                                + '@if ($errors->has('role'))'
                                + '<div class="ui red message">'
                                + '<strong>{{ $errors->first('role') }}</strong>'
                                + '</div>'
                                + '@endif'
                                + '</div>'
                                + '</div>';

                        ++actor_number;

                        $('.div-actors').append(html);
                    }
                });
            });



            // Fonction de Drag 'N Drop pour changer l'ordre des saisons
            $(function(){
                //Définition des variables
                var season_number = $('.div-seasons').length; // Nombre de saisons

                //Suppression d'une saison
                $(document).on('click', '.remove-season', function(){
                    $(this).parents('.div-season').next('.content').remove();
                    $(this).parents('.div-season').remove();

                    $('.sortable-season').find('.div-season').each(function(){
                        // On actualise sa position
                        index = parseInt($(this).index('.div-season')+1);
                        // On la met à jour dans la page
                        $(this).find(".div-expandable").html('<i class="dropdown icon"></i> Saison '+ index);
                        $(this).attr("id", 'lineseason' + index);
                        $(this).next('.div-episodes').attr("id", 'contentseason'+ index);
                        $(this).find(".ba-input").attr( "name", 'seasons[' + index + '][ba]');
                    });

                    --season_number;
                });

                //Ajout d'une saison
                $('.add-season').click(function(e){
                    e.preventDefault();
                    var html = '<div class="title div-season" id="lineseason' + season_number +'">'
                            + '<div class="ui grid">'
                            + '<div class="twelve wide column middle aligned div-expandable">'
                            + '<i class="dropdown icon"></i>'
                            + 'Saison '+ season_number
                            + '</div>'
                            + '<div class="four wide column">'
                            + '<button class="ui right floated negative basic circular icon button remove-season">'
                            + '<i class="remove icon"></i>'
                            + '</button>'
                            + '<button class="ui right floated positive basic circular icon button move-season">'
                            + '<i class="hashtag icon"></i>'
                            + '</button>'
                            + '</div>'
                            + '</div>'
                            + '</div>'
                            + '<div class="content" numero_saison=' + season_number + ' id="contentseason'+ season_number +'">'
                            + '<div class="field {{ $errors->has('ba') ? ' error' : '' }}">'
                            + '<label>Bande Annonce</label>'
                            + '<input class="ba-input" name="seasons[' + season_number + '][ba]" placeholder="Bande annonce" type="text" value="{{ old('ba') }}">'
                            + '@if ($errors->has('ba'))'
                            + '<div class="ui red message">'
                            + '<strong>{{ $errors->first('ba') }}</strong>'
                            + '</div>'
                            + '@endif'
                            + '</div>'
                            + '<button class="ui basic button add-episode'+ season_number +'">'
                            + '<i class="tv icon"></i>'
                            + 'Ajouter un épisode'
                            + '</button>'
                            + '<div class="accordion transition hidden div-episodes">'
                            + '</div>'
                            + '</div>';

                    ++season_number;

                    $('.div-seasons').append(html);


                    // Fonction de Drag 'N Drop pour changer l'ordre des épisodes
                    $(function(){
                        var episode_number =  $(this).next('.div-episode').length; // Nombre d'épisode total
                        var season_number = $(this).next('.content').attr('numero_saison');
                        console.log(season_number);

                        //Ajout d'un episode
                        $('.add-episode' + season_number).click(function(e){

                            console.log($(this));
                            e.preventDefault();

                            var number_season_parent = $(this).parents('.content').attr('numero_saison');

                            var html = '<div class="title div-episode" id="lineepisode' + episode_number +'">'
                                    + '<div class="ui grid">'
                                    + '<div class="twelve wide column middle aligned div-expandable">'
                                    + '<i class="dropdown icon"></i>'
                                    + 'Episode '+ number_season_parent + 'x' + episode_number
                                    + '</div>'
                                    + '<div class="four wide column">'
                                    + '<button class="ui right floated negative basic circular icon button remove-season">'
                                    + '<i class="remove icon"></i>'
                                    + '</button>'
                                    + '<button class="ui right floated positive basic circular icon button move-season">'
                                    + '<i class="hashtag icon"></i>'
                                    + '</button>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '<div class="content" id="contentepisode'+ episode_number +'">'
                                    + '<div class="field {{ $errors->has('ba') ? ' error' : '' }}">'
                                    + '<label>Bande Annonce</label>'
                                    + '<input class="ba-input" name="episodes[' + episode_number + '][ba]" placeholder="Bande annonce" type="text" value="{{ old('ba') }}">'
                                    + '@if ($errors->has('ba'))'
                                    + '<div class="ui red message">'
                                    + '<strong>{{ $errors->first('ba') }}</strong>'
                                    + '</div>'
                                    + '@endif'
                                    + '</div>'
                                    + '</div>';

                            ++ episode_number;
                            console.log(episode_number);

                            $(this).next('.div-episodes').append(html);
                        });
                    });

                });
            });

            // Submission
            $(document).on('submit', 'form', function(e) {
                e.preventDefault();

                $('.submit').addClass("loading");

                $.ajax({
                    method: $(this).attr('method'),
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    dataType: "json"
                })
                        .done(function (data) {
                            window.location.href = '{!! url('/adminShow') !!}';
                        })
                        .fail(function (data) {
                            $('.submit').removeClass("loading");
                            $.each(data.responseJSON, function (key, value) {
                                if (key == 'name') {
                                    $('.help-block').eq(0).text(value);
                                    $('.form-group').eq(0).addClass('has-error');
                                }
                            });
                        });
            });
        </script>
    @endsection
@endsection