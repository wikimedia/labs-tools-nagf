<?php
class Nagf {
	/**
	 * @var NagfView
	 */
	private $view;

	public function __construct() {
		$viewData = new stdClass();
		$viewData->title = 'Nagf - wmflabs';
		$viewData->status = null;
		$viewData->project = null;
		$viewData->hosts = null;
		$viewData->hostGraphsConfig = $this->getHostGraphsConfig();

		if (isset($_GET['project'])) {
			$project = $_GET['project'];
			$hosts = Graphite::getHostsForProject($project);
			if ($hosts) {
				$viewData->project = $project;
				$viewData->hosts = $hosts;
			} else {
				$viewData->title = 'Project not found - Nagf';
				$viewData->status = array(404, 'Project not found');
			}
		}

		// NB: Keywords must be compatible with Graphites "from" param (See NagfView::getProjectPage)
		$ranges = array('day', 'week', 'month', 'year');
		// Filter out invalid ranges and ensure we have at least one of them selected
		$cookieRange = isset($_COOKIE['nagf-range']) ? explode('!', $_COOKIE['nagf-range']) : array();
		$checked = array_intersect($ranges, $cookieRange) ?: array( 'day' );

		$viewData->ranges = array();
		foreach ($ranges as $range) {
			$viewData->ranges[$range] = in_array($range, $checked);
		}

		$this->view = new NagfView($viewData);
	}

	public function getView() {
		return $this->view;
	}

	protected function getHostGraphsConfig() {
		return array(
			'cpu' => array(
				'title' => 'CPU',
				'targets' => array(
					'alias(color(stacked(HOST.cpu.total.user),"#3333bb"),"User")',
					'alias(color(stacked(HOST.cpu.total.nice),"#ffea00"),"Nice")',
					'alias(color(stacked(HOST.cpu.total.system),"#dd0000"),"System")',
					'alias(color(stacked(HOST.cpu.total.iowait),"#ff8a60"),"Wait I/O")',
					'alias(alpha(color(stacked(HOST.cpu.total.idle),"#e2e2f2"),0.4),"Idle")',
				),
				'render' => array(
					'yMax' => 100,
				),
				'overview' => 'sum',
				'overview-render' => array(),
			),
			'load' => array(
				'title' => 'Load Average',
				'targets' => array(
					'alias(color(HOST.loadavg.01,"#bbbbbb"),"1-min")',
					'alias(color(HOST.loadavg.05,"#ffea00"),"5-min")',
					'alias(color(HOST.loadavg.15,"#33cc33"),"15-min")',
				),
				'overview' => array(
					'alias(color(sum(HOST.loadavg.01),"#bbbbbb"),"1-min")',
					'alias(color(sum(HOST.loadavg.05),"#ffea00"),"5-min")',
					'alias(color(sum(HOST.loadavg.15),"#33cc33"),"15-min")',
					'alias(color(sum(offset(scale(HOST.loadavg.01,0),1)),"green"),"Nodes")',
				),
			),
			'process' => array(
				'title' => 'Processes',
				'targets' => array(
					'alias(HOST.loadavg.processes_running,"Procs running")',
					'alias(secondYAxis(HOST.loadavg.processes_total),"Procs total")',
				),
				'overview' => array(
					'alias(sum(HOST.loadavg.processes_running),"Procs running")',
					'alias(secondYAxis(sum(HOST.loadavg.processes_total)),"Procs total")',
				),
			),
			'memory' => array(
				'title' => 'Memory',
				'targets' => array(
					'alias(color(stacked('
						. 'diffSeries(HOST.memory.MemTotal,HOST.memory.{MemFree,Buffers,Cached})'
						. '),"#5555cc"),"Used")',
					'alias(color(stacked(HOST.memory.Cached),"#33cc33"),"Cached")',
					'alias(color(stacked(HOST.memory.Buffers),"#99ff33"),"Buffers")',
					'alias(alpha(color(stacked(HOST.memory.MemFree),"#f0ffc0"),0.4),"Free")',
					'alias(color(stacked(HOST.memory.SwapCached),"#9900CC"),"Swap")',
					'alias(color(HOST.memory.MemTotal,"red"),"Total")',
				),
				'overview' => array(
					'alias(color(stacked('
						. 'diffSeries(sum(HOST.memory.MemTotal),sum(HOST.memory.{MemFree,Buffers,Cached}))'
						. '),"#5555cc"),"Used")',
					'alias(color(stacked(sum(HOST.memory.Cached)),"#33cc33"),"Cached")',
					'alias(color(stacked(sum(HOST.memory.Buffers)),"#99ff33"),"Buffers")',
					'alias(alpha(color(stacked(sum(HOST.memory.MemFree)),"#f0ffc0"),0.4),"Free")',
					'alias(color(stacked(sum(HOST.memory.SwapCached)),"#9900CC"),"Swap")',
					'alias(color(sum(HOST.memory.MemTotal),"red"),"Total")',
				),
			),
			'disk-bytes' => array(
				'title' => 'Disk space free (in bytes)',
				'targets' => array(
					'aliasByNode(maximumAbove(HOST.diskspace.*.byte_avail,0),-3,-2)',
				),
				'overview' => false,
				'render' => array(
					'yMin' => 0,
				),
			),
			'disk-inodes' => array(
				'title' => 'Disk space free (% of inodes)',
				'targets' => array(
					'aliasByNode(maximumAbove(HOST.diskspace.*.inodes_percentfree,0),-3,-2)',
				),
				'overview' => array(
					'aliasByNode(maximumAbove(HOST.diskspace.*.inodes_percentfree,0),1,-2)',
				),
				'render' => array(
					'yMin' => 0,
				),
			),
			'network-bytes' => array(
				'title' => 'Network bytes',
				'targets' => array(
					'alias(HOST.network.eth0.rx_byte,"Bytes received")',
					'alias(HOST.network.eth0.tx_byte,"Bytes sent")',
				),
				'overview' => 'sum',
			),
			'network-packets' => array(
				'title' => 'Network packets',
				'targets' => array(
					'alias(HOST.network.eth0.rx_packets,"Packets received")',
					'alias(HOST.network.eth0.tx_packets,"Packets sent")',
				),
				'overview' => 'sum',
			),
			'puppetagent' => array(
				'title' => 'Puppet agent',
				'targets' => array(
					'aliasByNode(HOST.puppetagent.failed_events,-2)',
				),
				'overview' => 'stacked',
			),
		);
	}
}
