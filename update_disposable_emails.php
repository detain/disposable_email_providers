#!/usr/bin/php
<?php
	$exts = array('line' => 'txt', 'json' => 'json', 'discard.email' => 'json');
	$lists = array(
		'line' => array(
			'https://raw.githubusercontent.com/andreis/disposable/master/domains.txt',
			'https://raw.githubusercontent.com/martenson/disposable-email-domains/master/disposable_email_blacklist.conf',
			'https://gist.githubusercontent.com/adamloving/4401361/raw/db901ef28d20af8aa91bf5082f5197d27926dea4/temporary-email-address-domains',
			'https://gist.githubusercontent.com/michenriksen/8710649/raw/d42c080d62279b793f211f0caaffb22f1c980912',
			'https://raw.githubusercontent.com/hassanazimi/List-of-Disposable-Email-Addresses/master/emails',
			'https://raw.githubusercontent.com/wesbos/burner-email-providers/master/emails.txt',
		),
		'json' => array(
			'https://raw.githubusercontent.com/ivolo/disposable-email-domains/master/index.json',
		),
		'discard.email' => array(
			'https://discard.email/about-getDomains=55bea3fee498cb80f4d3060b738a5936.htm'
		)
	);
	$combined = array();
	$sightings = array();
	foreach ($lists as $list_type => $list_data) {
		foreach ($list_data as $url) {
			if (preg_match('/^https?:\/\/([^\/]*)\.githubusercontent.com\/([^\/]+)\/([^\/]+)\/.*$/m', $url, $matches))
				$filename = "github-{$matches[1]}-{$matches[2]}-{$matches[3]}";
			elseif (preg_match('/^https?:\/\/discard\.email\/([^=\/]+)[=\/]?.*$/m', $url, $matches))
				$filename = "discard.email-{$matches[1]}";
			else
				$filename = str_replace(array('https://','/'),array('','_'), $url);
			$filename = strtolower($filename);
			if ($list_type == 'line') {
				$domain_names = file($url, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			} elseif ($list_type == 'json') {
				$domain_names = json_decode(file_get_contents($url), true);
			} elseif ($list_type == 'discard.email') {
				$domains = json_decode(file_get_contents($url), true);
				$domain_names = array();
				foreach ($domains['active'] as $domain_data) {
					$domain_names[] = $domain_data['domain'];
				}
			}
			foreach ($domain_names as $domain)
				if (!isset($combined[$domain]))
					$combined[$domain] = 1;
				else
					$combined[$domain]++;
			//file_put_contents('disposable-' . $filename, implode("\n", $domain_names));
		}
	}
	foreach ($combined as $domain => $count)
		if (!isset($sightings[$count]))
			$sightings[$count] = array($domain);
		else
			$sightings[$count][] = $domain;
	unset($combined);
	foreach ($sightings as $count => $domains) {
		sort($domains);
		file_put_contents('disposable_email_providers_'.$count.'_sightings.txt', implode("\n", $domains));
		file_put_contents('disposable_email_providers_'.$count.'_sightings.json', json_encode($domains, JSON_PRETTY_PRINT));
	}
