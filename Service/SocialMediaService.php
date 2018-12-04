<?php

namespace FieldInteractive\CitoBundle\Service;

use FieldInteractive\CitoBundle\Exception\FeedNotFoundExceptions;
use FieldInteractive\CitoBundle\Exception\ServiceNotConfiguredException;
use FieldInteractive\CitoBundle\Exception\UserNotFoundException;
use RZ\MixedFeed\Exception\CredentialsException;
use RZ\MixedFeed\Exception\FeedProviderErrorException;
use RZ\MixedFeed\MixedFeed;
use RZ\MixedFeed\InstagramFeed;
use RZ\MixedFeed\TwitterFeed;
use RZ\MixedFeed\FacebookPageFeed;

class SocialMediaService
{
    private $postsPath;

    private $instagram;

    private $twitter;

    private $facebook;

    /**
     * SocialMedia constructor.
     * @param $socialMedia
     * @param $postsPath
     * @throws CredentialsException
     */
    public function __construct($socialMedia, $postsPath)
    {
        $this->postsPath = rtrim($postsPath, '/');

        if (array_key_exists('facebook', $socialMedia)) {
            foreach ($socialMedia['facebook'] as $user => $options) {
                $this->facebook[$user] = new FacebookPageFeed($options['pageId'], $options['accessToken']);
            }
        }

        if (array_key_exists('twitter', $socialMedia)) {
            foreach ($socialMedia['twitter'] as $user => $options) {
                $this->twitter[$user] = new TwitterFeed($options['userId'], $options['consumerKey'], $options['consumerSecret'], $options['accessToken'], $options['accessTokenSecret']);
            }
        }

        if (array_key_exists('instagram', $socialMedia)) {
            foreach ($socialMedia['instagram'] as $user => $options) {
                $this->instagram[$user] = new InstagramFeed($options['userId'], $options['accessToken']);
            }
        }
    }

    /**
     * @param string $socialMedia
     * @return mixed
     */
    public function getConfig(string $socialMedia)
    {
        return $this->$socialMedia;
    }

    /**
     * @return string
     */
    public function getPostsPath()
    {
        return $this->postsPath;
    }

    /**
     * @param $postsPath
     */
    public function setPostsPath($postsPath)
    {
        $this->postsPath = $postsPath;
    }

    /**
     * @param null|string $user
     * @param int $count
     * @return array
     * @throws FeedProviderErrorException
     * @throws ServiceNotConfiguredException
     * @throws UserNotFoundException
     */
    public function downloadFacebookFeed($user = null, $count = 10)
    {
        if (empty($this->facebook)) {
            throw new ServiceNotConfiguredException('Facebook is not configured!');
        }

        $fb = $this->facebook;
        if (!empty($user)) {
            if (array_key_exists($user, $this->facebook)) {
                unset($fb);
                $fb = array(
                    $user => $this->facebook[$user]
                );
            } else {
                throw new UserNotFoundException("The user $user was not found.");
            }
        }

        $posts = [];
        /**
         * @var $feed FacebookPageFeed
         */
        foreach ($fb as $name => $feed) {
            $posts[$name] = $feed->getItems($count);
            // save feed
            foreach ($posts[$name] as $post) {
                $item = (array)$post;
                $path = $this->getPostsPath().'/facebook/'.$name.'/'.$item['id'];
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                file_put_contents($path.'/item.json', json_encode($item, JSON_PRETTY_PRINT));
                file_put_contents($path.'/picture.jpg', fopen($item['picture'], 'r'));
                file_put_contents($path.'/picture_full.jpg', fopen($item['full_picture'], 'r'));
            }
        }

        return $posts;
    }

    /**
     * @param string $user
     * @param int $count
     * @param int $offset
     * @return array
     * @throws FeedNotFoundExceptions
     */
    public function loadFacebookFeed(string $user, int $count = 10, int $offset = 0)
    {
        $path = $this->getPostsPath().'/facebook/'.$user.'/';
        if (!is_dir($path)) {
            throw new FeedNotFoundExceptions("Feed for user $user not found. Looked into $path. Maybe you forget to download the feed.");
        }

        $dir = array_diff(scandir($path, 1), ['.', '..']);;
        $posts = [];
        for($i = $offset; $i < ($count + $offset); $i++) {
            $json = json_decode(file_get_contents($path.$dir[$i].'/item.json'), true);
            $json['picture'] = $path.$dir[$i].'/picture.jpg';
            $json['full_picture'] = $path.$dir[$i].'/picture_full.jpg';
            $posts[$dir[$i]] =  $json;
        }

        return $posts;
    }

