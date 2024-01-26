<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Error;

/*
 * HTTP::Download
 *
 * PHP versions 4 and 5
 *
 * @category   HTTP
 * @package    HTTP_Download
 * @author     Michael Wallner <mike@php.net>
 * @copyright  2003-2005 Michael Wallner
 * @license    BSD, revised
 * @version    CVS: $Id: Download.php,v 1.78 2007/05/02 19:29:15 mike Exp $
 * @link       http://pear.php.net/package/HTTP_Download
 */

// {{{ constants
/**#@+ Use with HTTP_Download::setContentDisposition() **/
/*
 * Send data as attachment
 */
define('HTTP_DOWNLOAD_ATTACHMENT', 'attachment');
/*
 * Send data inline
 */
define('HTTP_DOWNLOAD_INLINE', 'inline');
/**#@-**/

/**#@+ Use with HTTP_Download::sendArchive() **/
/*
 * Send as uncompressed tar archive
 */
define('HTTP_DOWNLOAD_TAR', 'TAR');
/*
 * Send as gzipped tar archive
 */
define('HTTP_DOWNLOAD_TGZ', 'TGZ');
/*
 * Send as bzip2 compressed tar archive
 */
define('HTTP_DOWNLOAD_BZ2', 'BZ2');
/*
 * Send as zip archive
 */
define('HTTP_DOWNLOAD_ZIP', 'ZIP');
/**#@-**/

/**#@+
 * Error constants
 */
define('HTTP_DOWNLOAD_E_HEADERS_SENT', -1);
define('HTTP_DOWNLOAD_E_NO_EXT_ZLIB', -2);
define('HTTP_DOWNLOAD_E_NO_EXT_MMAGIC', -3);
define('HTTP_DOWNLOAD_E_INVALID_FILE', -4);
define('HTTP_DOWNLOAD_E_INVALID_PARAM', -5);
define('HTTP_DOWNLOAD_E_INVALID_RESOURCE', -6);
define('HTTP_DOWNLOAD_E_INVALID_REQUEST', -7);
define('HTTP_DOWNLOAD_E_INVALID_CONTENT_TYPE', -8);
define('HTTP_DOWNLOAD_E_INVALID_ARCHIVE_TYPE', -9);
/**#@-**/
// }}}

/**
 * Send HTTP Downloads/Responses.
 *
 * With this package you can handle (hidden) downloads.
 * It supports partial downloads, resuming and sending
 * raw data ie. from database BLOBs.
 *
 * <i>ATTENTION:</i>
 * You shouldn't use this package together with ob_gzhandler or
 * zlib.output_compression enabled in your php.ini, especially
 * if you want to send already gzipped data!
 *
 * @version  $Revision: 1.78 $
 */
class HTTP_Download
{
    // {{{ protected member variables
    /**
     * Path to file for download.
     *
     * @see     HTTP_Download::setFile()
     * @var string
     */
    public $file = '';

    /**
     * Data for download.
     *
     * @see     HTTP_Download::setData()
     * @var string
     */
    public $data = null;

    /**
     * Resource handle for download.
     *
     * @see     HTTP_Download::setResource()
     * @var int
     */
    public $handle = null;

    /**
     * Whether to gzip the download.
     *
     * @var bool
     */
    public $gzip = false;

    /**
     * Whether to allow caching of the download on the clients side.
     *
     * @var bool
     */
    public $cache = true;

    /**
     * Size of download.
     *
     * @var int
     */
    public $size = 0;

    /**
     * Last modified.
     *
     * @var int
     */
    public $lastModified = 0;

    /*
     * HTTP headers
     *
     * @access  protected
     * @var     array
     */
    public $headers = [
        'Content-Type' => 'application/x-octetstream',
        'Pragma' => 'cache',
        'Cache-Control' => 'public, must-revalidate, max-age=0',
        'Accept-Ranges' => 'bytes',
        'X-Sent-By' => 'Angie HTTP Download',
    ];

    /**
     * HTTP_Header.
     *
     * @var object
     */
    public $HTTP = null;

    /**
     * ETag.
     *
     * @var string
     */
    public $etag = '';

    /**
     * Buffer Size.
     *
     * @var int
     */
    public $bufferSize = 2097152;

    /**
     * Throttle Delay.
     *
     * @var float
     */
    public $throttleDelay = 0;

