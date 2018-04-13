<?php
class WebCache {

	/**
	 * @param string $segment
	 * @return string
	 */
	public static function escapeKeySegment($segment) {
		return preg_replace_callback('/[^a-zA-Z0-9_\-]/', function ($match) {
			return strtr(rawurlencode($match[0]), [ '%' => '_' ]);
		}, $segment);
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public static function validKey($key) {
		return preg_match('/^[a-zA-Z0-9_\-]+$/', $key) === 1;
	}

	/**
	 * Get string data from a url (or cache).
	 *
	 * @param string $key
	 * @param string $url
	 * @param int $expire How long this may be cached
	 * @return string
	 */
	public static function get($key, $url, $expire = 3600) {
		static $dir;
		if ($dir === null) {
			$dir = dirname(__DIR__) . '/cache';
		}

		if (!self::validKey($key)) {
			throw new Exception('Invalid key');
		}

		if (!is_writable($dir)) {
			throw new Exception('Unable to write to cache directory');
		}

		$cacheFile = "$dir/$key.cache";
		$hasCache = file_exists($cacheFile);

		if ($hasCache && filemtime($cacheFile) > (time() - $expire)) {
			// Cache file is new enough, use it.
			return file_get_contents($cacheFile);
		}

		// Fetch fresh copy from remote
		$context = stream_context_create(array(
			'http' => array(
				'user_agent' => 'WebCache.php (package: github.com/wikimedia/nagf)'
			)
		));
		$value = file_get_contents($url, false, $context);
		if ($value === false) {
			if ($hasCache) {
				// Keep using cache for now, remote failed
				return file_get_contents($cacheFile);
			}
			throw new Exception('Unable to fetch ' . $url);
		}

		$written = file_put_contents($cacheFile, $value, LOCK_EX);
		if ($written === false) {
			throw new Exception('Unable to write to cache file');
		}

		return $value;
	}
}
