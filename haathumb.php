<?php

/*
	haaKing's (https://github.com/haaking) haaThumb is the
    "simplest" way to call the thumbnail images
	via htaccess rewrite rules or web.config rewrite rules.

    Based on work done by Ben Gillbanks and Mark Maunder
	Based on work done by Tim McDaniels and Darren Hoyt

    Thanks to SeRosiS (https://github.com/Serosis) for
    some brillant ideas and documentation.

    GNU General Public License, version 2
	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

    Old version of the code was including timthumb.php but we did a kind of timthumb-lite
    (webshot deleted, external image deleted, some code included)
    Now we merge the haathumb and timthumb and we call it haaThumb 2.0


    version 2.0
*/
error_reporting(0);

$data = explode('/', $_GET['var']);

$filter = '&f=';

$size = explode('x', $data[0]);
if ($size[0] > 0) $width = '&w=' . $size[0];
if ($size[1] > 0) $height = '&h=' . $size[1];

if (in_array('quality', $data)) {
    $key = array_search('quality', $data);
    $key++;
    if (is_numeric($data[$key])) $quality = '&q=' . $data[$key];
}
if (in_array('fit', $data)) $zc = '&zc=0';
if (in_array('cropped', $data)) $zc = '&zc=1';
if (in_array('bordered', $data)) $zc = '&zc=2';
if (in_array('aspect', $data)) $zc = '&zc=3';
if (in_array('center', $data)) $a = '&a=c';
if (in_array('top', $data)) $a = '&a=t';
if (in_array('top-right', $data)) $a = '&a=tr';
if (in_array('top-left', $data)) $a = '&a=tl';
if (in_array('bottom', $data)) $a = '&a=b';
if (in_array('bottom-right', $data)) $a = '&a=br';
if (in_array('bottom-left', $data)) $a = '&a=bl';
if (in_array('left', $data)) $a = '&a=l';
if (in_array('right', $data)) $a = '&a=r';
if (in_array('invert', $data)) $filter .= '1|';
if (in_array('grayscale', $data)) $filter .= '2|';
if (in_array('brightness', $data)) {
    $key = array_search('brightness', $data);
    $key++;
    if (is_numeric($data[$key])) $filter .= '3,' . $data[$key] . '|';
}
if (in_array('contrast', $data)) {
    $key = array_search('contrast', $data);
    $key++;
    if (is_numeric($data[$key])) $filter .= '4,' . $data[$key] . '|';
}
if (in_array('colorize', $data)) {
    $key = array_search('colorize', $data);
    $key++;
    $rgba = explode('.', $data[$key]);
    if ($rgba[0] > 255) $rgba[0] = 255;
    if ($rgba[1] > 255) $rgba[1] = 255;
    if ($rgba[2] > 255) $rgba[2] = 255;
    if (count($rgba) == 3) $rgba[3] = 127;
    if (count($rgba) == 4 && $rgba[3] > 127) $rgba[3] = 127;
    if (count($rgba) == 4 && is_numeric($rgba[0]) && is_numeric($rgba[1]) && is_numeric($rgba[2]) && is_numeric($rgba[3])) $filter .= '5,' . implode(',', $rgba) . '|';
}
if (in_array('edge-detect', $data)) $filter .= '6|';
if (in_array('emboss', $data)) $filter .= '7|';
if (in_array('gaussian', $data)) $filter .= '8|';
if (in_array('selective', $data)) $filter .= '9|';
if (in_array('mean', $data)) $filter .= '10|';
if (in_array('smooth', $data)) {
    $key = array_search('smooth', $data);
    $key++;
    if (is_numeric($data[$key])) $filter .= '11,' . $data[$key] . '|';
}
if (in_array('pixelate', $data)) {
    $key = array_search('pixelate', $data);
    $key++;
    $pix = explode('.', $data[$key]);
    if ($pix[1] > 1) $pix[1] = 1;
    if (count($pix) == 1) $pix[1] = 0;
    if (count($pix) == 2 && is_numeric($pix[0]) && is_numeric($pix[1])) $filter .= '12,' . implode(',', $pix) . '|';
}
if (in_array('sharpen', $data)) $sharpen = '&s=1';
if (in_array('canvas-color', $data)) {
    $key = array_search('canvas-color', $data);
    $key++;
    if (preg_match('/^[a-f0-9]{6}$/i', $data[$key])) $canvasColor = '&cc=' . $data[$key];
}
if (in_array('canvas-trans', $data)) $canvasTrans = '&ct=1';

$progressive = TRUE; //default
if (in_array('conservative', $data)) $progressive = FALSE;
$filter = trim($filter, '|');
if ($filter == '&f=') $filter = null;

$source = explode('/src/', $_GET['var']);

$ttString = '&src=' . $source[1] . $width . $height . $quality . $zc . $a . $filter . $sharpen . $canvasColor . $canvasTrans;
$ttString = trim($ttString, '&');
$ttArray = array();
parse_str($ttString, $ttArray);

$_GET = $ttArray;

//echo $ttString.'<br /><br />';
//print_r ($_GET); echo '<br /><br />';
// I used this to debug my work



define ('DEBUG_ON', false);                                // Enable debug logging to web server error log (STDERR)
define ('DEBUG_LEVEL', 1);                                // Debug level 1 is less noisy and 3 is the most noisy
define ('MEMORY_LIMIT', '30M');                            // Set PHP memory limit
define ('BLOCK_EXTERNAL_LEECHERS', true);                // If the image or webshot is being loaded on an external site, display a red "No Hotlinking" gif.
define ('DISPLAY_ERROR_MESSAGES', true);                // Display error messages. Set to false to turn off errors (good for production websites)