    /**
     * Sent Bytes.
     *
     * @var int
     */
    public $sentBytes = 0;
    // }}}

    // {{{ constructor

    /**
     * Constructor.
     *
     * Set supplied parameters.
     *
     * @param array $params associative array of parameters
     *
     *          <b>one of:</b>
     *                  o 'file'                => path to file for download
     *                  o 'data'                => raw data for download
     *                  o 'resource'            => resource handle for download
     * <br/>
     *          <b>and any of:</b>
     *                  o 'cache'               => whether to allow cs caching
     *                  o 'gzip'                => whether to gzip the download
     *                  o 'lastmodified'        => unix timestamp
     *                  o 'contenttype'         => content type of download
     *                  o 'contentdisposition'  => content disposition
     *                  o 'buffersize'          => amount of bytes to buffer
     *                  o 'throttledelay'       => amount of secs to sleep
     *                  o 'cachecontrol'        => cache privacy and validity
     *
     * <br />
     * 'Content-Disposition' is not HTTP compliant, but most browsers
     * follow this header, so it was borrowed from MIME standard.
     *
     * It looks like this: <br />
     * "Content-Disposition: attachment; filename=example.tgz"
     *
     * @see HTTP_Download::setContentDisposition()
     */
    public function __construct($params = [])
    {
        $this->HTTP = new HTTP_Header();
        $this->setParams($params);
    }

    // }}}

    // {{{ public methods

    /**
     * Set parameters.
     *
     * Set supplied parameters through its accessor methods.
     *
     * @return mixed Returns true on success or PEAR_Error on failure
     * @param  array $params associative array of parameters
     *
     * @see     HTTP_Download::HTTP_Download()
     */
    public function setParams($params)
    {
        foreach ((array) $params as $param => $value) {
            $method = 'set' . $param;

            if (!method_exists($this, $method)) {
                return new Error("Method '$method' doesn't exist.");
            }

            $e = call_user_func_array([&$this, $method], (array) $value);

            //if (is_error($e)) {
            return $e;
            //}
        }

        return true;
    }

    /**
     * Set path to file for download.
     *
     * The Last-Modified header will be set to files filemtime(), actually.
     * Returns PEAR_Error (HTTP_DOWNLOAD_E_INVALID_FILE) if file doesn't exist.
     * Sends HTTP 404 status if $send_404 is set to true.
     *
     * @return mixed  Returns true on success or PEAR_Error on failure
     * @param  string $file     path to file for download
     * @param  bool   $send_404 whether to send HTTP/404 if
     *                          the file wasn't found
     */
    public function setFile($file, $send_404 = true)
    {
        $file = realpath($file);
        if (!is_file($file)) {
            if ($send_404) {
                $this->HTTP->sendStatusCode(404);
            }

            return new Error("File '$file' not found");
        }
        $this->setLastModified(filemtime($file));
        $this->file = $file;
        $this->size = filesize($file);

        return true;
    }

    /**
     * Set "Last-Modified".
     *
     * This is usually determined by filemtime() in HTTP_Download::setFile()
     * If you set raw data for download with HTTP_Download::setData() and you
     * want do send an appropiate "Last-Modified" header, you should call this
     * method.
     *
     * @param   int     unix timestamp
     */
    public function setLastModified($last_modified)
    {
        $this->lastModified = $this->headers['Last-Modified'] = (int) $last_modified;
    }

    /**
     * Set data for download.
     *
     * Set $data to null if you want to unset this.
     *
     * @param $data raw data to send
     */
    public function setData($data = null)
    {
        $this->data = $data;
        $this->size = strlen($data);
    }

    /**
     * Set resource for download.
     *
     * The resource handle supplied will be closed after sending the download.
     * Returns a PEAR_Error (HTTP_DOWNLOAD_E_INVALID_RESOURCE) if $handle
     * is no valid resource. Set $handle to null if you want to unset this.
     *
     * @return mixed Returns true on success or PEAR_Error on failure
     * @param  int   $handle resource handle
     */
    public function setResource($handle = null)
    {
        if (!isset($handle)) {
            $this->handle = null;
            $this->size = 0;

            return true;
        }

        if (is_resource($handle)) {
            $this->handle = $handle;
            $filestats = fstat($handle);
            $this->size = $filestats['size'];

            return true;
        }

        return new Error("Handle '$handle' is no valid resource");
    }

