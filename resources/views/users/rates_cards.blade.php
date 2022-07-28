@foreach($data["allRates"] as $rate)
    <div class="card">
        <div class="image">
            <img src="{{ chooseImage($rate->show_url, "banner", "333_100") }}" alt="Image illustrative de {{$rate->name}}">
        </div>
        <div class="content">
            <a class="header" href="{{route('show.fiche', $rate->show_url)}}">{{$rate->name}}</a>
        </div>
        <div class="extra">
            Nombre de notes: <b>{{$rate->count_rate}}</b> / Note : <b>{!! affichageNote($rate->avg_rate) !!}</b> <br>
            <i class="tv icon"></i> {{ Carbon\CarbonInterval::fromString($rate->duration . 'm')->cascade()->forHumans() }} devant la série
        </div>
    </div>
@endforeach