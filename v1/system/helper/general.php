<?php
function format_isodate($date) {
	return date(DATE_ISO8601, strtotime($date));
}

function format_decimal($val, int $precision = 2): string {
	$input = str_replace(' ', '', $val);
	$number = str_replace(',', '.', $input);
	if (strpos($number, '.')) {
		$groups = explode('.', str_replace(',', '.', $number));
		$lastGroup = array_pop($groups);
		$number = implode('', $groups) . '.' . $lastGroup;
	}
	return bcadd($number, 0, $precision);
}

function html_decode($html, $iframe = false) {
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	// We decode twice because sometimes not clearing tags.
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = preg_replace('~[\r\n]+~', '', $html);

	if ($iframe) {
		preg_match('/ta-insert-video="[\s\S]*?"/', $html, $matches);
		if ($matches[0]) {
			// Remove class and last "
			$video_link = substr(str_replace('ta-insert-video="', '', $matches[0]), 0, -1);

			// Change image to iframe from string
			$html = preg_replace('#<img[^>]+class="[^"]*ta-insert-video[^"]*"[^>]*>#', '<iframe src="' . $video_link . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>', $html);
		}
	}

	return $html;
}

function e22($str) {
	echo PHP_EOL . PHP_EOL . PHP_EOL . $str . PHP_EOL . PHP_EOL;
}

function e21($str) {
	echo PHP_EOL . PHP_EOL . PHP_EOL . $str . PHP_EOL;
}

function e20($str) {
	echo PHP_EOL . PHP_EOL . PHP_EOL . $str;
}

function e12($str) {
	echo PHP_EOL . PHP_EOL . $str . PHP_EOL . PHP_EOL;
}

function e11($str) {
	echo PHP_EOL . PHP_EOL . $str . PHP_EOL;
}

function e10($str) {
	echo PHP_EOL . PHP_EOL . $str;
}

function e02($str) {
	echo PHP_EOL . $str . PHP_EOL . PHP_EOL;
}

function e01($str) {
	echo PHP_EOL . $str . PHP_EOL;
}

function e00($str) {
	echo PHP_EOL . $str;
}

function debug($str) {
	try {
		if(false == file_exists(DIR_LOGS))
				mkdir(DIR_LOGS, 755, true);
				
		$file_path = DIR_LOGS . 'debug.log';

		$file = fopen($file_path, 'a');
		
		fwrite($file, date('Y-m-d G:i:s') . ' - ' . print_r($str, true) . "\n");
	}
	catch(\Throwable $t) {
		// do not break if error occurs here
	}
}

function turkish_operator() {
	return array(
		'530',
		'531',
		'532',
		'533',
		'534',
		'535',
		'536',
		'537',
		'538',
		'539',
		'561',

		'540',
		'541',
		'542',
		'543',
		'544',
		'545',
		'546',
		'547',
		'548',
		'549',

		'501',
		'505',
		'506',
		'507',
		'551',
		'552',
		'553',
		'554',
		'555',
		'559',
	);
}

function format_phone($phone, $turkish = false) {
	$phone = str_replace('+', '', $phone);
	$phone = str_replace(' ', '', $phone);
	$phone = str_replace('-', '', $phone);
	$phone = str_replace('(', '', $phone);
	$phone = str_replace(')', '', $phone);

	if ($turkish) {
		$phone = remove_turkish_phone($phone);
	} else {
		$removed_phone = remove_turkish_phone($phone);
		$operator = substr($removed_phone, 0, 3);

		if (in_array($operator, turkish_operator())) {
			$phone = remove_turkish_phone($phone);

			$phone = '90' . $phone;
		}
	}

	return $phone;
}

function remove_turkish_phone($phone) {
	if (strpos($phone, '0') == '+') {
		$phone = ltrim($phone, '+');
	}
	if (strpos($phone, '0') == '9') {
		$phone = ltrim($phone, '9');
	}
	if (strpos($phone, '0') == '0') {
		$phone = ltrim($phone, '0');
	}

	return $phone;
}