    /**
     * Whether to gzip the download.
     *
     * Returns a PEAR_Error (HTTP_DOWNLOAD_E_NO_EXT_ZLIB)
     * if ext/zlib is not available/loadable.
     *
     * @return mixed Returns true on success or PEAR_Error on failure
     * @param  bool  $gzip whether to gzip the download
     */
    public function setGzip($gzip = false)
    {
        throw new NotImplementedError('HTTP_Download::setGzip');
//        if ($gzip && !PEAR::loadExtension('zlib')) {
//            return PEAR::raiseError(
//                'GZIP compression (ext/zlib) not available.',
//                HTTP_DOWNLOAD_E_NO_EXT_ZLIB
//            );
//        }
//        $this->gzip = (bool) $gzip;
//        return true;
    }

    /**
     * Whether to allow caching.
     *
     * If set to true (default) we'll send some headers that are commonly
     * used for caching purposes like ETag, Cache-Control and Last-Modified.
     *
     * If caching is disabled, we'll send the download no matter if it
     * would actually be cached at the client side.
     *
     * @param bool $cache whether to allow caching
     */
    public function setCache($cache = true)
    {
        $this->cache = (bool) $cache;
    }

    /**
     * Whether to allow proxies to cache.
     *
     * If set to 'private' proxies shouldn't cache the response.
     * This setting defaults to 'public' and affects only cached responses.
     *
     * @return bool
     * @param  string $cache  private or public
     * @param  int    $maxage maximum age of the client cache entry
     */
    public function setCacheControl($cache = 'public', $maxage = 0)
    {
        switch ($cache = strtolower($cache)) {
            case 'private':
            case 'public':
                $this->headers['Cache-Control'] =
                    $cache . ', must-revalidate, max-age=' . abs($maxage);

                return true;
                break;
        }

        return false;
    }

    /**
     * Set ETag.
     *
     * Sets a user-defined ETag for cache-validation.  The ETag is usually
     * generated by HTTP_Download through its payload information.
     *
     * @param string $etag Entity tag used for strong cache validation
     */
    public function setETag($etag = null)
    {
        $this->etag = (string) $etag;
    }

    /**
     * Set Size of Buffer.
     *
     * The amount of bytes specified as buffer size is the maximum amount
     * of data read at once from resources or files.  The default size is 2M
     * (2097152 bytes).  Be aware that if you enable gzip compression and
     * you set a very low buffer size that the actual file size may grow
     * due to added gzip headers for each sent chunk of the specified size.
     *
     * Returns PEAR_Error (HTTP_DOWNLOAD_E_INVALID_PARAM) if $size is not
     * greater than 0 bytes.
     *
     * @return mixed Returns true on success or PEAR_Error on failure
     * @param  int   $bytes Amount of bytes to use as buffer
     */
    public function setBufferSize($bytes = 2097152)
    {
        if (0 >= $bytes) {
            return new Error('Buffer size must be greater than 0 bytes (' . $bytes . ' given)');
        }
        $this->bufferSize = abs($bytes);

        return true;
    }

    /**
     * Set Throttle Delay.
     *
     * Set the amount of seconds to sleep after each chunck that has been
     * sent.  One can implement some sort of throttle through adjusting the
     * buffer size and the throttle delay.  With the following settings
     * HTTP_Download will sleep a second after each 25 K of data sent.
     *
     * <code>
     *  Array(
     *      'throttledelay' => 1,
     *      'buffersize'    => 1024 * 25,
     *  )
     * </code>
     *
     * Just be aware that if gzipp'ing is enabled, decreasing the chunk size
     * too much leads to proportionally increased network traffic due to added
     * gzip header and bottom bytes around each chunk.
     *
     * @param float $seconds Amount of seconds to sleep after each
     *                       chunk that has been sent
     */
    public function setThrottleDelay($seconds = 0)
    {
        $this->throttleDelay = abs($seconds) * 1000;
    }

