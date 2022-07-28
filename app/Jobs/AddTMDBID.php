<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Episode;
use App\Models\Season;
use App\Models\Show;
use App\Packages\TMDB\TMDBController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class AddTMDBID.
 */
class AddTMDBID extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    public function handle()
    {
        $tmdb = new TMDBController(config('tmdb.apiKey'));

        $shows = Show::all();
        foreach ($shows as $show) {
            if ($show->tmdb_id != 0) {
                Log::debug('Show: '.$show->thetvdb_id.' already processed');
                continue;
            }

            if (is_null($show->thetvdb_id)) {
                Log::Error('Show: '.$show->name." doesn't have a tvdb id");
                continue;
            }

            Log::Info('Show: '.'Processing: '.$show->thetvdb_id.' - '.$show->name);

            $tmdb_id = $tmdb->findShow($show->thetvdb_id);

            $show->tmdb_id = $tmdb_id;
            $show->save();
        }

        $seasons = Season::all();
        foreach ($seasons as $season) {
            if ($season->tmdb_id != 0) {
                Log::debug('Season: '.$season->thetvdb_id.' already processed');
                continue;
            }

            if (is_null($season->thetvdb_id)) {
                Log::Error('Season: '.$season->name." doesn't have a tvdb id");
                continue;
            }

            Log::Info('Season: '.'Processing: '.$season->thetvdb_id.' - '.$season->name);

            $tmdb_id = $tmdb->findSeason($season->thetvdb_id);

            $season->tmdb_id = $tmdb_id;
            $season->save();
        }

        $episodes = Episode::all();
        foreach ($episodes as $episode) {
            if ($episode->tmdb_id != 0) {
                Log::debug('Episode: '.$episode->thetvdb_id.' already processed');
                continue;
            }

            if (is_null($episode->thetvdb_id)) {
                Log::Error('Episode: '.$episode->name." doesn't have a tvdb id");
                continue;
            }

            Log::Info('Episode: '.'Processing: '.$episode->thetvdb_id.' - '.$episode->name);

            $tmdb_id = $tmdb->findEpisode($episode->thetvdb_id);

            $episode->tmdb_id = $tmdb_id;
            $episode->save();
        }
    }
}
