<?php

namespace App\Interfaces;

/**
 * EpisodeRepositoryInterface implements all methods to interact with Episodes.
 */
interface EpisodeRepositoryInterface
{
    /**
     * @param $id
     *
     * @return mixed
     */
    public function getEpisodeByID($id);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getEpisodeWithSeasonShowByID($id);

    /**
     * @param $seasonID
     * @param $episodeNumero
     *
     * @return mixed
     */
    public function getEpisodeByEpisodeNumeroAndSeasonID($seasonID, $episodeNumero);

    /**
     * @param $seasonID
     * @param $episodeNumero
     * @param $episodeID
     *
     * @return mixed
     */
    public function getEpisodeByEpisodeNumeroSeasonIDAndEpisodeID($seasonID, $episodeNumero, $episodeID);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getRatesByEpisodeID($id);

    /**
     * @param $episode_id
     *
     * @return mixed
     */
    public function getEpisodeByIDWithSeasonIDAndShowID($episode_id);

    /**
     * @param $diffusion
     *
     * @return mixed
     */
    public function getEpisodesDiffusion($diffusion);

    /**
     * @param $diffusion
     * @param $date
     *
     * @return mixed
     */
    public function getPlanningHome($diffusion, $date);

    /**
     * @param $order
     *
     * @return mixed
     */
    public function getRankingEpisodes($order);

    /**
     * @param $show
     * @param $order
     *
     * @return mixed
     */
    public function getRankingEpisodesByShow($show, $order);
}