    /**
     * Set content type of the download.
     *
     * Default content type of the download will be 'application/x-octetstream'.
     * Returns PEAR_Error (HTTP_DOWNLOAD_E_INVALID_CONTENT_TYPE) if
     * $content_type doesn't seem to be valid.
     *
     * @return mixed  Returns true on success or PEAR_Error on failure
     * @param  string $content_type content type of file for download
     */
    public function setContentType($content_type = 'application/x-octetstream')
    {
        if (!preg_match('/^[a-z]+\w*\/[a-z]+[\w.;= +-]*$/', $content_type)) {
            return new Error("Invalid content type '$content_type' supplied.");
        }
        $this->headers['Content-Type'] = $content_type;

        return true;
    }

    /**
     * Static send.
     *
     * @see     HTTP_Download::HTTP_Download()
     * @see     HTTP_Download::send()
     *
     * @static
     * @return mixed Returns true on success or PEAR_Error on failure
     * @param  array $params associative array of parameters
     * @param  bool  $guess  whether HTTP_Download::guessContentType()
     *                       should be called
     */
    public function staticSend($params, $guess = false)
    {
        $d = new self();
        $d->setParams($params);

        if ($guess) {
            $d->guessContentType();
        }

        return $d->send();
    }

    /**
     * Guess content type of file.
     *
     * First we try to use PEAR::MIME_Type, if installed, to detect the content
     * type, else we check if ext/mime_magic is loaded and properly configured.
     *
     * Returns PEAR_Error if:
     *      o if PEAR::MIME_Type failed to detect a proper content type
     *        (HTTP_DOWNLOAD_E_INVALID_CONTENT_TYPE)
     *      o ext/magic.mime is not installed, or not properly configured
     *        (HTTP_DOWNLOAD_E_NO_EXT_MMAGIC)
     *      o mime_content_type() couldn't guess content type or returned
     *        a content type considered to be bogus by setContentType()
     *        (HTTP_DOWNLOAD_E_INVALID_CONTENT_TYPE)
     *
     * @return mixed Returns true on success or PEAR_Error on failure
     */
    public function guessContentType()
    {
        throw new NotImplementedError('HTTP_Download::guessContentType');
//        if (class_exists('MIME_Type') || @include_once 'MIME/Type.php') {
//            if (PEAR::isError($mime_type = MIME_Type::autoDetect($this->file))) {
//              return new Error($mime_type->getMessage());
//            }
//            return $this->setContentType($mime_type);
//        }
//        if (!function_exists('mime_content_type')) {
//            return PEAR::raiseError(
//                'This feature requires ext/mime_magic!',
//                HTTP_DOWNLOAD_E_NO_EXT_MMAGIC
//            );
//        }
//        if (!is_file(ini_get('mime_magic.magicfile'))) {
//            return PEAR::raiseError(
//                'ext/mime_magic is loaded but not properly configured!',
//                HTTP_DOWNLOAD_E_NO_EXT_MMAGIC
//            );
//        }
//        if (!$content_type = @mime_content_type($this->file)) {
//          return new Error('Couldn\'t guess content type with mime_content_type()');
//        }
//        return $this->setContentType($content_type);
    }

    /**
     * Send.
     *
     * Returns PEAR_Error if:
     *   o HTTP headers were already sent (HTTP_DOWNLOAD_E_HEADERS_SENT)
     *   o HTTP Range was invalid (HTTP_DOWNLOAD_E_INVALID_REQUEST)
     *
     * @return mixed Returns true on success or PEAR_Error on failure
     * @param  bool  $autoSetContentDisposition Whether to set the
     *                                          Content-Disposition header if it isn't already
     */
    public function send($autoSetContentDisposition = true)
    {
        if (headers_sent()) {
            return new Error('Headers already sent', true);
        }

        if (!ini_get('safe_mode')) {
            @set_time_limit(0);
        }

        if ($autoSetContentDisposition &&
            !isset($this->headers['Content-Disposition'])
        ) {
            $this->setContentDisposition();
        }

        if ($this->cache) {
            $this->headers['ETag'] = $this->generateETag();
            if ($this->isCached()) {
                $this->HTTP->sendStatusCode(304);
                $this->sendHeaders();

                return true;
            }
        } else {
            unset($this->headers['Last-Modified']);
        }

        // @note patched because system was taking output handler in consideration
        if (strtolower(ini_get('output_handler')) == 'ob_gzhandler') {
            while (ob_get_level() > 1) {
                ob_end_clean();
            }
        } else {
            while (ob_get_level()) {
                ob_end_clean();
            }

//            if (ob_get_level()) {
//                while (@ob_end_clean()) {
//                    ;
//                }
//            }
        }

        if ($this->gzip) {
            @ob_start('ob_gzhandler');
        } else {
            ob_start();
        }

        $this->sentBytes = 0;

        if ($this->isRangeRequest()) {
            $this->HTTP->sendStatusCode(206);
            $chunks = $this->getChunks();
        } else {
            $this->HTTP->sendStatusCode(200);
            $chunks = [[0, $this->size]];
            if (!$this->gzip && count(ob_list_handlers()) < 2) {
                $this->headers['Content-Length'] = $this->size;
            }
        }

        try {
            $this->sendChunks($chunks);
        } catch (Exception $e) {
            $this->HTTP->sendStatusCode(416);
            ob_end_clean();

            throw $e;
        }

        ob_end_flush();
        flush();

        return true;
    }

