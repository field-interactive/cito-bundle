<?php

namespace FieldInteractive\CitoBundle\Twig;

use FieldInteractive\CitoBundle\Service\SocialMediaService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig_SimpleFunction;


class SocialMediaExtension extends AbstractExtension
{
    /**
     * @var SocialMediaService
     */
    private $socialMediaService;

    /**
     * SocialMediaExtension constructor.
     *
     * @param string $postsPath
     */
    public function __construct(SocialMediaService $socialMediaService)
    {
        $this->socialMediaService = $socialMediaService;
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('FacebookPosts', array($this, 'getFacebookPosts')),
            new Twig_SimpleFunction('InstagrammPosts', array($this, 'getInstagrmPosts')),
            new Twig_SimpleFunction('TwitterPosts', array($this, 'getTwitterPosts')),
        );
    }

    /**
     * Reads the fb posts from posts.json and returns them
     *
     * @param   string $name The name of the fb page (this is the key you specified in config.php for the array)
     * @param   string $count The amount of the posts
     * @param   string $offset
     * @return  array  $posts The desired number of fb posts
     */
    public function getFacebookPosts($name, $count = 10, $offset = 0)
    {
        $posts = $this->socialMediaService->loadFacebookFeed($name, $count, $offset);
        return $posts;
    }

    /**
     * Reads the fb posts from posts.json and returns them
     *
     * @param   string $name The name of the fb page (this is the key you specified in config.php for the array)
     * @param   string $count The amount of the posts
     * @param   string $offset
     * @return  array  $posts The desired number of fb posts
     */
    public function getInstagramPosts($name, $count = 10, $offset = 0)
    {
        $posts = $this->socialMediaService->loadInstagramFeed($name, $count, $offset);
        return $posts;
    }

    /**
     * Reads the fb posts from posts.json and returns them
     *
     * @param   string $name The name of the fb page (this is the key you specified in config.php for the array)
     * @param   string $count The amount of the posts
     * @param   string $offset
     * @return  array  $posts The desired number of fb posts
     */
    public function getTwitterPosts($name, $count = 10, $offset = 0)
    {
        $posts = $this->socialMediaService->loadTwitterFeed($name, $count, $offset);
        return $posts;
    }

}
