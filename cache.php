<?php
/**
 * Caching service, with pass phrase to avoid abuse.
 *
 * Take two parameters:
 *      url: url encoded URL to retrieve
 *      key: 'public key' for this URL.
 *
 * The key's are generated in secret for individual URLs. This stops nasty people
 * from arbitrarily using this to cache/proxy any URL.
 *
 * 1) Generate the key for your URL:
 *
 *    From the command line:
 *
 *     *
 *    Output will look like:
 *
 *      Kvrgkag%2BOUthN3w45d1RLZom6dqW%2B6ojZI5Y5Yj8WQo%3D
 *
 * 2) URL Encode your URL, e.g. with http://meyerweb.com/eric/tools/dencoder/
 *
 * So http://yoururl.com will come out like: http%3A%2F%2Fyoururl.com
 *
 * 3) Generate a key for this URL, by passing the encoded URL in to the command line generate script:
 *
 *    php generate.php http://yoururl.com
 *
 *
 * Construct your cached URL.
 *
 * URL encode the URL, e.g. with http://meyerweb.com/eric/tools/dencoder/, so
 *
 *      http://yoururl.com
 *
 * would become
 *
 *      http%3A%2F%2Fyoururl.com
 *
 * Then construct the cache request URL like
 *
 *        http://someserver.com/phpcache/cache.php?url=http%3A%2F%2Fyoururl.com&key=Kvrgkag%2BOUthN3w45d1RLZom6dqW%2B6ojZI5Y5Yj8WQo%3D
 *
 * ERROR CODES:
 *      If everything goes well you'll get your content and a 200 error code.
 *
 *      Headers from the originator will all be lost!!
 *
 *      If the key stuff gets screwed up somewhere, you'll get a 403 Permission Denied
 *
 */

require_once('SimpleCache.php');
require_once('cipher.php');

$url = urldecode($_GET['url']);

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    header("HTTP/1.1 500 Internal Server Error");
    exit(0);
}

$cipher = new Cipher(file_get_contents('secret.txt'));

$encryptedtext = $cipher->encrypt(md5($_GET['url']));

if ($encryptedtext != $_GET['key']) {
    header('HTTP/1.0 403 Forbidden');
    echo $_GET['url'];
    exit(0);
}

$cache = new Gilbitron\Util\SimpleCache();
$cache->cache_path = 'cache/';
$cache->cache_time = 60;

if(!($data = $cache->get_cache($url))){
    $data = $cache->do_curl($url);
    $cache->set_cache($url, $data);
}

echo $data;

?>