@if($data["comments"]->count() > 0)
    @if($data["comments"][0]->commentable_type == 'App\Models\Show')
        <?php $type_comment = "Show"; ?>
    @elseif($data["comments"][0]->commentable_type == 'App\Models\Season')
        <?php $type_comment = "Season"; ?>
    @else
        <?php $type_comment = "Episode"; ?>
    @endif

    <div id="cardsRates" class="ui four stackable cards">
        @foreach($data["comments"] as $comment)
            <div class="card">
                <div class="image">
                    @if($comment->commentable_type == 'App\Models\Show')
                        <img src="{{ chooseImage($comment->commentable->show_url, "banner", "333_100") }}" alt="Image de la série">
                    @else
                    <img src="{{ chooseImage($comment->commentable->show->show_url, "banner", "333_100") }}" alt="Image de la série">
                    @endif
                </div>
                <div class="content">
                    @if($comment->commentable_type == 'App\Models\Show')
                        <a class="header" href="{{route('show.fiche', $comment->commentable->show_url)}}">{{$comment->commentable->name}}</a>
                    @elseif($comment->commentable_type == 'App\Models\Season')
                        <a class="header" href="{{route('season.fiche', [$comment->commentable->show->show_url, $comment->commentable->name])}}"> {{ $comment->commentable->show->name }} Saison {{$comment->commentable->name}}</a>
                    @else
                        <a class="header" href="{{route('episode.fiche', [$comment->commentable->show->show_url, $comment->commentable->season->name, $comment->commentable->numero, $comment->commentable->id])}}"> {{ $comment->commentable->show->name }} / {{ $comment->commentable->season->name }}.{{ sprintf('%02s', $comment->commentable->numero) }} {{ $comment->commentable->name }} </a>
                    @endif
                </div>
                <div class="extra">
                    <b>Avis
                        @if($comment->thumb == 1)
                            <span class="t-green">Favorable</span>
                        @elseif($comment->thumb == 2)
                            <span class="t-grey">Neutre</span>
                        @else
                            <span class="t-red">Défavorable</span>
                        @endif
                    </b><br>
                    <a class="readAvis" modal="{{ $type_comment }}-{{ $comment->id }}"  href="#">Lire l'avis</a>
                </div>
            </div>

            <div id="{{ $type_comment }}-{{ $comment->id }}" class="ui modal">
                <div class="header">L'avis de {{ $comment->user->username }} sur
                    @if($comment->commentable_type == 'App\Models\Show')
                        {{ $comment->commentable->name }}
                    @elseif($comment->commentable_type == 'App\Models\Episode')
                        {{ $comment->commentable->show->name }} / {{ $comment->commentable->season->name }}.{{ sprintf('%02s', $comment->commentable->numero) }} {{ $comment->commentable->name }}
                    @else
                        {{ $comment->commentable->show->name }} / Saison {{ $comment->commentable->name }}
                    @endif </div>
                <div class="content">
                    {!! $comment->message !!}
                </div>
            </div>
        @endforeach
    </div>

    <div id="paginate{{ $type_comment }}" class="ui center aligned p-1">
        {{ $data["comments"]->links() }}
    </div>
@else
    @component('components.message_simple', ['type' => 'info'])
        Pas d'avis trouvé.
    @endcomponent
@endif