<?php
class Graphite {
	/**
	 * @return array
	 */
	public static function getProjects() {
		$json = WebCache::get(
			'wmcloud-projects',
			'https://tools.wmflabs.org/openstack-browser/api/projects.json'
		);
		$data = json_decode($json);
		if (!isset($data->projects)) {
			return array();
		}
		sort($data->projects);
		return $data->projects;
	}

	/**
	 * @param string $project
	 * @return array
	 */
	public static function getHostsForProject($project) {
		$txt = WebCache::get(
			'wmcloud-hosts-' . WebCache::escapeKeySegment($project),
			'https://tools.wmflabs.org/openstack-browser/api/dsh/project/'
			. rawurlencode($project)
		);
		if (!is_string($txt)) {
			return array();
		}
		$list = [];
		foreach (explode("\n", $txt) as $line) {
			$fqdn = trim($line);
			if ($fqdn !== '') {
				$chunks = explode('.', $fqdn);
				// <hostname>.<novaproject>.<region>.<domain>
				// e.g. "webperf11.beta.eqiad.wmflabs"
				$hostname = implode('.', array_slice($chunks, 0, -3));
				$list[] = $hostname;
			}
		}
		sort($list);
		return $list;
	}
}
