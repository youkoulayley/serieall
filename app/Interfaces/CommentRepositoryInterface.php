<?php

namespace App\Interfaces;

use App\Models\Comment;

/**
 * RateRepositoryInterface implements all methods to interact with Rates.
 */
interface CommentRepositoryInterface
{
    /**
     * @param $user_id
     * @param $type
     * @param $type_id
     *
     * @return mixed
     */
    public function getCommentByUserIDTypeTypeID($user_id, $type, $type_id);

    /**
     * @param $type
     * @param $type_id
     * @param $user_comment_id
     *
     * @return mixed
     */
    public function getLastTwoCommentsByTypeTypeID($type, $type_id, $user_comment_id);

    /**
     * @param $user_id
     * @param $object
     * @param $object_id
     *
     * @return mixed
     */
    public function getCommentsForFiche($user_id, $object, $object_id);

    /**
     * @param $showId
     *
     * @return mixed
     */
    public function getCommentCountByTypeForShow($showId);

    /**
     * @param $object
     * @param $object_id
     * @param $order
     *
     * @return mixed
     */
    public function getAllCommentsByTypeTypeID($object, $object_id, $order);

    /**
     * @param $object
     * @param $object_id
     * @param $order
     *
     * @return mixed
     */
    public function getAllCommentsByTypeTypeIDAdmin($object, $object_id, $order);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getCommentByID($id);

    /**
     * @param $user_id
     *
     * @return mixed
     */
    public function getCommentByUserIDThumbNotNull($user_id);

    /**
     * @param $user_id
     *
     * @return mixed
     */
    public function countCommentByUserIDThumbNotNull($user_id);

    /**
     * @param $articleId
     *
     * @return mixed
     */
    public function getCommentCountForArticle($articleId);

    /**
     * @param $user_id
     *
     * @return mixed
     */
    public function getCommentsByUserID($user_id);

    /**
     * @param $user_id
     * @param $name_page
     * @param $filter
     * @param $tri
     *
     * @return mixed
     */
    public function getCommentsShowForProfile($user_id, $name_page, $filter, $tri);

    /**
     * @param $user_id
     * @param $name_page
     * @param $filter
     * @param $tri
     *
     * @return mixed
     */
    public function getCommentsSeasonForProfile($user_id, $name_page, $filter, $tri);

    /**
     * @param $user_id
     * @param $name_page
     * @param $filter
     * @param $tri
     *
     * @return mixed
     */
    public function getCommentsEpisodeForProfile($user_id, $name_page, $filter, $tri);

    /**
     * @param $limit
     *
     * @return mixed
     */
    public function getLastComments($limit);
}