    /**
     * Set Content-Disposition header.
     *
     * @see HTTP_Download::HTTP_Download
     *
     * @param string $disposition whether to send the download
     *                            inline or as attachment
     * @param string $file_name   the filename to display in
     *                            the browser's download window
     *
     * <b>Example:</b>
     * <code>
     * $HTTP_Download->setContentDisposition(
     *   HTTP_DOWNLOAD_ATTACHMENT,
     *   'download.tgz'
     * );
     * </code>
     */
    public function setContentDisposition($disposition = HTTP_DOWNLOAD_ATTACHMENT,
        $file_name = null)
    {
        if ($file_name === null) {
            $file_name = basename($this->file);
        }

        $this->headers['Content-Disposition'] = "$disposition; filename=\"$file_name\"";
    }

    // }}}

    // {{{ protected methods

    /**
     * Generate ETag.
     *
     * @return string
     */
    public function generateETag()
    {
        if (!$this->etag) {
            if ($this->data) {
                $md5 = md5($this->data);
            } else {
                $fst = is_resource($this->handle) ?
                    fstat($this->handle) : stat($this->file);
                $md5 = md5($fst['mtime'] . '=' . $fst['ino'] . '=' . $fst['size']);
            }
            $this->etag = '"' . $md5 . '-' . crc32($md5) . '"';
        }

        return $this->etag;
    }

    /**
     * Check if entity is cached.
     *
     * @return bool
     */
    public function isCached()
    {
        return
            (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
                $this->lastModified == strtotime(current($a = explode(
                    ';', $_SERVER['HTTP_IF_MODIFIED_SINCE'])))) ||
            (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
                $this->compareAsterisk('HTTP_IF_NONE_MATCH', $this->etag))
        ;
    }

