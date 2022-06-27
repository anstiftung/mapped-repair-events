<?php
namespace App\Controller\Component;

use Cake\Controller\Component; // for unit tests
use Cake\Core\Configure;
use Cake\Utility\Text;

class StringComponent extends Component
{

    public $seoLinkArray = [];

    /**
     * @param string $string
     * @return string
     */
    public static function removeIdFromSlug($string)
    {
        return preg_replace('/^([\d]+)-(.*)$/', '$2', $string);
    }

    /**
     * Prepares text for output on the website
     * - replaces html entities
     * - translates line brakes
     * - reencodes simple formating - [b] for bold
     */
    public static function prepareTextForHTML($text) {
        $text = nl2br($text);
        return $text;
    }

    public static function prepareTextPreview($text) {
        if (!is_null($text)) {
            $text = strip_tags($text, '<ul><li><p><b><a><h2><strong>');
        }
        return $text;
    }

    public static function prepareTextPreviewForLinkedBoxes($text) {
        $text = strip_tags($text);
        return $text;
    }

    public static function textForHTML($text)
    {
        return htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
    }

    // $str: The anchor string that will be altered
    // $relValue: The rel attribute values you wish to have attached to the anchor
    public static function makeNoFollow(&$str, $relValue = 'nofollow')
    {

        if (is_null($str)) {
            return $str;
        }

        // See if there is already a "rel" attribute
        if (strpos($str, "rel")) {
            $pattern = "/rel=([\"'])([^\\1]+?)\\1/";
            $replace = "rel=\\1\\2 $relValue\\1";
        } else {
            $pattern = "/<a /";
            $replace = "<a rel=\"$relValue\" ";
        }

        $str = preg_replace($pattern, $replace, $str);

        return $str;

    }

    public static function createPassword()
    {
        return mb_strtolower(self::createRandomString(15));
    }

    public static function createConfirmationCode($email)
    {
        $confirmationCode = mb_strtolower(substr(md5($email), 0, 5) . StringComponent::createRandomString(5));
        return $confirmationCode;
    }

    public static function decodeConfirmationCode($emailAndConfirmationCodeHash)
    {
        $emailHash = substr($emailAndConfirmationCodeHash, 0, 5);
        $cc = substr($emailAndConfirmationCodeHash, - 5);

        $return = [
            'emailHash' => $emailHash,
            'confirmationCode' => $cc
        ];

        return $return;
    }

    public static function createRandomString($n = 8)
    {
        $characters = "abcdefghijkmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $randomString = '';
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }

    /**
     *
     * http://stackoverflow.com/questions/5305879/automatic-clean-and-seo-friendly-url-slugs
     *
     * @param string $string
     * @param string $separator
     * @return string
     */
    public static function slugify($string, $separator = '-', $toLower = true)
    {
        $accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
        $special_cases = [
            '&' => 'and',
            "'" => '',
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'ß' => 'ss'
        ];
        $string = trim($string);
        if ($toLower) {
            $string = mb_strtolower($string, 'UTF-8');
        }
        $string = str_replace(array_keys($special_cases), array_values($special_cases), $string);
        $string = preg_replace($accents_regex, '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'));
        $string = preg_replace("/[^a-zA-Z0-9]/u", "$separator", $string);
        $string = preg_replace("/[$separator]+/u", "$separator", $string);
        return $string;
    }

    public static function slugifyAndKeepCase($string)
    {
        return self::slugify($string, '-', false);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function encryptSensitiveData($string, $hideAllCharacters=false)
    {
        $len = strlen($string);
        if ($hideAllCharacters) {
            return str_repeat('*', $len);
        }
        if ($len < 2) return $string;
        return substr($string, 0, 1).str_repeat('*', $len - 2).substr($string, $len - 1, 1);

    }

    public static function cutHtmlString($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true)
    {

        if (is_null($text)) {
            return '';
        }

        if ($considerHtml) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
            $total_length = strlen($ending);
            $open_tags = array();
            $truncate = '';
            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                    // if it's an "empty element" with or without xhtml-conform closing slash
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                        // do nothing
                        // if tag is a closing tag
                    } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                        // delete tag from $open_tags list
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) {
                            unset($open_tags[$pos]);
                        }
                        // if tag is an opening tag
                    } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                        // add tag to the beginning of $open_tags list
                        array_unshift($open_tags, strtolower($tag_matchings[1]));
                    }
                    // add html-tag to $truncate'd text
                    $truncate .= $line_matchings[1];
                }
                // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length+$content_length> $length) {
                    // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1]+1-$entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                                // no more characters left
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left+$entities_length);
                    // maximum lenght is reached, so get off the loop
                    break;
                } else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }
                // if the maximum length is reached, get off the loop
                if($total_length>= $length) {
                    break;
                }
            }
        } else {
            if (strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = substr($text, 0, $length - strlen($ending));
            }
        }
        // if the words shouldn't be cut in the middle...
        if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
        }
        // add the defined ending to the text
        $truncate .= $ending;
        if($considerHtml) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }

        $truncate = Configure::read('AppConfig.htmlHelper')->trimAndRemoveEmptyTags($truncate);
        return $truncate;
    }

    public static function isUrlRelative($url)
    {
        if (preg_match('/https?\:\/\//', $url)) {
            return false;
        }
        return true;
    }

    /**
     * http://www.maurits.vdschee.nl/php_hide_email/
     */
    public static function hide_email($email, $class = '', $renderAsLink = true)
    {

        if (is_null($email)) {
            return;
        }

        $classHtml = '';
        if ($class != '') {
            $classHtml = 'class=\"' . $class . '\" ';
        }

        $character_set = '+-.0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';
        $key = str_shuffle($character_set);
        $cipher_text = '';
        $id = 'e' . rand(1, 999999999);
        if ($renderAsLink) {
            $tag = '<a ' . $classHtml . 'href=\\"mailto:"+d+"\\">"+d+"</a>';
        } else {
            $tag = '<span ' . $classHtml . '>"+d+"</span>';
        }

        for ($i = 0; $i < strlen($email); $i++) {
            if (!strpos($character_set, $email[$i]) > 0) {
                $email[$i] = '+';
            }
            $cipher_text .= $key[
                strpos(
                    $character_set,
                    $email[$i]
                )
            ];
        }

        $script = 'var a="' . $key . '";var b=a.split("").sort().join("");var c="' . $cipher_text . '";var d="";';
        $script .= 'for(var e=0;e<c.length;e++)d+=b.charAt(a.indexOf(c.charAt(e)));';
        $script .= 'document.getElementById("' . $id . '").innerHTML="'.$tag.'";';
        $script = "eval(\"" . str_replace([
            "\\",
            '"'
        ], [
            "\\\\",
            '\"'
        ], $script) . "\")";

        $script = '<script type="text/javascript">/*<![CDATA[*/' . $script . '/*]]>*/</script>';
        return '<span id="' . $id . '">[javascript protected email address]</span>' . $script;

    }
}