//Image fetching and caching
define ('FILE_CACHE_ENABLED', TRUE);                    // Should we store resized/modified images on disk to speed things up?
define ('FILE_CACHE_TIME_BETWEEN_CLEANS', 86400);    // How often the cache is cleaned

define ('FILE_CACHE_MAX_FILE_AGE', 86400);                // How old does a file have to be to be deleted from the cache
define ('FILE_CACHE_SUFFIX', 'thumb.cache');            // What to put at the end of all files in the cache directory so we can identify them
define ('FILE_CACHE_PREFIX', 'haa');                // What to put at the beg of all files in the cache directory so we can identify them
define ('FILE_CACHE_DIRECTORY', 'cache');                // Directory where images are cached. Left blank it will use the system temporary directory (which is better for security)
define ('MAX_FILE_SIZE', 10485760);                        // 10 Megs is 10485760. This is the max internal or external file size that we'll process.

//Browser caching
define ('BROWSER_CACHE_MAX_AGE', 864000);                // Time to cache in the browser
define ('BROWSER_CACHE_DISABLE', false);                // Use for testing if you want to disable all browser caching

//Image size and defaults
define ('MAX_WIDTH', 1500);                                // Maximum image width
define ('MAX_HEIGHT', 1500);                            // Maximum image height
define ('NOT_FOUND_IMAGE', '');                            // Image to serve if any 404 occurs
define ('ERROR_IMAGE', '');                                // Image to serve if an error occurs instead of showing error message
define ('PNG_IS_TRANSPARENT', FALSE);                    // Define if a png image should have a transparent background color. Use False value if you want to display a custom coloured canvas_colour
define ('JPEG_IS_PROGRESSIVE', $progressive);                    // Define if a created jpeg image is progressive
define ('DEFAULT_Q', 90);                                // Default image quality.
define ('DEFAULT_ZC', 1);                                // Default zoom/crop setting.
define ('DEFAULT_F', '');                                // Default image filters.
define ('DEFAULT_S', 0);                                // Default sharpen value.
define ('DEFAULT_CC', 'ffffff');                        // Default canvas colour.
define ('DEFAULT_WIDTH', 100);                            // Default thumbnail width.
define ('DEFAULT_HEIGHT', 100);                            // Default thumbnail height.
define ('LOCAL_FILE_BASE_DIRECTORY', __DIR__);

//Image compression is enabled if either of these point to valid paths

//These are now disabled by default because the file sizes of PNGs (and GIFs) are much smaller than we used to generate.
//They only work for PNGs. GIFs and JPEGs are not affected.
define ('OPTIPNG_ENABLED', false);
define ('OPTIPNG_PATH', '/usr/bin/optipng'); //This will run first because it gives better compression than pngcrush.
define ('PNGCRUSH_ENABLED', false);
define ('PNGCRUSH_PATH', '/usr/bin/pngcrush'); //This will only run if OPTIPNG_PATH is not set or is not valid

haathumb::start();

class haathumb
{
    protected $src = "";
    protected $is404 = false;
    protected $docRoot = "";
    protected $lastURLError = false;
    protected $localImage = "";
    protected $localImageMTime = 0;
    protected $url = false;
    protected $myHost = "";
    protected $isURL = false;
    protected $cachefile = '';
    protected $errors = array();
    protected $toDeletes = array();
    protected $cacheDirectory = '';
    protected $startTime = 0;
    protected $lastBenchTime = 0;
    protected $cropTop = false;
    protected $salt = "";
    protected $fileCacheVersion = 1; //Generally if haathumb.php is modifed (upgraded) then the salt changes and all cache files are recreated. This is a backup mechanism to force regen.
    protected $filePrependSecurityBlock = "<?php die('Execution denied!'); //"; //Designed to have three letter mime type, space, question mark and greater than symbol appended. 6 bytes total.
    protected static $curlDataWritten = 0;
    protected static $curlFH = false;

    public static function start()
    {
        $tim = new haathumb();
        $tim->handleErrors();
        if ($tim->tryBrowserCache()) {
            exit(0);
        }
        $tim->handleErrors();
        if (FILE_CACHE_ENABLED && $tim->tryServerCache()) {
            exit(0);
        }
        $tim->handleErrors();
        $tim->run();
        $tim->handleErrors();
        exit(0);
    }