    /**
     * Compare against an asterisk or check for equality.
     *
     * @return bool
     * @param  string  key for the $_SERVER array
     * @param   string  string to compare
     */
    public function compareAsterisk($svar, $compare)
    {
        foreach (array_map('trim', explode(',', $_SERVER[$svar])) as $request) {
            if ($request === '*' || $request === $compare) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send HTTP headers.
     */
    public function sendHeaders()
    {
        foreach ($this->headers as $header => $value) {
            $this->HTTP->setHeader($header, $value);
        }

        $this->HTTP->sendHeaders();

        /* NSAPI won't output anything if we did this */
        if (strncasecmp(PHP_SAPI, 'nsapi', 5)) {
            ob_flush();
            flush();
        }
    }

    /**
     * Check if range is requested.
     *
     * @return bool
     */
    public function isRangeRequest()
    {
        if (!isset($_SERVER['HTTP_RANGE'])) {
            return false;
        }

        return $this->isValidRange();
    }

    /**
     * Check if entity hasn't changed.
     *
     * @return bool
     */
    public function isValidRange()
    {
        if (isset($_SERVER['HTTP_IF_MATCH']) &&
            !$this->compareAsterisk('HTTP_IF_MATCH', $this->etag)
        ) {
            return false;
        }
        if (isset($_SERVER['HTTP_IF_RANGE']) &&
            $_SERVER['HTTP_IF_RANGE'] !== $this->etag &&
            strtotime($_SERVER['HTTP_IF_RANGE']) !== $this->lastModified
        ) {
            return false;
        }
        if (isset($_SERVER['HTTP_IF_UNMODIFIED_SINCE'])) {
            $lm = current($a = explode(';', $_SERVER['HTTP_IF_UNMODIFIED_SINCE']));
            if (strtotime($lm) !== $this->lastModified) {
                return false;
            }
        }
        if (isset($_SERVER['HTTP_UNLESS_MODIFIED_SINCE'])) {
            $lm = current($a = explode(';', $_SERVER['HTTP_UNLESS_MODIFIED_SINCE']));
            if (strtotime($lm) !== $this->lastModified) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get chunks to send.
     *
     * @return array
     */
    public function getChunks()
    {
        $parts = [];
        foreach (explode(',', $this->getRanges()) as $chunk) {
            [$o, $e] = explode('-', $chunk);
            if ($e >= $this->size || (empty($e) && $e !== 0 && $e !== '0')) {
                $e = $this->size - 1;
            }
            if (empty($o) && $o !== 0 && $o !== '0') {
                $o = $this->size - $e;
                $e = $this->size - 1;
            }
            $parts[] = [$o, $e];
        }

        return $parts;
    }

    /**
     * Get range request.
     *
     * @return array
     */
    public function getRanges()
    {
        return preg_match('/^bytes=((\d*-\d*,? ?)+)$/',
            @$_SERVER['HTTP_RANGE'], $matches) ? $matches[1] : [];
    }

    /**
     * Send multiple chunks.
     *
     * @return mixed Returns true on success or PEAR_Error on failure
     * @param  array $chunks
     */
    public function sendChunks($chunks)
    {
        if (count($chunks) == 1) {
            return $this->sendChunk(current($chunks));
        }

        $bound = uniqid('HTTP_DOWNLOAD-', true);
        $cType = $this->headers['Content-Type'];
        $this->headers['Content-Type'] =
            'multipart/byteranges; boundary=' . $bound;
        $this->sendHeaders();
        foreach ($chunks as $chunk) {
            $this->sendChunk($chunk, $cType, $bound);
        }

        //echo "\r\n--$bound--\r\n";

        return true;
    }

    /**
     * Send chunk of data.
     *
     * @return mixed  Returns true on success or PEAR_Error on failure
     * @param  array  $chunk start and end offset of the chunk to send
     * @param  string $cType actual content type
     * @param  string $bound boundary for multipart/byteranges
     */
    public function sendChunk($chunk, $cType = null, $bound = null)
    {
        [$offset, $lastbyte] = $chunk;
        $length = ($lastbyte - $offset) + 1;

        if ($length < 1) {
            return new Error("Error processing range request: $offset-$lastbyte/$length");
        }

        $range = $offset . '-' . $lastbyte . '/' . $this->size;

        if (isset($cType, $bound)) {
            echo "\r\n--$bound\r\n",
            "Content-Type: $cType\r\n",
            "Content-Range: bytes $range\r\n\r\n";
        } else {
            if ($this->isRangeRequest()) {
                $this->headers['Content-Length'] = $length;
                $this->headers['Content-Range'] = 'bytes ' . $range;
            }
            $this->sendHeaders();
        }

        if ($this->data) {
            while (($length -= $this->bufferSize) > 0) {
                $this->flush(substr($this->data, $offset, $this->bufferSize));
                $this->throttleDelay and $this->sleep();
                $offset += $this->bufferSize;
            }
            if ($length) {
                $this->flush(substr($this->data, $offset, $this->bufferSize + $length));
            }
        } else {
            if (!is_resource($this->handle)) {
                $this->handle = fopen($this->file, 'rb');
            }
            fseek($this->handle, $offset);
            while (($length -= $this->bufferSize) > 0) {
                $this->flush(fread($this->handle, $this->bufferSize));
                $this->throttleDelay and $this->sleep();
            }
            if ($length) {
                $this->flush(fread($this->handle, $this->bufferSize + $length));
            }
        }

        return true;
    }

    /**
     * Flush.
     *
     * @param string $data
     */
    public function flush($data = '')
    {
        if ($dlen = strlen($data)) {
            $this->sentBytes += $dlen;
            echo $data;
        }
        @ob_flush();
        flush();
    }

    /**
     * Sleep.
     */
    public function sleep()
    {
        if (OS_WINDOWS) {
            com_message_pump($this->throttleDelay);
        } else {
            usleep($this->throttleDelay * 1000);
        }
    }

    // }}}
}
