<?php
/**
 * Cache Handler
 * php version 7
 *
 * @category  CacheHandler
 * @package   CacheHandler
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Cache-Handler
 * @since     Class available since Release 1.0.0
 */
namespace CacheHandler;

/**
 * Autoload
 * php version 7
 *
 * @category  CacheHandler
 * @package   CacheHandler
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Cache-Handler
 * @since     Class available since Release 1.0.0
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
    private $_cacheLocation = '/var/www/resources';

    /**
     * Initialize check and serve file
     *
     * @param string $relativeFilePath File path in cache Folder with leading slash
     *
     * @return void
     */
    public function init($relativeFilePath): void
    {
        $filePath = '/' . trim(
            string: str_replace(
                search: '../',
                replace: '',
                subject: urldecode(string: $relativeFilePath)
            ),
            characters: './'
        );
        $fileLocation = $this->_cacheLocation . $filePath;

        if (!file_exists(filename: $fileLocation)) {
            header(header: 'HTTP/1.0 404 Not Found');
            die();
        }

        $modifiedTime = filemtime(filename: $fileLocation);

        // Let Etag be last modified timestamp of file.
        $eTag = md5(string: "{$fileLocation}-{$modifiedTime}");

        if ((isset($_SERVER['HTTP_IF_NONE_MATCH'])
            && strpos(
                haystack: $_SERVER['HTTP_IF_NONE_MATCH'],
                needle: $eTag
            ) !== false)
            || (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
            && @strtotime(
                datetime: $_SERVER['HTTP_IF_MODIFIED_SINCE']
            ) == $modifiedTime)
        ) {
            header(header: 'HTTP/1.1 304 Not Modified');
            exit;
        }
        $this->_serveFile(
            fileLocation: $fileLocation,
            modifiedTime: $modifiedTime,
            eTag: $eTag
        );
    }

    /**
     * Serve File content
     *
     * @param string  $fileLocation File location
     * @param integer $modifiedTime Modified time
     * @param string  $eTag         E-Tag
     *
     * @return never
     */
    private function _serveFile($fileLocation, $modifiedTime, $eTag): never
    {
        // Get the $fileLocation file mime
        $fileInfo = finfo_open(flags: FILEINFO_MIME_TYPE);
        $mime = finfo_file(finfo: $fileInfo, filename: $fileLocation);
        finfo_close(finfo: $fileInfo);

        // Headers
        $gmDate = gmdate(
            format: "D, d M Y H:i:s",
            timestamp: $modifiedTime
        );
        header(header: 'Cache-Control: max-age=0, must-revalidate');
        header(header: "Last-Modified: {$gmDate} GMT");
        header(header: "Etag:'{$eTag}'");
        header(header: 'Expires: -1');
        header(header: "Content-Type: {$mime}");
        header(header: 'Content-Length: ' . filesize(filename: $fileLocation));

        // Send file content as stream
        $fp = fopen(filename: $fileLocation, mode: 'rb');
        fpassthru(stream: $fp);
        exit;
    }
}
