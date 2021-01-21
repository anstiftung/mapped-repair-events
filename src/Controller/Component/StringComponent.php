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

    public static function cleanStringForRssFeedDescription($string) {
        $string = htmlspecialchars(strip_tags($string, ALLOWED_TAGS_CKEDITOR_USER));
        return $string;
    }

    /**
     * ?url= param is cut from a's href - in pagination sort links i have no idea where it comes from
     * @param string <a>
     */
    public static function cleanPaginationSortLinks($link) {
        preg_match('/<a(.*?)href="(.*?)"(.*?)>/', $link, $matches);
        if (!empty($matches[2])) {
            return str_replace($matches[2], strtok($matches[2], '?'), $link);
        }
        return $link;
    }

    /**
     * Checks if a string is ASCII
     * returns false if any character found not in 0-255
     *
     * @param string $string
     * @return boolean
     */
    public static function isASCII($string)
    {
        return (preg_match('/^[\\x00-\\x7A]*$/', $string));
    }

    public static function getUniqueMailinatorEmailAddress()
    {
        return strtolower(self::createRandomString(20) . '-repini@mailinator.com');
    }

    /**
     * 	Tests if a string is utf8 encoded.
     * 	This will use the iconv support if installed/compiled with php, which
     * 	is much faster than the regular expression match that is performed if
     * 	iconv is not installed.
     * 	This function is from O'Reilly - Building Scalable Websites
     *  @param	string	$string
     * 	@return boolean
     */
    public static function isUtf8($string) {
        if (function_exists('iconv')) {
            return (iconv('UTF-8', 'UTF-8', $string) == $string);
        } else {
            $regexp = '[\xC0-\xDF](^\x80-\xBF]|$)'.
                '|[\xE0-\xEF].{0,1}([^\x80-\xBF]|$)'.
                '|[\xF0-\xF7].{0,2}([^\x80-\xBF]|$)'.
                '|[\xF8-\xFB].{0,3}([^\x80-\xBF]|$)'.
                '|[\xFC-\xFD].{0,4}([^\x80-\xBF]|$)'.
                '|[\xFE-\xFE].{0,5}([^\x80-\xBF]|$)'.
                '|[\x00-\x7F][\x80-\xBF]'.
                '|[\xC0-\xDF].[\x80-\xBF]'.
                '|[\xE0-\xEF]..[\x80-\xBF]'.
                '|[\xF0-\xF7]...[\x80-\xBF]'.
                '|[\xF8-\xFB]....[\x80-\xBF]'.
                '|[\xFC-\xFD].....[\x80-\xBF]'.
                '|[\xFE-\xFE]......[\x80-\xBF]'.
                '|^[\x80-\xBF]';
            return preg_match('!'.$regexp.'!', $string);
        }
    }

    /**
     * Removes characters from the beginning of a string if they are found
     * <code>
     * $string = 'my example is easy';
     * // will echo 'your wife is easy'
     * echo StringComponent::compareCut($string, 'my example', 'your wife');
     * </code>
     *
     * @param
     *            string
     * @param
     *            string
     * @param
     * @return string
     */
    public static function compareCut($haystack, $find, $replace)
    {
        $findLength = mb_strlen($find, 'UTF-8');
        if (! strncmp($haystack, $find, $findLength)) {
            return $haystack;
        }
        return $replace . mb_substr($haystack, $findLength);
    }

    /**
     * Replaces HTML-Signs into HTML Entities
     * This function was introduced because htmlenties from php encoded
     * a utf-8 as 2 byte html entity, so a ö became Ã¶
     * only do this because & and other special characters do not need to be masked in utf8 sites
     *
     * @param
     *            string
     * @return string
     */
    public static function htmlentities($string)
    {
        $replaced = strtr($string, [
            '"' => '&quot;',
            '<' => '&lt;',
            '>' => '&gt;'
        ]);
        return $replaced;
    }

    /**
     * Prepares text for output on the website
     * - replaces html entities
     * - translates line brakes
     * - reencodes simple formating - [b] for bold
     */
    public static function prepareTextForHTML($text) {

        //		$text = self::htmlentities($text);
        $text = nl2br($text);

        //		$arraySearch  = array('[b]', '[/b]');
        //		$arrayReplace = array('<b>','</b>');
        //		$text = str_replace($arraySearch, $arrayReplace, $text);

        return $text;
    }

    public static function prepareTextPreview($text) {
        $text = strip_tags($text, '<ul><li><p><b><a><h2><strong>');
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

    /**
     * Truncates a string
     *
     * @param string $string
     * @param integer $length
     * @param string $end
     * @param boolean $force
     */
    public static function truncate($string, $length, $end = '', $force = false, $calculateWidths = true, $calculateWithEndString = true)
    {
        if (strlen($string) <= $length)
            return $string;
            $truncated = '';
            $strLength = mb_strlen($string, 'UTF-8');
            if (strlen($end) == 0 || ! $calculateWithEndString) {
                $lengthToCut = $length;
            } else {
                $lengthToCut = $length - mb_strlen($end, 'UTF-8');
            }
            $lastSpace = 0;
            for ($i = 0; $i < $strLength; $i ++) {
                $char = mb_substr($string, $i, 1, 'UTF-8');
                if ($calculateWidths) {
                    $lengthToCut += self::charWidth($char);
                }
                if (preg_match('/\s/', $char)) {
                    $lastSpace = $i;
                }
                if ($i >= $lengthToCut) {
                    break;
                }
            }
            if ($lastSpace == 0 or $force == true) {
                $truncated = mb_substr($string, 0, $lengthToCut, 'UTF-8');
            } else {
                $truncated = mb_substr($string, 0, $lastSpace, 'UTF-8');
            }
            return $truncated . $end;
    }

    /**
     * Adds some line breaks if the lines are to long
     *
     * @param string $string
     * @param integer $length
     * @param boolean $force
     * @return string
     */
    public static function wrap($string, $length, $force = true, $calculateWidths = true)
    {
        if (strlen($string) <= $length)
            return $string;
            $strLength = mb_strlen($string, 'UTF-8');
            $intag = false;
            $charsSinceLastBreak = 0;
            $wrapped = '';
            $lengthToCut = $length;
            for ($i = 0; $i < $strLength; $i ++) {
                $char = mb_substr($string, $i, 1, 'UTF-8');
                if ($char == '<')
                    $intag = true;
                    if ($calculateWidths) {
                        $lengthToCut += self::charWidth($char);
                    }
                    if (! $intag) {
                        if (preg_match('/[\s+]/', $char)) {
                            $charsSinceLastBreak = 0;
                            $lengthToCut = $length;
                        } elseif ($charsSinceLastBreak >= $lengthToCut) {
                            $charsSinceLastBreak = 0;
                            $wrapped .= LF;
                            $lengthToCut = $length;
                        } else {}
                        $charsSinceLastBreak ++;
                    }
                    if ($char == '>')
                        $intag = false;
                        $wrapped .= $char;
            }
            return $wrapped;
    }

    /**
     * Characters have different width.
     * some are very broad, some a thin.
     *
     * @param string $char
     * @return integer
     */
    public static function charWidth($char)
    {
        // super wide characters
        if (preg_match('/[©@®@—]/', $char)) {
            return - 1.2;
            // wide characters
        } elseif (preg_match('/[WQTZMDGOÖÓÒÔHUÜÚÙÛÄÁÀÂ]/i', $char)) {
            return - 0.8;
            // thin characters
        } elseif (preg_match('/[ilt1j\.,;:´`!\(\)\'*()\|\[\]]/', $char)) {
            return + 0.3;
            // all others
        } else {
            return 0;
        }
    }

    /**
     * Implodes a String like php native function does but can handle
     * objects in the array and multiple dimensions
     *
     * @param string $glue
     * @param
     *            array(mixed)
     * @return string
     */
    public static function implode($glue, Array $pieces)
    {
        if (count($pieces) == 0)
            return '';
            $return = '';
            foreach ($pieces as $piece) {
                if (is_array($piece)) {
                    $return .= self::implode($glue, $piece);
                } elseif (is_object($piece)) {
                    $return .= $piece->__toString() . $glue;
                } else {
                    $return .= $piece . $glue;
                }
            }
            return substr($return, 0, - strlen($glue));
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

    /**
     * Probability of two exact Random Strings, with a-zA-Z0-9 is:
     * (25+25+10)^(25+25+10)
     *
     * generates a random string of $length length
     * <code>
     * // create random password
     * $str_passwort = Text::createRandomString(6);
     * // create password just with letter and some special chars
     * $str_password = Text::createRandomString(8, 'A-Z()');
     * </code>
     *
     * @param
     *            integer length of the string
     * @return string generated string
     *
     */
    public static function createRandomString($length = 8, $salt = null)
    {
        if ($salt !== null) {
            $salt = strtr($salt, [
                "a-z" => "abcdefghijklmnopqrstuvw",
                "A-Z" => "ABCDEFGHIJKLMNOPQRSTUVW",
                "0-9" => "0123456789",
                "1-9" => "123456789"
            ]);
        } else {
            $salt = "abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789"; // salt to select chars from
        }
        srand((double) microtime() * 1000000); // start the random generator
        $string = "";
        for ($i = 0; $i < $length; $i ++) {
            $string .= substr($salt, rand() % strlen($salt), 1);
        }
        return $string;
    }

    /**
     * create an md5 hash of the gien string
     * returns the with number given amount of characters
     *
     * @param
     *            string
     * @return string (16 characters - the first 8 from string(email) hash and the first 8 from number (userid))
     *
     */
    public static function createMd5Hash($sString, $iNumber)
    {
        $sReturnString = '';
        $sReturnNumber = '';
        $sReturnString = md5($sString, false);
        $sReturnNumber = md5($iNumber, false);

        $sReturn = substr($sReturnString, 0, 8) . substr($sReturnNumber, 0, 8);

        return $sReturn;
    }

    /**
     * create an md5 hash of the gien string
     * returns the with number given amount of characters
     *
     * @param
     *            string
     * @return string
     *
     */
    public static function createSimpleMd5Hash($sString)
    {
        return md5($sString, false);
    }

    /**
     * Strips all breaks from a string
     *
     * @param string $string
     * @param string $replace
     *            Replace with this string
     */
    public static function stripBrakes($string, $replace = "")
    {
        return preg_replace('/([\\r|\\n]|\\<br\\>|\\<br \\/\\>|\\<p\\>|\\<p \\/\\>)/', $replace, $string);
    }

    /**
     * Removes all non-alpha numerical charachters
     *
     * @param string $string
     * @return string
     */
    public static function stripNonAlphaNumerical($string)
    {
        return preg_replace('/([^\w])/', "", $string);
    }

    /**
     * replaces umlauts and sz
     *
     * @return string cleaned string
     */
    public static function replaceUmlauts($string)
    {
        $arraySearch = [
            'ä',
            'ö',
            'ü',
            'Ä',
            'Ö',
            'Ü',
            'ß'
        ];
        $arrayRepalce = [
            'ae',
            'oe',
            'ue',
            'Ae',
            'Oe',
            'Ue',
            'ss'
        ];
        return str_replace($arraySearch, $arrayRepalce, $string);
    }

    /**
     * original character should be kept
     * http://de.wikipedia.org/wiki/Diakritisches_Zeichen
     *
     * @return string cleaned string
     */
    public static function replaceDiacriticCharacters($string)
    {
        // TODO nicht explizit aufführen sondern den basis-buchstabe berechnen - vielleicht gibts da einen guten weg
        $search = explode(",", "ç,ş,ţ,æ,œ,á,ć,é,í,ó,ú,ý,à,è,ì,ò,ù,ỳ,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,ŷ,č,ě,ř,š,ž,å,ů,ő,e,i,ø,u,ã,ñ,ă,ŭ");
        $replace = explode(",", "c,s,t,ae,oe,a,c,e,i,o,u,y,a,e,i,o,u,y,a,e,i,o,u,y,a,e,i,o,u,y,c,e,r,s,z,a,u,o,e,i,o,u,a,n,a,u");
        return str_replace($search, $replace, $string);
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

    public static function getStrippedTextForBoxes($text)
    {
        $text = strip_tags($text, '<b>');
        $text = Text::truncate($text, 100);
        return $text;
    }

    /**
     * http://www.maurits.vdschee.nl/php_hide_email/
     */
    public static function hide_email($email, $class = '', $renderAsLink = true)
    {
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

            $cipher_text .= $key[strpos($character_set, $email[$i])];

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

        }

        return '<span id="' . $id . '">[javascript protected email address]</span>' . $script;

    }
}