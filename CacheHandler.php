<?php
/**
 * Class to reduce cache hits and save on bandwidth using ETags and cache headers.
 *
 * HTTP etags header helps reduce the cache hits
 * Helps browser avoid unwanted hits to un-modified content on the server
 * which are cached on client browser.
 * The headers in class helps fetch only the modified content.
 * 
 * @category   PHP E-Tags
 * @package    Cache handler
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class CacheHandler
{
    /**
     * Cache Folder
     * 
     * The folder location outside docroot
     * without a slash at the end
     * 
     * @var string
     */
    private $cacheLocation = '/var/www/resources';

    /**
     * Initalise check and serve file
     *
     * @param string $relativeFilePath File path in cache Folder with leading slash(/)
     * @return void
     */
    public function init($relativeFilePath)
    {
        $filePath = '/' . trim(str_replace('../','',urldecode($relativeFilePath)), './');
        $fileLocation = $this->$cacheLocation . $filePath;

        if (!file_exists($fileLocation)) {
            header('HTTP/1.0 404 Not Found');
            die();
        }

        $modifiedTime = filemtime($fileLocation);

        // Let Etag be last modified timestamp of file.
        $eTag = "{$modifiedTime}";

        if (
            (
                isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
                strpos($_SERVER['HTTP_IF_NONE_MATCH'], $eTag) !== false
            ) ||
            (
                isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
                @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $modifiedTime
            )
        ) { 
            header('HTTP/1.1 304 Not Modified'); 
            exit;
        }
        $this->serveFile($fileLocation, $modifiedTime, $eTag);
    }

    /**
     * Serve File content
     *
     * @param string  $fileLocation
     * @param integer $modifiedTime
     * @param string  $eTag
     * @return void
     */
    private function serveFile($fileLocation, $modifiedTime, $eTag)
    {
        // Get the $fileLocation file mime
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($fileInfo, $fileLocation);
        finfo_close($fileInfo);

        // Headers
        header('Cache-Control: max-age=0, must-revalidate');
        header("Last-Modified: ".gmdate("D, d M Y H:i:s", $modifiedTime)." GMT"); 
        header("Etag:'{$eTag}'");
        header('Expires: -1');
        header("Content-Type: {$mime}");
        header('Content-Length: ' . filesize($fileLocation));

        // Send file content as stream
        $fp = fopen($fileLocation, 'rb');
        fpassthru($fp);
        exit;
    }
}