    public function __construct()
    {
        $this->startTime = microtime(true);
        date_default_timezone_set('UTC');
        $this->debug(1, "Starting new request from " . $this->getIP() . " to " . $_SERVER['REQUEST_URI']);
        $this->calcDocRoot();
        //On windows systems I'm assuming fileinode returns an empty string or a number that doesn't change. Check this.
        $this->salt = @filemtime(__FILE__) . '-' . @fileinode(__FILE__);
        $this->debug(3, "Salt is: " . $this->salt);
        if (FILE_CACHE_DIRECTORY) {
            if (!is_dir(FILE_CACHE_DIRECTORY)) {
                @mkdir(FILE_CACHE_DIRECTORY);
                if (!is_dir(FILE_CACHE_DIRECTORY)) {
                    $this->error("Could not create the file cache directory.");
                    return false;
                }
            }
            $this->cacheDirectory = FILE_CACHE_DIRECTORY;
            if (!touch($this->cacheDirectory . '/index.html')) {
                $this->error("Could not create the index.html file - to fix this create an empty file named index.html file in the cache directory.");
            }
        } else {
            $this->cacheDirectory = sys_get_temp_dir();
        }
        //Clean the cache before we do anything because we don't want the first visitor after FILE_CACHE_TIME_BETWEEN_CLEANS expires to get a stale image.
        $this->cleanCache();

        $this->myHost = preg_replace('/^www\./i', '', $_SERVER['HTTP_HOST']);
        $this->src = $this->param('src');
        $this->url = parse_url($this->src);
        $this->src = preg_replace('/https?:\/\/(?:www\.)?' . $this->myHost . '/i', '', $this->src);

        if (strlen($this->src) <= 3) {
            $this->error("No image specified");
            return false;
        }
        if (BLOCK_EXTERNAL_LEECHERS && array_key_exists('HTTP_REFERER', $_SERVER) && (!preg_match('/^https?:\/\/(?:www\.)?' . $this->myHost . '(?:$|\/)/i', $_SERVER['HTTP_REFERER']))) {
            // base64 encoded red image that says 'no hotlinkers'
            // nothing to worry about! :)
            $imgData = base64_decode("R0lGODlhUAAMAIAAAP8AAP///yH5BAAHAP8ALAAAAABQAAwAAAJpjI+py+0Po5y0OgAMjjv01YUZ\nOGplhWXfNa6JCLnWkXplrcBmW+spbwvaVr/cDyg7IoFC2KbYVC2NQ5MQ4ZNao9Ynzjl9ScNYpneb\nDULB3RP6JuPuaGfuuV4fumf8PuvqFyhYtjdoeFgAADs=");
            header('Content-Type: image/gif');
            header('Content-Length: ' . strlen($imgData));
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header("Pragma: no-cache");
            header('Expires: ' . gmdate('D, d M Y H:i:s', time()));
            echo $imgData;
            return false;
            exit(0);
        }
        if (preg_match('/^https?:\/\/[^\/]+/i', $this->src)) {
            $this->debug(2, "Is a request for an external URL: " . $this->src);
            $this->isURL = true;
        } else {
            $this->debug(2, "Is a request for an internal file: " . $this->src);
        }
        if ($this->isURL) {
            $this->error("You are not allowed to fetch images from an external website.");
            return false;
        }

        $cachePrefix = ($this->isURL ? '_ext_' : '_int_');
        if ($this->isURL) {
            $arr = explode('&', $_SERVER ['QUERY_STRING']);
            asort($arr);
            $this->cachefile = $this->cacheDirectory . '/' . FILE_CACHE_PREFIX . $cachePrefix . md5($this->salt . implode('', $arr) . $this->fileCacheVersion) . FILE_CACHE_SUFFIX;
        } else {
            $this->localImage = $this->getLocalImagePath($this->src);

            if (!$this->localImage) {
                $this->debug(1, "Could not find the local image: {$this->localImage}");
                $this->error("Could not find the internal image you specified.");
                $this->set404();
                return false;
            }
            $this->debug(1, "Local image path is {$this->localImage}");
            $this->localImageMTime = @filemtime($this->localImage);
            //We include the mtime of the local file in case in changes on disk.
            $this->cachefile = $this->cacheDirectory . '/' . FILE_CACHE_PREFIX . $cachePrefix . md5($this->salt . $this->localImageMTime . $_SERVER ['QUERY_STRING'] . $this->fileCacheVersion) . FILE_CACHE_SUFFIX;
        }
        $this->debug(2, "Cache file is: " . $this->cachefile);

        return true;
    }

    public function __destruct()
    {
        foreach ($this->toDeletes as $del) {
            $this->debug(2, "Deleting temp file $del");
            @unlink($del);
        }
    }

    public function run()
    {
        if ($this->isURL) {

                $this->debug(1, "Got a request for an external image but ALLOW_EXTERNAL is disabled so returning error msg.");
                $this->error("You are not allowed to fetch images from an external website.");
                return false;

        } else {
            $this->debug(3, "Got request for internal image. Starting serveInternalImage()");
            $this->serveInternalImage();
        }
        return true;
    }

    protected function handleErrors()
    {
        if ($this->haveErrors()) {
            if (NOT_FOUND_IMAGE && $this->is404()) {
                if ($this->serveImg(NOT_FOUND_IMAGE)) {
                    exit(0);
                } else {
                    $this->error("Additionally, the 404 image that is configured could not be found or there was an error serving it.");
                }
            }
            if (ERROR_IMAGE) {
                if ($this->serveImg(ERROR_IMAGE)) {
                    exit(0);
                } else {
                    $this->error("Additionally, the error image that is configured could not be found or there was an error serving it.");
                }
            }
            $this->serveErrors();
            exit(0);
        }
        return false;
    }

