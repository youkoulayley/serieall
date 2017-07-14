@extends('layouts.admin')

@section('breadcrumbs')
    <a href="{{ route('admin') }}" class="section">
        Administration
    </a>
    <i class="right angle icon divider"></i>
    <a href="{{ route('admin.shows.index') }}" class="section">
        Séries
    </a>
    <i class="right angle icon divider"></i>
    <a href="{{ route('admin.shows.edit', $season->show->id) }}" class="section">
        {{ $season->show->name }}
    </a>
    <i class="right angle icon divider"></i>
    <a href="{{ route('admin.seasons.edit', $season->id) }}" class="section">
        Saison {{ $season->name }}
    </a>
    <i class="right angle icon divider"></i>
    <div class="active section">
        Ajouter de nouveaux épisodes
    </div>
@endsection

@section('content')
    <h1 class="ui header" id="admin-titre">
        Ajouter de nouveaux épisodes
        <span class="sub header">
            Ajouter de nouveaux épisodes dans la saison {{ $season->name }} de "{{ $season->show->name }}"
        </span>
    </h1>

    <div class="ui centered grid">
        <div class="fifteen wide column segment">
            <div class="ui segment">
                <p>
                    <button class="ui basic button add-episode">
                        <i class="tv icon"></i>
                        Ajouter un épisode
                    </button>
                    <br />
                </p>

                <form class="ui form" action="{{ route('admin.episodes.store', $season->id) }}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}

                    <input type="hidden" name="season_id" value="{{ $season->id }}">

                    <div class="ui styled fluid accordion">
                        <div class="div-episodes">

                        </div>
                    </div>

                    <p></p>
                    <button class="submit positive ui button" type="submit">Envoyer</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $('.ui.styled.fluid.accordion')
        .accordion({
            selector: {
                trigger: '.title'
            },
            exclusive: false
        })
    ;

    $(function () {
        var episodeNumber  =  $('.div-episodes').length; // Nombre d'épisodes

        //Ajout d'un episode
        $('.add-episode').click(function (e) {
            e.preventDefault();

            var html =  '<div class="title">'
                + '<div class="ui grid">'
                + '<div class="twelve wide column middle aligned">'
                + '<i class="dropdown icon"></i>'
                + 'Episode à ajouter n°' + episodeNumber
                + '</div>'
                + '<div class="four wide column">'
                + '<button class="ui right floated negative basic circular icon button episodeRemove">'
                + '<i class="remove icon"></i>'
                + '</button>'
                + '</div>'
                + '</div>'
                + '</div>'

                + '<div class="content">'
                + '<div class="two fields">'
                + '<div class="field">'
                + '<label>Nom original</label>'
                + '<input class="episodeInputNameEN" id="episodes.' + episodeNumber + '.name" name="[episodes][' + episodeNumber + '][name]" placeholder="Nom original de l\'épisode" type="text" value="{{ old('name') }}">'
                + '<div class="ui red hidden message"></div>'
                + '</div>'
                + '<div class="field">'
                + '<label>Nom français</label>'
                + '<input class="episodeInputNameFR" id="episodes.' + episodeNumber + '.name_fr" name="[episodes][' + episodeNumber + '][name_fr]" placeholder="Nom français de l\'épisode" type="text" value="{{ old('name_fr') }}">'
                + '<div class="ui red hidden message"></div>'
                + '</div>'
                + '</div>'

                + '<div class="two fields">'
                + '<div class="field">'
                + '<label>Résumé original</label>'
                + '<textarea class="episodeInputResumeEN" id="episodes.' + episodeNumber + '.resume" name="[episodes][' + episodeNumber + '][resume]" placeholder="Résumé original de l\'épisode" value="{{ old('resume') }}""></textarea>'
                + '<div class="ui red hidden message"></div>'
                + '</div>'
                + '<div class="field">'
                + '<label>Résumé de l\'épisode</label>'
                + '<textarea class="episodeInputResumeFR" id="episodes.' + episodeNumber + '.resume_fr" name="[episodes][' + episodeNumber + '][resume_fr]" placeholder="Résumé en français de l\'épisode" value="{{ old('resume_fr') }}""></textarea>'
                + '<div class="ui red hidden message"></div>'
                + '</div>'
                + '</div>'

                + '<div class="two fields">'
                + '<div class="field">'
                + '<label>Date de la diffusion originale</label>'
                + '<div class="ui calendar" id="datepicker">'
                + '<div class="ui input left icon">'
                + '<i class="calendar icon"></i>'
                + '<input class="episodeInputDiffusionUS date-picker" id="episodes.' + episodeNumber + '.diffusion_us" name="[episodes][' + episodeNumber + '][diffusion_us]" type="date" placeholder="Date" value="{{ old('diffusion_us') }}">'
                + '</div>'
                + '</div>'
                + '<div class="ui red hidden message"></div>'
                + '</div>'
                + '<div class="field">'
                + '<label>Date de la diffusion française</label>'
                + '<div class="ui calendar" id="datepicker">'
                + '<div class="ui input left icon">'
                + '<i class="calendar icon"></i>'
                + '<input class="episodeInputDiffusionFR date-picker" id="episodes.' + episodeNumber + '.diffusion_fr" name="[episodes][' + episodeNumber + '][diffusion_fr]" type="date" placeholder="Date" value="{{ old('diffusion_fr') }}">'
                + '</div>'
                + '</div>'
                + '<div class="ui red hidden message"></div>'
                + '</div>'
                + '</div>'

                + '<div class="two fields">'
                + '<div class="field">'
                + '<label>Particularité</label>'
                + '<textarea rows="2" class="episodeInputParticularite" id="episodes.' + episodeNumber + '.particularite" name="[episodes][' + episodeNumber + '][particularite]" placeholder="Particularité de l\'épisode" value="{{ old('particularite') }}""></textarea>'
                + '<div class="ui red hidden message"></div>'
                + '</div>'
                + '<div class="field">'
                + '<label>Bande annonce de l\'épisode</label>'
                + '<input class="episodeInputBA" id="episodes.' + episodeNumber + '.ba" name="[episodes][' + episodeNumber + '][ba]" type="date" placeholder="Bande Annonce de l\'épisode" value="{{ old('ba') }}">'
                + '<div class="ui red hidden message"></div>'
                + '</div>'
                + '</div>'

                + '<div class="three fields">'

                + '<div class="field">'
                + '<label>Réalisateur(s) de l\'épisode</label>'
                + '<div class="ui fluid multiple search selection dropdown directorDropdown">'
                + '<input class="episodeInputDirectors" id="episodes.' + episodeNumber + '.directors" name="[episodes][' + episodeNumber + '][directors]" type="hidden" value="{{ old('directors') }}">'
                + '<i class="dropdown icon"></i>'
                + '<div class="default text">Choisir</div>'
                + '<div class="menu">'
                + '</div>'
                + '</div>'
                + '<div class="ui red hidden message"></div>'
                + '</div>'

                + '<div class="field">'
                + '<label>Scénariste(s) de l\'épisode</label>'
                + '<div class="ui fluid multiple search selection dropdown writerDropdown">'
                + '<input class="episodeInputWriters" id="episodes.' + episodeNumber + '.writers" name="[episodes][' + episodeNumber + '][writers]" type="hidden" value="{{ old('writers') }}">'
                + '<i class="dropdown icon"></i>'
                + '<div class="default text">Choisir</div>'
                + '<div class="menu">'
                + '</div>'
                + '</div>'
                + '<div class="ui red hidden message"></div>'
                + '</div>'

                + '<div class="field">'
                + '<label>Guest(s) de l\'épisode</label>'
                + '<div class="ui fluid multiple search selection dropdown guestDropdown">'
                + '<input class="episodeInputGuests" id="episodes.' + episodeNumber + '.guests" name="[episodes][' + episodeNumber + '][guests]" type="hidden" value="{{ old('guests') }}">'
                + '<i class="dropdown icon"></i>'
                + '<div class="default text">Choisir</div>'
                + '<div class="menu">'
                + '</div>'
                + '</div>'
                + '<div class="ui red hidden message"></div>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '</div>';

            $(function () {
                $('.date-picker').datepicker({
                    showAnim: "blind",
                    dateFormat: "yy-mm-dd",
                    changeMonth: true,
                    changeYear: true
                });

                $('.guestDropdown')
                    .dropdown({
                        apiSettings: {
                            url: '/api/artists/list?name-lk=*{query}*'
                        },
                        fields: {remoteValues: "data", value: "name"},
                        allowAdditions: true,
                        forceSelection: false,
                        minCharacters: 4
                    });
                $('.writerDropdown')
                    .dropdown({
                        apiSettings: {
                            url: '/api/artists/list?name-lk=*{query}*'
                        },
                        fields: {remoteValues: "data", value: "name"},
                        allowAdditions: true,
                        forceSelection: false,
                        minCharacters: 4
                    });
                $('.directorDropdown')
                    .dropdown({
                        apiSettings: {
                            url: '/api/artists/list?name-lk=*{query}*'
                        },
                        fields: {remoteValues: "data", value: "name"},
                        allowAdditions: true,
                        forceSelection: false,
                        minCharacters: 4
                    });
            });

            ++episodeNumber;

            $('.div-episodes').append(html);

        });
    });
</script>
@endsection