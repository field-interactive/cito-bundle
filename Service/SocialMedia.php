<?php

namespace FieldInteractive\CitoBundle\Service;

class SocialMedia
{
    private $postsPath;

    private $facebook = array();

    private $twitter = array();

    private $instagramm = array();

    public function __construct($socialMedia, $postsPath)
    {
        $this->postsPath = $postsPath;

        if (array_key_exists('facebook', $socialMedia)) {
            $this->facebook = $socialMedia['facebook'];
        }

        if (array_key_exists('twitter', $socialMedia)) {
            $this->twitter = $socialMedia['twitter'];
        }

        if (array_key_exists('facebook', $socialMedia)) {
            $this->instagramm = $socialMedia['instagramm'];
        }
    }

    public function getFacebookPosts($user = '')
    {
        if (empty($this->facebook)) {
            throw new \Exception('Facebook is not configured!');
        }

        $fb = $this->facebook;
        if (!empty($user)) {
            if (array_key_exists($user, $this->facebook)) {
                unset($fb);
                $fb = array();
                $fb[$user] = $this->facebook[$user];
            } else {
                throw new \Exception('User is not configured under facebook!');
            }
        }

        foreach ($fb as $pageName => $page) {

            //define fan page id
            $facebook_page = $page['pageId'];

            $access_token = $page['access_token'];

            //use Facebook Graph API to retrieve albums' ID and count (no of images in album)
            $string1 = file_get_contents('https://graph.facebook.com/' . $facebook_page . '/posts?access_token=' . $access_token);

            //the Json file with the data contains the word "count" which is a reserved word in PHP, so I replaced it with "countx" (I know- very imaginative)
            $string = str_replace("count", "countx", $string1);

            //decoding the Json feed
            $jdata = json_decode($string);

            // somehow we get every item two times
            $usedIds = array();

            if (is_array($jdata->data)) {
                $iEntry = 0;

                foreach ($jdata->data as $post) {
                    if (isset($usedIds[$post->id])) {
                        continue;
                    } else {
                        $usedIds[$post->id] = $post->id;
                    }
                    if (isset($post->message)) {

                        if (isset($post->type) && $post->type == 'video') {
                            $fbPosts[$iEntry]['video'] = 'video';
                        }

                        $fbPosts[$iEntry]['date'] = date('d.m.Y', strtotime($post->created_time));

                        /**
                         * @author FS
                         * Get pictures since it won't get queried any longer (july 2017)
                         */
                        $image = file_get_contents('https://graph.facebook.com/' . $post->id . '?fields=full_picture&access_token=' . $access_token);
                        $image = json_decode($image);

                        $fbPosts[$iEntry]['img'] = false;

                        if (!empty($image->full_picture)) {
                            $filename = $this->postsPath . $pageName . '/images/' . $post->id . '.jpg';

                            if (!file_exists($this->postsPath . 'facebook/' . $pageName . '/images/')) {
                                mkdir($this->postsPath . 'facebook/' . $pageName . '/images/', 0777, true);
                            }

                            file_put_contents($filename, file_get_contents($image->full_picture));

                            $fbPosts[$iEntry]['img'] = '/posts/' . $pageName . '/images/' . $post->id . '.jpg';
                        }

                        $message = $post->message;
                        $fbPosts[$iEntry]['message'] = $message;

                        $length = 70;           // Modify for desired width
                        if (strlen($message) <= $length) {
                            $teaser = $message; // Do nothing
                        } else {
                            if (!($pos = strpos($message, ' ', $length))) {
                                $pos = $length;
                            }

                            $teaser = substr($message, 0, $pos);
                            $teaser .= ' ...';
                        }

                        $fbPosts[$iEntry]['teaser'] = $teaser;

                        $fbPosts[$iEntry]['link'] = "http://www.facebook.com/" . str_replace('_', '/posts/', $post->id);

                        $iEntry++;
                        if ($iEntry >= $page['count']) {
                            break;
                        }
                    }
                }

                if (!empty($fbPosts)) {
                    $filename = $this->postsPath . 'facebook/' . $pageName . '/posts.json';

                    if (!file_exists($this->postsPath . 'facebook/' . $pageName)) {
                        mkdir($this->postsPath . 'facebook/' . $pageName, 0777, true);
                    }

                    file_put_contents($filename, json_encode($fbPosts));
                }
            }
        }
    }

    public function getTwitterPosts($user = '')
    {
        // Todo: Implement
    }

    public function getInstagrammPosts($user = '')
    {
        // Todo: Implement
    }

    /**
     * @return mixed
     */
    public function getFacebook()
    {
        return $this->facebook;
    }
}
