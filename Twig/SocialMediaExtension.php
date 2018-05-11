<?php

namespace FieldInteractive\CitoBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig_SimpleFunction;


class SocialMediaExtension extends AbstractExtension
{
    /**
     * @var string
     */
    private $postsPath;

    /**
     * SocialMediaExtension constructor.
     *
     * @param string $postsPath
     */
    public function __construct($postsPath)
    {
        $this->postsPath = $postsPath;
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('getFacebookPosts', array($this, 'getFacebookPosts')),
        );
    }

    /**
     * Reads the fb posts from posts.json and returns them
     *
     * @param   string $name The name of the fb page (this is the key you specified in config.php for the array)
     * @return  array  $posts       The desired number of fb posts
     */
    public function getFacebookPosts($name)
    {
        $posts = file_get_contents($this->postsPath . 'facebook/' . $name . '/posts.json');
        $posts = json_decode($posts, true);

        return $posts;
    }

}
