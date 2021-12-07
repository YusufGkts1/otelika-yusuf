<?php

namespace tests\Polling\data;

class CitizenProvider {

	public function males_3_females_2_with_birth_date_2000() {
		return [[
			'id' => '11111111111',
			'kimlik_no' => '11111111111',
			'ad' => 'firstname 1',
			'soyad' => 'lastname 1',
			'cinsiyet' => '1',
			'telefon' => '11111',
			'dogum_tarih' => '2000-01-01'
		], [
			'id' => '22222222222',
			'kimlik_no' => '22222222222',
			'ad' => 'firstname 2',
			'soyad' => 'lastname 2',
			'cinsiyet' => '2',
			'telefon' => '22222',
			'dogum_tarih' => '2000-01-01'
		], [
			'id' => '33333333333',
			'kimlik_no' => '33333333333',
			'ad' => 'firstname 3',
			'soyad' => 'lastname 3',
			'cinsiyet' => '1',
			'telefon' => '33333',
			'dogum_tarih' => '2000-01-01'
		], [
			'id' => '44444444444',
			'kimlik_no' => '44444444444',
			'ad' => 'firstname 4',
			'soyad' => 'lastname 4',
			'cinsiyet' => '2',
			'telefon' => '44444',
			'dogum_tarih' => '2000-01-01'
		], [
			'id' => '55555555555',
			'kimlik_no' => '55555555555',
			'ad' => 'firstname 5',
			'soyad' => 'lastname 5',
			'cinsiyet' => '1',
			'telefon' => '55555',
			'dogum_tarih' => '2000-01-01'
		]];
	}

	public function citizens_with_null_info() {
		return [
			[
				'id' => '11111111111',
				'kimlik_no' => '11111111111'
			],
			[
				'id' => '22222222222',
				'kimlik_no' => '22222222222'
			],
			[
				'id' => '33333333333',
				'kimlik_no' => '33333333333'
			]
		];
	}

	public function male_deceased_at_1970_female_born_at_1985() {
		return [[
			'id' => '11111111111',
			'kimlik_no' => '11111111111',
			'ad' => 'decased',
			'soyad' => 'male',
			'cinsiyet' => '1',
			'telefon' => '11111',
			'dogum_tarih' => '1930-01-01',
			'olum_tarih' => '1970-01-01'
		], [
			'id' => '22222222222',
			'kimlik_no' => '22222222222',
			'ad' => 'living',
			'soyad' => 'female',
			'cinsiyet' => '2',
			'telefon' => '22222',
			'dogum_tarih' => '1985-01-01'
		]];
	}
}

?>