    protected function tryBrowserCache()
    {
        if (BROWSER_CACHE_DISABLE) {
            $this->debug(3, "Browser caching is disabled");
            return false;
        }
        if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $this->debug(3, "Got a conditional get");
            $mtime = false;
            //We've already checked if the real file exists in the constructor
            if (!is_file($this->cachefile)) {
                //If we don't have something cached, regenerate the cached image.
                return false;
            }
            if ($this->localImageMTime) {
                $mtime = $this->localImageMTime;
                $this->debug(3, "Local real file's modification time is $mtime");
            } else if (is_file($this->cachefile)) { //If it's not a local request then use the mtime of the cached file to determine the 304
                $mtime = @filemtime($this->cachefile);
                $this->debug(3, "Cached file's modification time is $mtime");
            }
            if (!$mtime) {
                return false;
            }

            $iftime = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
            $this->debug(3, "The conditional get's if-modified-since unixtime is $iftime");
            if ($iftime < 1) {
                $this->debug(3, "Got an invalid conditional get modified since time. Returning false.");
                return false;
            }
            if ($iftime < $mtime) { //Real file or cache file has been modified since last request, so force refetch.
                $this->debug(3, "File has been modified since last fetch.");
                return false;
            } else { //Otherwise serve a 304
                $this->debug(3, "File has not been modified since last get, so serving a 304.");
                header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
                $this->debug(1, "Returning 304 not modified");
                return true;
            }
        }
        return false;
    }

    protected function tryServerCache()
    {
        $this->debug(3, "Trying server cache");
        if (file_exists($this->cachefile)) {
            $this->debug(3, "Cachefile {$this->cachefile} exists");
            if ($this->isURL) {
                $this->debug(3, "This is an external request, so checking if the cachefile is empty which means the request failed previously.");
                if (filesize($this->cachefile) < 1) {
                    $this->debug(3, "Found an empty cachefile indicating a failed earlier request. Checking how old it is.");
                    //Fetching error occured previously
                    if (time() - @filemtime($this->cachefile) > WAIT_BETWEEN_FETCH_ERRORS) {
                        $this->debug(3, "File is older than " . WAIT_BETWEEN_FETCH_ERRORS . " seconds. Deleting and returning false so app can try and load file.");
                        @unlink($this->cachefile);
                        return false; //to indicate we didn't serve from cache and app should try and load
                    } else {
                        $this->debug(3, "Empty cachefile is still fresh so returning message saying we had an error fetching this image from remote host.");
                        $this->set404();
                        $this->error("An error occured fetching image.");
                        return false;
                    }
                }
            } else {
                $this->debug(3, "Trying to serve cachefile {$this->cachefile}");
            }
            if ($this->serveCacheFile()) {
                $this->debug(3, "Succesfully served cachefile {$this->cachefile}");
                return true;
            } else {
                $this->debug(3, "Failed to serve cachefile {$this->cachefile} - Deleting it from cache.");
                //Image serving failed. We can't retry at this point, but lets remove it from cache so the next request recreates it
                @unlink($this->cachefile);
                return true;
            }
        }
    }

    protected function error($err)
    {
        $this->debug(3, "Adding error message: $err");
        $this->errors[] = $err;
        return false;

    }

    protected function haveErrors()
    {
        if (sizeof($this->errors) > 0) {
            return true;
        }
        return false;
    }

    protected function serveErrors()
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
        if (!DISPLAY_ERROR_MESSAGES) {
            return;
        }
        $html = '<ul>';
        foreach ($this->errors as $err) {
            $html .= '<li>' . htmlentities($err) . '</li>';
        }
        $html .= '</ul>';
        echo '<h1>A haathumb error has occured</h1>The following error(s) occured:<br />' . $html . '<br />';
        echo '<br />Query String : ' . htmlentities($_SERVER['QUERY_STRING'], ENT_QUOTES);
    }

    protected function serveInternalImage()
    {
        $this->debug(3, "Local image path is $this->localImage");
        if (!$this->localImage) {
            $this->sanityFail("localImage not set after verifying it earlier in the code.");
            return false;
        }
        $fileSize = filesize($this->localImage);
        if ($fileSize > MAX_FILE_SIZE) {
            $this->error("The file you specified is greater than the maximum allowed file size.");
            return false;
        }
        if ($fileSize <= 0) {
            $this->error("The file you specified is <= 0 bytes.");
            return false;
        }
        $this->debug(3, "Calling processImageAndWriteToCache() for local image.");
        if ($this->processImageAndWriteToCache($this->localImage)) {
            $this->serveCacheFile();
            return true;
        } else {
            return false;
        }
    }

    protected function cleanCache()
    {
        if (FILE_CACHE_TIME_BETWEEN_CLEANS < 0) {
            return;
        }
        $this->debug(3, "cleanCache() called");
        $lastCleanFile = $this->cacheDirectory . '/haathumb_cacheLastCleanTime.touch';

        //If this is a new haathumb installation we need to create the file
        if (!is_file($lastCleanFile)) {
            $this->debug(1, "File tracking last clean doesn't exist. Creating $lastCleanFile");
            if (!touch($lastCleanFile)) {
                $this->error("Could not create cache clean timestamp file.");
            }
            return;
        }
        if (@filemtime($lastCleanFile) < (time() - FILE_CACHE_TIME_BETWEEN_CLEANS)) { //Cache was last cleaned more than 1 day ago
            $this->debug(1, "Cache was last cleaned more than " . FILE_CACHE_TIME_BETWEEN_CLEANS . " seconds ago. Cleaning now.");
            // Very slight race condition here, but worst case we'll have 2 or 3 servers cleaning the cache simultaneously once a day.
            if (!touch($lastCleanFile)) {
                $this->error("Could not create cache clean timestamp file.");
            }
            $files = glob($this->cacheDirectory . '/*' . FILE_CACHE_SUFFIX);
            if ($files) {
                $timeAgo = time() - FILE_CACHE_MAX_FILE_AGE;
                foreach ($files as $file) {
                    if (@filemtime($file) < $timeAgo) {
                        $this->debug(3, "Deleting cache file $file older than max age: " . FILE_CACHE_MAX_FILE_AGE . " seconds");
                        @unlink($file);
                    }
                }
            }
            return true;
        } else {
            $this->debug(3, "Cache was cleaned less than " . FILE_CACHE_TIME_BETWEEN_CLEANS . " seconds ago so no cleaning needed.");
        }
        return false;
    }

    protected function processImageAndWriteToCache($localImage)
    {
        $sData = getimagesize($localImage);
        $origType = $sData[2];
        $mimeType = $sData['mime'];

        $this->debug(3, "Mime type of image is $mimeType");
        if (!preg_match('/^image\/(?:gif|jpg|jpeg|png)$/i', $mimeType)) {
            return $this->error("The image being resized is not a valid gif, jpg or png.");
        }

        if (!function_exists('imagecreatetruecolor')) {
            return $this->error('GD Library Error: imagecreatetruecolor does not exist - please contact your webhost and ask them to install the GD library');
        }

        if (function_exists('imagefilter') && defined('IMG_FILTER_NEGATE')) {
            $imageFilters = array(
                1 => array(IMG_FILTER_NEGATE, 0),
                2 => array(IMG_FILTER_GRAYSCALE, 0),
                3 => array(IMG_FILTER_BRIGHTNESS, 1),
                4 => array(IMG_FILTER_CONTRAST, 1),
                5 => array(IMG_FILTER_COLORIZE, 4),
                6 => array(IMG_FILTER_EDGEDETECT, 0),
                7 => array(IMG_FILTER_EMBOSS, 0),
                8 => array(IMG_FILTER_GAUSSIAN_BLUR, 0),
                9 => array(IMG_FILTER_SELECTIVE_BLUR, 0),
                10 => array(IMG_FILTER_MEAN_REMOVAL, 0),
                11 => array(IMG_FILTER_SMOOTH, 1), // Added support to adjust smoothness level
                12 => array(IMG_FILTER_PIXELATE, 2), // Added pixelate support
            );
        }

        // get standard input properties
        $new_width = (int)abs($this->param('w', 0));
        $new_height = (int)abs($this->param('h', 0));
        $zoom_crop = (int)$this->param('zc', DEFAULT_ZC);
        $quality = (int)abs($this->param('q', DEFAULT_Q));
        $align = $this->cropTop ? 't' : $this->param('a', 'c');
        $filters = $this->param('f', DEFAULT_F);
        $sharpen = (bool)$this->param('s', DEFAULT_S);
        $canvas_color = $this->param('cc', DEFAULT_CC);
        $canvas_trans = (bool)$this->param('ct', '1');

        // set default width and height if neither are set already
        if ($new_width == 0 && $new_height == 0) {
            $new_width = (int)DEFAULT_WIDTH;
            $new_height = (int)DEFAULT_HEIGHT;
        }

        // ensure size limits can not be abused
        $new_width = min($new_width, MAX_WIDTH);
        $new_height = min($new_height, MAX_HEIGHT);

        // set memory limit to be able to have enough space to resize larger images
        $this->setMemoryLimit();

        // open the existing image
        $image = $this->openImage($mimeType, $localImage);
        if ($image === false) {
            return $this->error('Unable to open image.');
        }

        // Get original width and height
        $width = imagesx($image);
        $height = imagesy($image);
        $origin_x = 0;
        $origin_y = 0;

        // generate new w/h if not provided
        if ($new_width && !$new_height) {
            $new_height = floor($height * ($new_width / $width));
        } else if ($new_height && !$new_width) {
            $new_width = floor($width * ($new_height / $height));
        }

        // scale down and add borders
        if ($zoom_crop == 3) {

            $final_height = $height * ($new_width / $width);

            if ($final_height > $new_height) {
                $new_width = $width * ($new_height / $height);
            } else {
                $new_height = $final_height;
            }

        }

        // create a new true color image
        $canvas = imagecreatetruecolor($new_width, $new_height);
        imagealphablending($canvas, false);

        if (strlen($canvas_color) == 3) { //if is 3-char notation, edit string into 6-char notation
            $canvas_color = str_repeat(substr($canvas_color, 0, 1), 2) . str_repeat(substr($canvas_color, 1, 1), 2) . str_repeat(substr($canvas_color, 2, 1), 2);
        } else if (strlen($canvas_color) != 6) {
            $canvas_color = DEFAULT_CC; // on error return default canvas color
        }

        $canvas_color_R = hexdec(substr($canvas_color, 0, 2));
        $canvas_color_G = hexdec(substr($canvas_color, 2, 2));
        $canvas_color_B = hexdec(substr($canvas_color, 4, 2));

        // Create a new transparent color for image
        // If is a png and PNG_IS_TRANSPARENT is false then remove the alpha transparency
        // (and if is set a canvas color show it in the background)
        if (preg_match('/^image\/png$/i', $mimeType) && !PNG_IS_TRANSPARENT && $canvas_trans) {
            $color = imagecolorallocatealpha($canvas, $canvas_color_R, $canvas_color_G, $canvas_color_B, 127);
        } else {
            $color = imagecolorallocatealpha($canvas, $canvas_color_R, $canvas_color_G, $canvas_color_B, 0);
        }


        // Completely fill the background of the new image with allocated color.
        imagefill($canvas, 0, 0, $color);

        // scale down and add borders
        if ($zoom_crop == 2) {

            $final_height = $height * ($new_width / $width);

            if ($final_height > $new_height) {

                $origin_x = $new_width / 2;
                $new_width = $width * ($new_height / $height);
                $origin_x = round($origin_x - ($new_width / 2));

            } else {

                $origin_y = $new_height / 2;
                $new_height = $final_height;
                $origin_y = round($origin_y - ($new_height / 2));

            }

        }

        // Restore transparency blending
        imagesavealpha($canvas, true);

        if ($zoom_crop > 0) {

            $src_x = $src_y = 0;
            $src_w = $width;
            $src_h = $height;

            $cmp_x = $width / $new_width;
            $cmp_y = $height / $new_height;

            // calculate x or y coordinate and width or height of source
            if ($cmp_x > $cmp_y) {

                $src_w = round($width / $cmp_x * $cmp_y);
                $src_x = round(($width - ($width / $cmp_x * $cmp_y)) / 2);

            } else if ($cmp_y > $cmp_x) {

                $src_h = round($height / $cmp_y * $cmp_x);
                $src_y = round(($height - ($height / $cmp_y * $cmp_x)) / 2);

            }

            // positional cropping!
            if ($align) {
                if (strpos($align, 't') !== false) {
                    $src_y = 0;
                }
                if (strpos($align, 'b') !== false) {
                    $src_y = $height - $src_h;
                }
                if (strpos($align, 'l') !== false) {
                    $src_x = 0;
                }
                if (strpos($align, 'r') !== false) {
                    $src_x = $width - $src_w;
                }
            }

            imagecopyresampled($canvas, $image, $origin_x, $origin_y, $src_x, $src_y, $new_width, $new_height, $src_w, $src_h);

        } else {

            // copy and resize part of an image with resampling
            imagecopyresampled($canvas, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        }

        if ($filters != '' && function_exists('imagefilter') && defined('IMG_FILTER_NEGATE')) {
            // apply filters to image
            $filterList = explode('|', $filters);
            foreach ($filterList as $fl) {

                $filterSettings = explode(',', $fl);
                if (isset ($imageFilters[$filterSettings[0]])) {

                    for ($i = 0; $i < 4; $i++) {
                        if (!isset ($filterSettings[$i])) {
                            $filterSettings[$i] = null;
                        } else {
                            $filterSettings[$i] = (int)$filterSettings[$i];
                        }
                    }

                    switch ($imageFilters[$filterSettings[0]][1]) {

                        case 1:

                            imagefilter($canvas, $imageFilters[$filterSettings[0]][0], $filterSettings[1]);
                            break;

                        case 2:

                            imagefilter($canvas, $imageFilters[$filterSettings[0]][0], $filterSettings[1], $filterSettings[2]);
                            break;

                        case 3:

                            imagefilter($canvas, $imageFilters[$filterSettings[0]][0], $filterSettings[1], $filterSettings[2], $filterSettings[3]);
                            break;

                        case 4:

                            imagefilter($canvas, $imageFilters[$filterSettings[0]][0], $filterSettings[1], $filterSettings[2], $filterSettings[3], $filterSettings[4]);
                            break;

                        default:

                            imagefilter($canvas, $imageFilters[$filterSettings[0]][0]);
                            break;

                    }
                }
            }
        }

        // sharpen image
        if ($sharpen && function_exists('imageconvolution')) {

            $sharpenMatrix = array(
                array(-1, -1, -1),
                array(-1, 16, -1),
                array(-1, -1, -1),
            );

            $divisor = 8;
            $offset = 0;

            imageconvolution($canvas, $sharpenMatrix, $divisor, $offset);

        }
        //Straight from Wordpress core code. Reduces filesize by up to 70% for PNG's
        if ((IMAGETYPE_PNG == $origType || IMAGETYPE_GIF == $origType) && function_exists('imageistruecolor') && !imageistruecolor($image) && imagecolortransparent($image) > 0) {
            imagetruecolortopalette($canvas, false, imagecolorstotal($image));
        }

        $imgType = "";
        $tempfile = tempnam($this->cacheDirectory, 'haathumb_tmpimg_');
        if (preg_match('/^image\/(?:jpg|jpeg)$/i', $mimeType)) {
            $imgType = 'jpg';
            imageinterlace($canvas, JPEG_IS_PROGRESSIVE);
            imagejpeg($canvas, $tempfile, $quality);
        } else if (preg_match('/^image\/png$/i', $mimeType)) {
            $imgType = 'png';
            imagepng($canvas, $tempfile, floor($quality * 0.09));
        } else if (preg_match('/^image\/gif$/i', $mimeType)) {
            $imgType = 'gif';
            imagegif($canvas, $tempfile);
        } else {
            return $this->sanityFail("Could not match mime type after verifying it previously.");
        }

        if ($imgType == 'png' && OPTIPNG_ENABLED && OPTIPNG_PATH && @is_file(OPTIPNG_PATH)) {
            $exec = OPTIPNG_PATH;
            $this->debug(3, "optipng'ing $tempfile");
            $presize = filesize($tempfile);
            $out = `$exec -o1 $tempfile`; //you can use up to -o7 but it really slows things down
            clearstatcache();
            $aftersize = filesize($tempfile);
            $sizeDrop = $presize - $aftersize;
            if ($sizeDrop > 0) {
                $this->debug(1, "optipng reduced size by $sizeDrop");
            } else if ($sizeDrop < 0) {
                $this->debug(1, "optipng increased size! Difference was: $sizeDrop");
            } else {
                $this->debug(1, "optipng did not change image size.");
            }
        } else if ($imgType == 'png' && PNGCRUSH_ENABLED && PNGCRUSH_PATH && @is_file(PNGCRUSH_PATH)) {
            $exec = PNGCRUSH_PATH;
            $tempfile2 = tempnam($this->cacheDirectory, 'haathumb_tmpimg_');
            $this->debug(3, "pngcrush'ing $tempfile to $tempfile2");
            $out = `$exec $tempfile $tempfile2`;
            $todel = "";
            if (is_file($tempfile2)) {
                $sizeDrop = filesize($tempfile) - filesize($tempfile2);
                if ($sizeDrop > 0) {
                    $this->debug(1, "pngcrush was succesful and gave a $sizeDrop byte size reduction");
                    $todel = $tempfile;
                    $tempfile = $tempfile2;
                } else {
                    $this->debug(1, "pngcrush did not reduce file size. Difference was $sizeDrop bytes.");
                    $todel = $tempfile2;
                }
            } else {
                $this->debug(3, "pngcrush failed with output: $out");
                $todel = $tempfile2;
            }
            @unlink($todel);
        }

        $this->debug(3, "Rewriting image with security header.");
        $tempfile4 = tempnam($this->cacheDirectory, 'haathumb_tmpimg_');
        $context = stream_context_create();
        $fp = fopen($tempfile, 'r', 0, $context);
        file_put_contents($tempfile4, $this->filePrependSecurityBlock . $imgType . ' ?' . '>'); //6 extra bytes, first 3 being image type
        file_put_contents($tempfile4, $fp, FILE_APPEND);
        fclose($fp);
        @unlink($tempfile);
        $this->debug(3, "Locking and replacing cache file.");
        $lockFile = $this->cachefile . '.lock';
        $fh = fopen($lockFile, 'w');
        if (!$fh) {
            return $this->error("Could not open the lockfile for writing an image.");
        }
        if (flock($fh, LOCK_EX)) {
            @unlink($this->cachefile); //rename generally overwrites, but doing this in case of platform specific quirks. File might not exist yet.
            rename($tempfile4, $this->cachefile);
            flock($fh, LOCK_UN);
            fclose($fh);
            @unlink($lockFile);
        } else {
            fclose($fh);
            @unlink($lockFile);
            @unlink($tempfile4);
            return $this->error("Could not get a lock for writing.");
        }
        $this->debug(3, "Done image replace with security header. Cleaning up and running cleanCache()");
        imagedestroy($canvas);
        imagedestroy($image);
        return true;
    }

    protected function calcDocRoot()
    {
        $docRoot = @$_SERVER['DOCUMENT_ROOT'];
        if (defined('LOCAL_FILE_BASE_DIRECTORY')) {
            $docRoot = LOCAL_FILE_BASE_DIRECTORY;
        }
        if (!isset($docRoot)) {
            $this->debug(3, "DOCUMENT_ROOT is not set. This is probably windows. Starting search 1.");
            if (isset($_SERVER['SCRIPT_FILENAME'])) {
                $docRoot = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
                $this->debug(3, "Generated docRoot using SCRIPT_FILENAME and PHP_SELF as: $docRoot");
            }
        }
        if (!isset($docRoot)) {
            $this->debug(3, "DOCUMENT_ROOT still is not set. Starting search 2.");
            if (isset($_SERVER['PATH_TRANSLATED'])) {
                $docRoot = str_replace('\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
                $this->debug(3, "Generated docRoot using PATH_TRANSLATED and PHP_SELF as: $docRoot");
            }
        }
        if ($docRoot && $_SERVER['DOCUMENT_ROOT'] != '/') {
            $docRoot = preg_replace('/\/$/', '', $docRoot);
        }
        $this->debug(3, "Doc root is: " . $docRoot);
        $this->docRoot = $docRoot;

    }

    protected function getLocalImagePath($src)
    {
        $src = ltrim($src, '/'); //strip off the leading '/'
        if (!$this->docRoot) {
            $this->debug(3, "We have no document root set, so as a last resort, lets check if the image is in the current dir and serve that.");
            //We don't support serving images outside the current dir if we don't have a doc root for security reasons.
            $file = preg_replace('/^.*?([^\/\\\\]+)$/', '$1', $src); //strip off any path info and just leave the filename.
            if (is_file($file)) {
                return $this->realpath($file);
            }
            return $this->error("Could not find your website document root and the file specified doesn't exist in haathumbs directory. We don't support serving files outside haathumb's directory without a document root for security reasons.");
        } else if (!is_dir($this->docRoot)) {
            $this->error("Server path does not exist. Ensure variable \$_SERVER['DOCUMENT_ROOT'] is set correctly");
        }

        //Do not go past this point without docRoot set

        //Try src under docRoot
        if (file_exists($this->docRoot . '/' . $src)) {
            $this->debug(3, "Found file as " . $this->docRoot . '/' . $src);
            $real = $this->realpath($this->docRoot . '/' . $src);
            if (stripos($real, $this->docRoot) === 0) {
                return $real;
            } else {
                $this->debug(1, "Security block: The file specified occurs outside the document root.");
                //allow search to continue
            }
        }
        //Check absolute paths and then verify the real path is under doc root
        $absolute = $this->realpath('/' . $src);
        if ($absolute && file_exists($absolute)) { //realpath does file_exists check, so can probably skip the exists check here
            $this->debug(3, "Found absolute path: $absolute");
            if (!$this->docRoot) {
                $this->sanityFail("docRoot not set when checking absolute path.");
            }
            if (stripos($absolute, $this->docRoot) === 0) {
                return $absolute;
            } else {
                $this->debug(1, "Security block: The file specified occurs outside the document root.");
                //and continue search
            }
        }

        $base = $this->docRoot;

        // account for Windows directory structure
        if (strstr($_SERVER['SCRIPT_FILENAME'], ':')) {
            $sub_directories = explode('\\', str_replace($this->docRoot, '', $_SERVER['SCRIPT_FILENAME']));
        } else {
            $sub_directories = explode('/', str_replace($this->docRoot, '', $_SERVER['SCRIPT_FILENAME']));
        }

        foreach ($sub_directories as $sub) {
            $base .= $sub . '/';
            $this->debug(3, "Trying file as: " . $base . $src);
            if (file_exists($base . $src)) {
                $this->debug(3, "Found file as: " . $base . $src);
                $real = $this->realpath($base . $src);
                if (stripos($real, $this->realpath($this->docRoot)) === 0) {
                    return $real;
                } else {
                    $this->debug(1, "Security block: The file specified occurs outside the document root.");
                    //And continue search
                }
            }
        }
        return false;
    }

    protected function realpath($path)
    {
        //try to remove any relative paths
        $remove_relatives = '/\w+\/\.\.\//';
        while (preg_match($remove_relatives, $path)) {
            $path = preg_replace($remove_relatives, '', $path);
        }
        //if any remain use PHP realpath to strip them out, otherwise return $path
        //if using realpath, any symlinks will also be resolved
        return preg_match('#^\.\./|/\.\./#', $path) ? realpath($path) : $path;
    }

    protected function toDelete($name)
    {
        $this->debug(3, "Scheduling file $name to delete on destruct.");
        $this->toDeletes[] = $name;
    }

    protected function serveCacheFile()
    {
        $this->debug(3, "Serving {$this->cachefile}");
        if (!is_file($this->cachefile)) {
            $this->error("serveCacheFile called in haathumb but we couldn't find the cached file.");
            return false;
        }
        $fp = fopen($this->cachefile, 'rb');
        if (!$fp) {
            return $this->error("Could not open cachefile.");
        }
        fseek($fp, strlen($this->filePrependSecurityBlock), SEEK_SET);
        $imgType = fread($fp, 3);
        fseek($fp, 3, SEEK_CUR);
        if (ftell($fp) != strlen($this->filePrependSecurityBlock) + 6) {
            @unlink($this->cachefile);
            return $this->error("The cached image file seems to be corrupt.");
        }
        $imageDataSize = filesize($this->cachefile) - (strlen($this->filePrependSecurityBlock) + 6);
        $this->sendImageHeaders($imgType, $imageDataSize);
        $bytesSent = @fpassthru($fp);
        fclose($fp);
        if ($bytesSent > 0) {
            return true;
        }
        $content = file_get_contents($this->cachefile);
        if ($content != FALSE) {
            $content = substr($content, strlen($this->filePrependSecurityBlock) + 6);
            echo $content;
            $this->debug(3, "Served using file_get_contents and echo");
            return true;
        } else {
            $this->error("Cache file could not be loaded.");
            return false;
        }
    }

    protected function sendImageHeaders($mimeType, $dataSize)
    {
        if (!preg_match('/^image\//i', $mimeType)) {
            $mimeType = 'image/' . $mimeType;
        }
        if (strtolower($mimeType) == 'image/jpg') {
            $mimeType = 'image/jpeg';
        }
        $gmdate_expires = gmdate('D, d M Y H:i:s', strtotime('now +10 days')) . ' GMT';
        $gmdate_modified = gmdate('D, d M Y H:i:s') . ' GMT';
        // send content headers then display image
        header('Content-Type: ' . $mimeType);
        header('Accept-Ranges: none'); //Changed this because we don't accept range requests
        header('Last-Modified: ' . $gmdate_modified);
        header('Content-Length: ' . $dataSize);
        if (BROWSER_CACHE_DISABLE) {
            $this->debug(3, "Browser cache is disabled so setting non-caching headers.");
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header("Pragma: no-cache");
            header('Expires: ' . gmdate('D, d M Y H:i:s', time()));
        } else {
            $this->debug(3, "Browser caching is enabled");
            header('Cache-Control: max-age=' . BROWSER_CACHE_MAX_AGE . ', must-revalidate');
            header('Expires: ' . $gmdate_expires);
        }
        return true;
    }


    protected function param($property, $default = '')
    {
        if (isset ($_GET[$property])) {
            return $_GET[$property];
        } else {
            return $default;
        }
    }

    protected function openImage($mimeType, $src)
    {
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($src);
                break;

            case 'image/png':
                $image = imagecreatefrompng($src);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;

            case 'image/gif':
                $image = imagecreatefromgif($src);
                break;

            default:
                $this->error("Unrecognised mimeType");
        }

        return $image;
    }

    protected function getIP()
    {
        $rem = @$_SERVER["REMOTE_ADDR"];
        $ff = @$_SERVER["HTTP_X_FORWARDED_FOR"];
        $ci = @$_SERVER["HTTP_CLIENT_IP"];
        if (preg_match('/^(?:192\.168|172\.16|10\.|127\.)/', $rem)) {
            if ($ff) {
                return $ff;
            }
            if ($ci) {
                return $ci;
            }
            return $rem;
        } else {
            if ($rem) {
                return $rem;
            }
            if ($ff) {
                return $ff;
            }
            if ($ci) {
                return $ci;
            }
            return "UNKNOWN";
        }
    }

    protected function debug($level, $msg)
    {
        if (DEBUG_ON && $level <= DEBUG_LEVEL) {
            $execTime = sprintf('%.6f', microtime(true) - $this->startTime);
            $tick = sprintf('%.6f', 0);
            if ($this->lastBenchTime > 0) {
                $tick = sprintf('%.6f', microtime(true) - $this->lastBenchTime);
            }
            $this->lastBenchTime = microtime(true);
            error_log("haathumb Debug line " . __LINE__ . " [$execTime : $tick]: $msg");
        }
    }

    protected function sanityFail($msg)
    {
        return $this->error("There is a problem in the haathumb code. Message: Please report this error at <a href='http://code.google.com/p/haathumb/issues/list'>haathumb's bug tracking page</a>: $msg");
    }

    protected function getMimeType($file)
    {
        $info = getimagesize($file);
        if (is_array($info) && $info['mime']) {
            return $info['mime'];
        }
        return '';
    }

    protected function setMemoryLimit()
    {
        $inimem = ini_get('memory_limit');
        $inibytes = haathumb::returnBytes($inimem);
        $ourbytes = haathumb::returnBytes(MEMORY_LIMIT);
        if ($inibytes < $ourbytes) {
            ini_set('memory_limit', MEMORY_LIMIT);
            $this->debug(3, "Increased memory from $inimem to " . MEMORY_LIMIT);
        } else {
            $this->debug(3, "Not adjusting memory size because the current setting is " . $inimem . " and our size of " . MEMORY_LIMIT . " is smaller.");
        }
    }

    protected static function returnBytes($size_str)
    {
        switch (substr($size_str, -1)) {
            case 'M':
            case 'm':
                return (int)$size_str * 1048576;
            case 'K':
            case 'k':
                return (int)$size_str * 1024;
            case 'G':
            case 'g':
                return (int)$size_str * 1073741824;
            default:
                return $size_str;
        }
    }


    protected function serveImg($file)
    {
        $s = getimagesize($file);
        if (!($s && $s['mime'])) {
            return false;
        }
        header('Content-Type: ' . $s['mime']);
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header("Pragma: no-cache");
        $bytes = @readfile($file);
        if ($bytes > 0) {
            return true;
        }
        $content = @file_get_contents($file);
        if ($content != FALSE) {
            echo $content;
            return true;
        }
        return false;

    }

    protected function set404()
    {
        $this->is404 = true;
    }

    protected function is404()
    {
        return $this->is404;
    }
}
