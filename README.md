# Cache Handler

Issue "HTTP/1.1 304 Not Modified" response for cached files


## Usage

```PHP
require_once __DIR__ . '/Autoload.php';

use CacheHandler\CacheHandler;

$filePath = $_GET['file'];

$cacheHandler = new CacheHandler();
$cacheHandler->init($filePath);
```