    /**
     * @param null $user
     * @param int $count
     * @return array
     * @throws FeedProviderErrorException
     * @throws ServiceNotConfiguredException
     * @throws UserNotFoundException
     */
    public function downloadTwitterFeed($user = null, $count = 10)
    {
        if (empty($this->twitter)) {
            throw new ServiceNotConfiguredException('Twitter is not configured!');
        }

        $tw = $this->twitter;
        if (!empty($user)) {
            if (array_key_exists($user, $this->twitter)) {
                unset($tw);
                $tw = array();
                $tw[$user] = $this->twitter[$user];
            } else {
                throw new UserNotFoundException("The user $user was not found.");
            }
        }

        $posts = [];
        /**
         * @var $feed TwitterFeed
         */
        foreach ($tw as $name => $feed) {
            $posts[$name] = $feed->getItems($count);
            // save feed
            foreach ($posts[$name] as $post) {
                $item = (array)$post;
                $path = $this->getPostsPath().'/twitter/'.$name.'/'.$item['id'];
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                file_put_contents($path.'/item.json', json_encode($item, JSON_PRETTY_PRINT));
            }
        }

        return $posts;
    }

    /**
     * @param string $user
     * @param int $count
     * @param int $offset
     * @return array
     * @throws FeedNotFoundExceptions
     */
    public function loadTwitterFeed(string $user, int $count = 10, int $offset = 0)
    {
        $path = $this->getPostsPath().'/twitter/'.$user;
        if (!is_dir($path)) {
            throw new FeedNotFoundExceptions("Feed for user $user not found. Maybe you forget to download the feed.");
        }

        $dir = array_diff(scandir($path, 1), ['.', '..']);;
        $posts = [];
        for($i = $offset; $i < ($count + $offset); $i++) {
            $json = json_decode(file_get_contents($path.$dir[$i].'/item.json'), true);
            $posts[$dir[$i]] =  $json;
        }

        return $posts;
    }

    /**
     * @param null $user
     * @param int $count
     * @param bool $save
     * @return array
     * @throws FeedProviderErrorException
     * @throws ServiceNotConfiguredException
     * @throws UserNotFoundException
     */
    public function downloadInstagramFeed($user = null, $count = 10, $save = false)
    {
        if (empty($this->instagram)) {
            throw new ServiceNotConfiguredException('Instagram is not configured!');
        }

        $ig = $this->instagram;
        if (!empty($user)) {
            if (array_key_exists($user, $this->instagram)) {
                unset($ig);
                $ig = array();
                $ig[$user] = $this->instagram[$user];
            } else {
                throw new UserNotFoundException("The user $user was not found.");
            }
        }

        $posts = [];
        /**
         * @var $feed InstagramFeed
         */
        foreach ($ig as $name => $feed) {
            $posts[$name] = $feed->getItems($count);
            foreach ($posts[$name] as $post) {
                $item = (array)$post;
                $path = $this->getPostsPath().'/instagram/'.$name.'/'.$item['id'];
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                file_put_contents($path.'/item.json', json_encode($item, JSON_PRETTY_PRINT));
            }
        }

        return $posts;
    }

    /**
     * @param string $user
     * @param int $count
     * @param int $offset
     * @return array
     * @throws FeedNotFoundExceptions
     */
    public function loadInstagramFeed(string $user, int $count = 10, int $offset = 0)
    {
        $path = $this->getPostsPath().'/instagram/'.$user;
        if (!is_dir($path)) {
            throw new FeedNotFoundExceptions("Feed for user $user not found. Maybe you forget to download the feed.");
        }

        $dir = array_diff(scandir($path, 1), ['.', '..']);;
        $posts = [];
        for($i = $offset; $i < ($count + $offset); $i++) {
            $json = json_decode(file_get_contents($path.$dir[$i].'/item.json'), true);
            $posts[$dir[$i]] =  $json;
        }

        return $posts;
    }
}
