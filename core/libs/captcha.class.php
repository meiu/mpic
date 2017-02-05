<?php
/**
 *
 */
class captcha_cla{
    /**
     * Captcha session key
     *
     */
    var $_sessionValueKey = 'MEIU_CAPTCHA_VALUE';
    var $_sessionTtlKey = 'MEIU_CAPTCHA_TTL';

    /**
     * Captcha life time
     *
     * @var int
     */
    var $_ttl = 90;

    /**
     * Seed
     *
     * @var string
     */
    var $_seed = '346789ABCDEFGHJKLMNPRTUVWXYabcdefhjkmnpwxy';

    /**
     * Font
     *
     * @var string
     */
    var $_font = '';

    /**
     * Font size
     *
     * @var int
     */
    var $_size = 15;

    /**
     * Padding
     *
     * @var int
     */
    var $_padding = 4;

    /**
     * Space between chars
     *
     * @var int
     */
    var $_space = 4;

    /**
     * Captcha width
     *
     * @var int
     */
    var $_width = 80;

    /**
     * Captcha height
     *
     * @var int
     */
    var $_height = 30;

    /**
     * Num of chars in captcha
     *
     * @var int
     */
    var $_length = 4;

    /**
     * Background color
     *
     * @var string
     */
    var $_bgColor = '#f8f8f8';

    /**
     * Image
     *
     * @var resource
     */
    var $_image;



    /**
     * Constructor
     *
     * @param array $config
     */
    function captcha_cla($config = array())
    {
        isset($_SESSION) || session_start();

        $this->_init($config);
    }

    /**
     * Init config
     *
     * @param array $config
     */
    function _init($config)
    {
        $this->_font = dirname(__FILE__).'/captcha.ttf';//默认的字体

        $keys = array('sessionValueKey', 'sessionTtlKey', 'ttl', 'seed', 'font', 'size', 'width', 'height', 'length', 'bgColor', 'padding');
        foreach ($keys as $key)
        {
            if (isset($config[$key])) {
                $this->{'_' . $key} = $config[$key];
            }
        }
    }

    /**
     * Display captcha
     *
     * @param string $type | png/gif/jpeg
     */
    function display($type = 'png')
    {
        $this->_image();
        $type = strtolower($type);
        $func = "image$type";

        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Content-type: image/$type");

        $func($this->_image);
        imagedestroy($this->_image);
    }

    /**
     * Make image
     *
     */
    function _image()
    {
        $this->_image = imagecreate($this->_width, $this->_height);

        imageFilledRectangle($this->_image, 0, 0, $this->_width, $this->_height, $this->_color($this->_bgColor));

        $seed = $this->_seed();

        $_SESSION[$this->_sessionValueKey] = $seed;
        $_SESSION[$this->_sessionTtlKey] = time() + $this->_ttl;

        for ($i = 0; $i < $this->_length; $i++) {
            $text = substr($seed, $i, 1);
            $x = $this->_padding + $i * ($this->_size + $this->_space);
            $y = rand(0.6 * $this->_height, 0.8 * $this->_height);
            $textColor = imageColorAllocate($this->_image, rand(50, 155), rand(50, 155), rand(50, 155));
            imagettftext($this->_image, $this->_size, rand(-18,18), $x, $y, $textColor, $this->_font, $text);
        }

        $this->_noise();
    }

    /**
     * Make seed
     *
     * @return string
     */
    function _seed()
    {
        $str = str_shuffle(str_repeat($this->_seed, $this->_length));
        return substr($str, 0, $this->_length);
    }

    /**
     * Colar
     *
     * HEX color to RGB
     * @param string $color
     * @return int | ImageColorAllocate
     */
    function _color($color)
    {
        $color = ltrim($color, '#');
        $dec = hexdec($color);
        return ImageColorAllocate($this->_image, 0xFF & ($dec >> 0x10), 0xFF & ($dec >> 0x8), 0xFF & $dec);
    }

    /**
     * Noise
     *
     */
    function _noise()
    {
        $pointLimit = rand(128, 192);
        for ($i = 0; $i < $pointLimit; $i++) {
            $x = rand($this->_padding, $this->_width - $this->_padding);
            $y = rand($this->_padding, $this->_height - $this->_padding);
            $color = imagecolorallocate($this->_image, rand(0,255), rand(0,255), rand(0,255));

            imagesetpixel($this->_image, $x, $y, $color);
        }

        $lineLimit = rand(3, 5);
        for($i = 0; $i < $lineLimit; $i++) {
            $x1 = rand($this->_padding, $this->_width - $this->_padding);
            $y1 = rand($this->_padding, $this->_height - $this->_padding);
            $x2 = rand($x1, $this->_width - $this->_padding);
            $y2 = rand($y1, $this->_height - $this->_padding);

            imageline($this->_image, $x1, $y1, $x2, $y2, rand(0, 255));
        }
    }

    /**
     * Check captcha
     *
     * @param string $value
     * @param boolean $caseSensitive
     * @return boolean
     */
    function check($value, $caseSensitive = false)
    {
        isset($_SESSION) || session_start();
        if(!isset($_SESSION[$this->_sessionTtlKey]) || !isset($_SESSION[$this->_sessionValueKey])){
            return false;
        }

        $expireTime = $_SESSION[$this->_sessionTtlKey];
        $captchaCode = $_SESSION[$this->_sessionValueKey];

        // make captcha session expire
        unset($_SESSION[$this->_sessionTtlKey]);
        unset($_SESSION[$this->_sessionValueKey]);

        if (time() > $expireTime) {
            return false;
        }

        $func = $caseSensitive ? 'strcmp' : 'strcasecmp';

        if (0 !== $func($value, $captchaCode)) {
            return false;
        }

        return true;
    }
}