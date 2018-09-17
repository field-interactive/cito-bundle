<?php

namespace FieldInteractive\CitoBundle\Service;

use RZ\MixedFeed\Exception\CredentialsException;
use RZ\MixedFeed\FacebookPageFeed;
use RZ\MixedFeed\InstagramFeed;
use RZ\MixedFeed\TwitterFeed;

class SocialMedia
{
    private $postsPath;

    private $facebook;

    private $twitter;

    private $instagramm;

    public function __construct($socialMedia, $postsPath)
    {
        $this->postsPath = $postsPath;

        if (array_key_exists('facebook', $socialMedia)) {
            foreach ($socialMedia['facebook'] as $name => $options) {
                try {
                    $this->facebook[$name] = new FacebookPageFeed($options['pageId'], $options['accessToken']);
                } catch (CredentialsException $e) {
                }
            }
        }

        if (array_key_exists('twitter', $socialMedia)) {
            foreach ($socialMedia['twitter'] as $name => $options) {
                try {
                    $this->twitter[$name] = new TwitterFeed($options['userId'], $options['consumerKey'], $options['consumerSecret'], $options['accessToken'], $options['accessTokenSecret']);
                } catch (CredentialsException $e) {
                }
            }
        }

        if (array_key_exists('instagram', $socialMedia)) {
            foreach ($socialMedia['instagram'] as $name => $options) {
                try {
                    $this->instagram[$name] = new InstagramFeed($options['userId'], $options['accessToken']);
                } catch (CredentialsException $e) {
                }
            }
        }
    }

    /**
     * @param null|string $page
     * @param int $count
     * @return array
     * @throws \RZ\MixedFeed\Exception\FeedProviderErrorException
     */
    public function getFacebookPosts($page = null, $count = 10)
    {
        if (empty($this->facebook)) {
            throw new \Exception('Facebook is not configured!');
        }

        $fb = $this->facebook;
        if (!empty($page)) {
            if (array_key_exists($page, $this->facebook)) {
                unset($fb);
                $fb = array();
                $fb[$page] = $this->facebook[$page];
            } else {
                throw new \Exception('User is not configured under facebook!');
            }
        }

        $posts = [];
        /**
         * @var $feed FacebookPageFeed
         */
        foreach ($fb as $name => $feed) {
            $post[$name] = $feed->getItems($count);
        }

        return $posts;
    }

    /**
     * @param null $page
     * @param int $count
     * @return array
     * @throws \Exception
     */
    public function getTwitterTweets($page = null, $count = 10)
    {
        if (empty($this->twitter)) {
            throw new \Exception('Twitter is not configured!');
        }

        $tw = $this->twitter;
        if (!empty($page)) {
            if (array_key_exists($page, $this->twitter)) {
                unset($fb);
                $tw = array();
                $tw[$page] = $this->twitter[$page];
            } else {
                throw new \Exception('User is not configured under twitter!');
            }
        }

        $posts = [];
        /**
         * @var $feed TwitterFeed
         */
        foreach ($tw as $name => $feed) {
            $post[$name] = $feed->getItems($count);
        }

        return $posts;
    }

    /**
     * @param null|string $page
     * @param int $count
     * @return array
     * @throws \RZ\MixedFeed\Exception\FeedProviderErrorException
     */
    public function getInstagramPosts($page = null, $count = 10)
    {
        if (empty($this->instagram)) {
            throw new \Exception('Instagram is not configured!');
        }

        $ig = $this->instagram;
        if (!empty($page)) {
            if (array_key_exists($page, $this->instagram)) {
                unset($ig);
                $ig = array();
                $ig[$page] = $this->instagram[$page];
            } else {
                throw new \Exception('User is not configured under instagramm!');
            }
        }

        $posts = [];
        /**
         * @var $feed InstagramFeed
         */
        foreach ($ig as $name => $feed) {
            $post[$name] = $feed->getItems($count);
        }

        return $posts;
    }
}
