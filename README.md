# Cache Handler
 
Issue "HTTP/1.1 304 Not Modified" response for cached files
 

## Usage
 

    include_once ('CacheHandler.php');
    
    $filePath = $_GET['file'];
    
    $cacheHandler = new CacheHandler();
    $cacheHandler->init($filePath);