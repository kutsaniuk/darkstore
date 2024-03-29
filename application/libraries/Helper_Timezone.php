<?php 

class Helper_Timezone
{
	 public static function getTimeZoneSelectArray($name='time_zone',$selectedZone = NULL)
    {
		$structure=array();
        $regions = array(
            'Africa' => DateTimeZone::AFRICA,
            'America' => DateTimeZone::AMERICA,
            'Antarctica' => DateTimeZone::ANTARCTICA,
            'Aisa' => DateTimeZone::ASIA,
            'Atlantic' => DateTimeZone::ATLANTIC,
            'Europe' => DateTimeZone::EUROPE,
            'Indian' => DateTimeZone::INDIAN,
            'Pacific' => DateTimeZone::PACIFIC
        );
		
		$list = array(
		 
		'Africa/Abidjan'=>'(+00:00 UTC) Abidjan',
'Africa/Accra'=>'(+00:00 UTC) Accra',
'Africa/Bamako'=>'(+00:00 UTC) Bamako',
'Africa/Banjul'=>'(+00:00 UTC) Banjul',
'Africa/Bissau'=>'(+00:00 UTC) Bissau',
'Africa/Conakry'=>'(+00:00 UTC) Conakry',
'Africa/Dakar'=>'(+00:00 UTC) Dakar',
'Africa/Freetown'=>'(+00:00 UTC) Freetown',
'Africa/Lome'=>'(+00:00 UTC) Lome',
'Africa/Monrovia'=>'(+00:00 UTC) Monrovia',
'Africa/Nouakchott'=>'(+00:00 UTC) Nouakchott',
'Africa/Ouagadougou'=>'(+00:00 UTC) Ouagadougou',
'Africa/Sao_Tome'=>'(+00:00 UTC) Sao Tome',
'Africa/Algiers'=>'(+01:00 UTC) Algiers',
'Africa/Bangui'=>'(+01:00 UTC) Bangui',
'Africa/Brazzaville'=>'(+01:00 UTC) Brazzaville',
'Africa/Casablanca'=>'(+01:00 UTC) Casablanca',
'Africa/Douala'=>'(+01:00 UTC) Douala',
'Africa/El_Aaiun'=>'(+01:00 UTC) El Aaiun',
'Africa/Kinshasa'=>'(+01:00 UTC) Kinshasa',
'Africa/Lagos'=>'(+01:00 UTC) Lagos',
'Africa/Libreville'=>'(+01:00 UTC) Libreville',
'Africa/Luanda'=>'(+01:00 UTC) Luanda',
'Africa/Malabo'=>'(+01:00 UTC) Malabo',
'Africa/Ndjamena'=>'(+01:00 UTC) Ndjamena',
'Africa/Niamey'=>'(+01:00 UTC) Niamey',
'Africa/Porto-Novo'=>'(+01:00 UTC) Porto-Novo',
'Africa/Tunis'=>'(+01:00 UTC) Tunis',
'Africa/Windhoek'=>'(+01:00 UTC) Windhoek',
'Africa/Blantyre'=>'(+02:00 UTC) Blantyre',
'Africa/Bujumbura'=>'(+02:00 UTC) Bujumbura',
'Africa/Cairo'=>'(+02:00 UTC) Cairo',
'Africa/Ceuta'=>'(+02:00 UTC) Ceuta',
'Africa/Gaborone'=>'(+02:00 UTC) Gaborone',
'Africa/Harare'=>'(+02:00 UTC) Harare',
'Africa/Johannesburg'=>'(+02:00 UTC) Johannesburg',
'Africa/Kigali'=>'(+02:00 UTC) Kigali',
'Africa/Lubumbashi'=>'(+02:00 UTC) Lubumbashi',
'Africa/Lusaka'=>'(+02:00 UTC) Lusaka',
'Africa/Maputo'=>'(+02:00 UTC) Maputo',
'Africa/Maseru'=>'(+02:00 UTC) Maseru',
'Africa/Mbabane'=>'(+02:00 UTC) Mbabane',
'Africa/Tripoli'=>'(+02:00 UTC) Tripoli',
'Africa/Addis_Ababa'=>'(+03:00 UTC) Addis Ababa',
'Africa/Asmara'=>'(+03:00 UTC) Asmara',
'Africa/Dar_es_Salaam'=>'(+03:00 UTC) Dar es Salaam',
'Africa/Djibouti'=>'(+03:00 UTC) Djibouti',
'Africa/Juba'=>'(+03:00 UTC) Juba',
'Africa/Kampala'=>'(+03:00 UTC) Kampala',
'Africa/Khartoum'=>'(+03:00 UTC) Khartoum',
'Africa/Mogadishu'=>'(+03:00 UTC) Mogadishu',
'Africa/Nairobi'=>'(+03:00 UTC) Nairobi',
 'America/Anchorage'=>'America',
'America/Anchorage'=>'(-08:00 UTC) Anchorage',
'America/Juneau'=>'(-08:00 UTC) Juneau',
'America/Metlakatla'=>'(-08:00 UTC) Metlakatla',
'America/Nome'=>'(-08:00 UTC) Nome',
'America/Sitka'=>'(-08:00 UTC) Sitka',
'America/Yakutat'=>'(-08:00 UTC) Yakutat',
'America/Creston'=>'(-07:00 UTC) Creston',
'America/Dawson'=>'(-07:00 UTC) Dawson',
'America/Dawson_Creek'=>'(-07:00 UTC) Dawson Creek',
'America/Hermosillo'=>'(-07:00 UTC) Hermosillo',
'America/Los_Angeles'=>'(-07:00 UTC) Los Angeles',
'America/Phoenix'=>'(-07:00 UTC) Phoenix',
'America/Santa_Isabel'=>'(-07:00 UTC) Santa Isabel',
'America/Tijuana'=>'(-07:00 UTC) Tijuana',
'America/Vancouver'=>'(-07:00 UTC) Vancouver',
'America/Whitehorse'=>'(-07:00 UTC) Whitehorse',
'America/Belize'=>'(-06:00 UTC) Belize',
'America/Boise'=>'(-06:00 UTC) Boise',
'America/Cambridge_Bay'=>'(-06:00 UTC) Cambridge Bay',
'America/Chihuahua'=>'(-06:00 UTC) Chihuahua',
'America/Costa_Rica'=>'(-06:00 UTC) Costa Rica',
'America/Denver'=>'(-06:00 UTC) Denver',
'America/Edmonton'=>'(-06:00 UTC) Edmonton',
'America/El_Salvador'=>'(-06:00 UTC) El Salvador',
'America/Guatemala'=>'(-06:00 UTC) Guatemala',
'America/Inuvik'=>'(-06:00 UTC) Inuvik',
'America/Managua'=>'(-06:00 UTC) Managua',
'America/Mazatlan'=>'(-06:00 UTC) Mazatlan',
'America/Ojinaga'=>'(-06:00 UTC) Ojinaga',
'America/Regina'=>'(-06:00 UTC) Regina',
'America/Swift_Current'=>'(-06:00 UTC) Swift Current',
'America/Tegucigalpa'=>'(-06:00 UTC) Tegucigalpa',
'America/Yellowknife'=>'(-06:00 UTC) Yellowknife',
'America/Atikokan'=>'(-05:00 UTC) Atikokan',
'America/Bahia_Banderas'=>'(-05:00 UTC) Bahia Banderas',
'America/Bogota'=>'(-05:00 UTC) Bogota',
'America/Cancun'=>'(-05:00 UTC) Cancun',
'America/Cayman'=>'(-05:00 UTC) Cayman',
'America/Chicago'=>'(-05:00 UTC) Chicago',
'America/Eirunepe'=>'(-05:00 UTC) Eirunepe',
'America/Guayaquil'=>'(-05:00 UTC) Guayaquil',
'America/Indiana/Knox'=>'(-05:00 UTC) Indiana/Knox',
'America/Indiana/Tell_City'=>'(-05:00 UTC) Indiana/Tell City',
'America/Jamaica'=>'(-05:00 UTC) Jamaica',
'America/Lima'=>'(-05:00 UTC) Lima',
'America/Matamoros'=>'(-05:00 UTC) Matamoros',
'America/Menominee'=>'(-05:00 UTC) Menominee',
'America/Merida'=>'(-05:00 UTC) Merida',
'America/Mexico_City'=>'(-05:00 UTC) Mexico City',
'America/Monterrey'=>'(-05:00 UTC) Monterrey',
'America/North_Dakota/Beulah'=>'(-05:00 UTC) North Dakota/Beulah',
'America/North_Dakota/Center'=>'(-05:00 UTC) North Dakota/Center',
'America/North_Dakota/New_Salem'=>'(-05:00 UTC) North Dakota/New Salem',
'America/Panama'=>'(-05:00 UTC) Panama',
'America/Rainy_River'=>'(-05:00 UTC) Rainy River',
'America/Rankin_Inlet'=>'(-05:00 UTC) Rankin Inlet',
'America/Resolute'=>'(-05:00 UTC) Resolute',
'America/Rio_Branco'=>'(-05:00 UTC) Rio Branco',
'America/Winnipeg'=>'(-05:00 UTC) Winnipeg',
'America/Caracas'=>'(-04:30 UTC) Caracas',
'America/Anguilla'=>'(-04:00 UTC) Anguilla',
'America/Antigua'=>'(-04:00 UTC) Antigua',
'America/Aruba'=>'(-04:00 UTC) Aruba',
'America/Asuncion'=>'(-04:00 UTC) Asuncion',
'America/Barbados'=>'(-04:00 UTC) Barbados',
'America/Blanc-Sablon'=>'(-04:00 UTC) Blanc-Sablon',
'America/Boa_Vista'=>'(-04:00 UTC) Boa Vista',
'America/Campo_Grande'=>'(-04:00 UTC) Campo Grande',
'America/Cuiaba'=>'(-04:00 UTC) Cuiaba',
'America/Curacao'=>'(-04:00 UTC) Curacao',
'America/Detroit'=>'(-04:00 UTC) Detroit',
'America/Dominica'=>'(-04:00 UTC) Dominica',
'America/Grand_Turk'=>'(-04:00 UTC) Grand Turk',
'America/Grenada'=>'(-04:00 UTC) Grenada',
'America/Guadeloupe'=>'(-04:00 UTC) Guadeloupe',
'America/Guyana'=>'(-04:00 UTC) Guyana',
'America/Havana'=>'(-04:00 UTC) Havana',
'America/Indiana/Indianapolis'=>'(-04:00 UTC) Indiana/Indianapolis',
'America/Indiana/Marengo'=>'(-04:00 UTC) Indiana/Marengo',
'America/Indiana/Petersburg'=>'(-04:00 UTC) Indiana/Petersburg',
'America/Indiana/Vevay'=>'(-04:00 UTC) Indiana/Vevay',
'America/Indiana/Vincennes'=>'(-04:00 UTC) Indiana/Vincennes',
'America/Indiana/Winamac'=>'(-04:00 UTC) Indiana/Winamac',
'America/Iqaluit'=>'(-04:00 UTC) Iqaluit',
'America/Kentucky/Louisville'=>'(-04:00 UTC) Kentucky/Louisville',
'America/Kentucky/Monticello'=>'(-04:00 UTC) Kentucky/Monticello',
'America/Kralendijk'=>'(-04:00 UTC) Kralendijk',
'America/La_Paz'=>'(-04:00 UTC) La Paz',
'America/Lower_Princes'=>'(-04:00 UTC) Lower Princes',
'America/Manaus'=>'(-04:00 UTC) Manaus',
'America/Marigot'=>'(-04:00 UTC) Marigot',
'America/Martinique'=>'(-04:00 UTC) Martinique',
'America/Montserrat'=>'(-04:00 UTC) Montserrat',
'America/Nassau'=>'(-04:00 UTC) Nassau',
'America/New_York'=>'(-04:00 UTC) New York',
'America/Nipigon'=>'(-04:00 UTC) Nipigon',
'America/Pangnirtung'=>'(-04:00 UTC) Pangnirtung',
'America/Port-au-Prince'=>'(-04:00 UTC) Port-au-Prince',
'America/Port_of_Spain'=>'(-04:00 UTC) Port of Spain',
'America/Porto_Velho'=>'(-04:00 UTC) Porto Velho',
'America/Puerto_Rico'=>'(-04:00 UTC) Puerto Rico',
'America/Santo_Domingo'=>'(-04:00 UTC) Santo Domingo',
'America/St_Barthelemy'=>'(-04:00 UTC) St Barthelemy',
'America/St_Kitts'=>'(-04:00 UTC) St Kitts',
'America/St_Lucia'=>'(-04:00 UTC) St Lucia',
'America/St_Thomas'=>'(-04:00 UTC) St Thomas',
'America/St_Vincent'=>'(-04:00 UTC) St Vincent',
'America/Thunder_Bay'=>'(-04:00 UTC) Thunder Bay',
'America/Toronto'=>'(-04:00 UTC) Toronto',
'America/Tortola'=>'(-04:00 UTC) Tortola',
'America/Araguaina'=>'(-03:00 UTC) Araguaina',
'America/Argentina/Buenos_Aires'=>'(-03:00 UTC) Argentina/Buenos Aires',
'America/Argentina/Catamarca'=>'(-03:00 UTC) Argentina/Catamarca',
'America/Argentina/Cordoba'=>'(-03:00 UTC) Argentina/Cordoba',
'America/Argentina/Jujuy'=>'(-03:00 UTC) Argentina/Jujuy',
'America/Argentina/La_Rioja'=>'(-03:00 UTC) Argentina/La Rioja',
'America/Argentina/Mendoza'=>'(-03:00 UTC) Argentina/Mendoza',
'America/Argentina/Rio_Gallegos'=>'(-03:00 UTC) Argentina/Rio Gallegos',
'America/Argentina/Salta'=>'(-03:00 UTC) Argentina/Salta',
'America/Argentina/San_Juan'=>'(-03:00 UTC) Argentina/San Juan',
'America/Argentina/San_Luis'=>'(-03:00 UTC) Argentina/San Luis',
'America/Argentina/Tucuman'=>'(-03:00 UTC) Argentina/Tucuman',
'America/Argentina/Ushuaia'=>'(-03:00 UTC) Argentina/Ushuaia',
'America/Bahia'=>'(-03:00 UTC) Bahia',
'America/Belem'=>'(-03:00 UTC) Belem',
'America/Cayenne'=>'(-03:00 UTC) Cayenne',
'America/Fortaleza'=>'(-03:00 UTC) Fortaleza',
'America/Glace_Bay'=>'(-03:00 UTC) Glace Bay',
'America/Goose_Bay'=>'(-03:00 UTC) Goose Bay',
'America/Halifax'=>'(-03:00 UTC) Halifax',
'America/Maceio'=>'(-03:00 UTC) Maceio',
'America/Moncton'=>'(-03:00 UTC) Moncton',
'America/Montevideo'=>'(-03:00 UTC) Montevideo',
'America/Paramaribo'=>'(-03:00 UTC) Paramaribo',
'America/Recife'=>'(-03:00 UTC) Recife',
'America/Santarem'=>'(-03:00 UTC) Santarem',
'America/Santiago'=>'(-03:00 UTC) Santiago',
'America/Sao_Paulo'=>'(-03:00 UTC) Sao Paulo',
'America/Thule'=>'(-03:00 UTC) Thule',
'America/Godthab'=>'(-02:00 UTC) Godthab',
'America/Miquelon'=>'(-02:00 UTC) Miquelon',
'America/Noronha'=>'(-02:00 UTC) Noronha',
'America/St_Johns'=>'(-02:30 UTC) St Johns',
'America/Danmarkshavn'=>'(+00:00 UTC) Danmarkshavn',
'America/Scoresbysund'=>'(+00:00 UTC) Scoresbysund',
  'Asia/Amman'=>'Asia',
'Asia/Amman'=>'(+03:00 UTC) Amman',
'Asia/Baghdad'=>'(+03:00 UTC) Baghdad',
'Asia/Bahrain'=>'(+03:00 UTC) Bahrain',
'Asia/Beirut'=>'(+03:00 UTC) Beirut',
'Asia/Damascus'=>'(+03:00 UTC) Damascus',
'Asia/Gaza'=>'(+03:00 UTC) Gaza',
'Asia/Hebron'=>'(+03:00 UTC) Hebron',
'Asia/Jerusalem'=>'(+03:00 UTC) Jerusalem',
'Asia/Kuwait'=>'(+03:00 UTC) Kuwait',
'Asia/Nicosia'=>'(+03:00 UTC) Nicosia',
'Asia/Qatar'=>'(+03:00 UTC) Qatar',
'Asia/Riyadh'=>'(+03:00 UTC) Riyadh',
'Asia/Kabul'=>'(+04:30 UTC) Kabul',
'Asia/Tehran'=>'(+04:30 UTC) Tehran',
'Asia/Dubai'=>'(+04:00 UTC) Dubai',
'Asia/Muscat'=>'(+04:00 UTC) Muscat',
'Asia/Tbilisi'=>'(+04:00 UTC) Tbilisi',
'Asia/Yerevan'=>'(+04:00 UTC) Yerevan',
'Asia/Kathmandu'=>'(+05:45 UTC) Kathmandu',
'Asia/Colombo'=>'(+05:30 UTC) Colombo',
'Asia/Kolkata'=>'(+05:30 UTC) Kolkata',
'Asia/Aqtau'=>'(+05:00 UTC) Aqtau',
'Asia/Aqtobe'=>'(+05:00 UTC) Aqtobe',
'Asia/Ashgabat'=>'(+05:00 UTC) Ashgabat',
'Asia/Baku'=>'(+05:00 UTC) Baku',
'Asia/Dushanbe'=>'(+05:00 UTC) Dushanbe',
'Asia/Karachi'=>'(+05:00 UTC) Karachi',
'Asia/Oral'=>'(+05:00 UTC) Oral',
'Asia/Samarkand'=>'(+05:00 UTC) Samarkand',
'Asia/Tashkent'=>'(+05:00 UTC) Tashkent',
'Asia/Yekaterinburg'=>'(+05:00 UTC) Yekaterinburg',
'Asia/Almaty'=>'(+06:00 UTC) Almaty',
'Asia/Bishkek'=>'(+06:00 UTC) Bishkek',
'Asia/Dhaka'=>'(+06:00 UTC) Dhaka',
'Asia/Novosibirsk'=>'(+06:00 UTC) Novosibirsk',
'Asia/Omsk'=>'(+06:00 UTC) Omsk',
'Asia/Qyzylorda'=>'(+06:00 UTC) Qyzylorda',
'Asia/Thimphu'=>'(+06:00 UTC) Thimphu',
'Asia/Urumqi'=>'(+06:00 UTC) Urumqi',
'Asia/Rangoon'=>'(+06:30 UTC) Rangoon',
'Asia/Bangkok'=>'(+07:00 UTC) Bangkok',
'Asia/Ho_Chi_Minh'=>'(+07:00 UTC) Ho Chi Minh',
'Asia/Jakarta'=>'(+07:00 UTC) Jakarta',
'Asia/Krasnoyarsk'=>'(+07:00 UTC) Krasnoyarsk',
'Asia/Novokuznetsk'=>'(+07:00 UTC) Novokuznetsk',
'Asia/Phnom_Penh'=>'(+07:00 UTC) Phnom Penh',
'Asia/Pontianak'=>'(+07:00 UTC) Pontianak',
'Asia/Vientiane'=>'(+07:00 UTC) Vientiane',
'Asia/Pyongyang'=>'(+08:30 UTC) Pyongyang',
'Asia/Brunei'=>'(+08:00 UTC) Brunei',
'Asia/Chita'=>'(+08:00 UTC) Chita',
'Asia/Hong_Kong'=>'(+08:00 UTC) Hong Kong',
'Asia/Hovd'=>'(+08:00 UTC) Hovd',
'Asia/Irkutsk'=>'(+08:00 UTC) Irkutsk',
'Asia/Kuala_Lumpur'=>'(+08:00 UTC) Kuala Lumpur',
'Asia/Kuching'=>'(+08:00 UTC) Kuching',
'Asia/Macau'=>'(+08:00 UTC) Macau',
'Asia/Makassar'=>'(+08:00 UTC) Makassar',
'Asia/Manila'=>'(+08:00 UTC) Manila',
'Asia/Shanghai'=>'(+08:00 UTC) Shanghai',
'Asia/Singapore'=>'(+08:00 UTC) Singapore',
'Asia/Taipei'=>'(+08:00 UTC) Taipei',
'Asia/Choibalsan'=>'(+09:00 UTC) Choibalsan',
'Asia/Dili'=>'(+09:00 UTC) Dili',
'Asia/Jayapura'=>'(+09:00 UTC) Jayapura',
'Asia/Khandyga'=>'(+09:00 UTC) Khandyga',
'Asia/Seoul'=>'(+09:00 UTC) Seoul',
'Asia/Tokyo'=>'(+09:00 UTC) Tokyo',
'Asia/Ulaanbaatar'=>'(+09:00 UTC) Ulaanbaatar',
'Asia/Yakutsk'=>'(+09:00 UTC) Yakutsk',
'Asia/Magadan'=>'(+10:00 UTC) Magadan',
'Asia/Sakhalin'=>'(+10:00 UTC) Sakhalin',
'Asia/Ust-Nera'=>'(+10:00 UTC) Ust-Nera',
'Asia/Vladivostok'=>'(+10:00 UTC) Vladivostok',
'Asia/Srednekolymsk'=>'(+11:00 UTC) Srednekolymsk',
'Asia/Anadyr'=>'(+12:00 UTC) Anadyr',
'Asia/Kamchatka'=>'(+12:00 UTC) Kamchatka',
 'Atlantic/Stanley'=>'Atlantic',
'Atlantic/Stanley'=>'(-03:00 UTC) Stanley',
'Atlantic/South_Georgia'=>'(-02:00 UTC) South Georgia',
'Atlantic/Cape_Verde'=>'(-01:00 UTC) Cape Verde',
'Atlantic/Azores'=>'(+00:00 UTC) Azores',
'Atlantic/Reykjavik'=>'(+00:00 UTC) Reykjavik',
'Atlantic/St_Helena'=>'(+00:00 UTC) St Helena',
'Atlantic/Canary'=>'(+01:00 UTC) Canary',
'Atlantic/Faroe'=>'(+01:00 UTC) Faroe',
'Atlantic/Madeira'=>'(+01:00 UTC) Madeira',
 'Europe/Guernsey'=>'Europe',
'Europe/Guernsey'=>'(+01:00 UTC) Guernsey',
'Europe/Isle_of_Man'=>'(+01:00 UTC) Isle of Man',
'Europe/Jersey'=>'(+01:00 UTC) Jersey',
'Europe/Lisbon'=>'(+01:00 UTC) Lisbon',
'Europe/London'=>'(+01:00 UTC) London',
'Europe/Amsterdam'=>'(+02:00 UTC) Amsterdam',
'Europe/Andorra'=>'(+02:00 UTC) Andorra',
'Europe/Belgrade'=>'(+02:00 UTC) Belgrade',
'Europe/Berlin'=>'(+02:00 UTC) Berlin',
'Europe/Bratislava'=>'(+02:00 UTC) Bratislava',
'Europe/Brussels'=>'(+02:00 UTC) Brussels',
'Europe/Budapest'=>'(+02:00 UTC) Budapest',
'Europe/Busingen'=>'(+02:00 UTC) Busingen',
'Europe/Copenhagen'=>'(+02:00 UTC) Copenhagen',
'Europe/Gibraltar'=>'(+02:00 UTC) Gibraltar',
'Europe/Kaliningrad'=>'(+02:00 UTC) Kaliningrad',
'Europe/Ljubljana'=>'(+02:00 UTC) Ljubljana',
'Europe/Luxembourg'=>'(+02:00 UTC) Luxembourg',
'Europe/Madrid'=>'(+02:00 UTC) Madrid',
'Europe/Malta'=>'(+02:00 UTC) Malta',
'Europe/Monaco'=>'(+02:00 UTC) Monaco',
'Europe/Oslo'=>'(+02:00 UTC) Oslo',
'Europe/Paris'=>'(+02:00 UTC) Paris',
'Europe/Podgorica'=>'(+02:00 UTC) Podgorica',
'Europe/Prague'=>'(+02:00 UTC) Prague',
'Europe/Rome'=>'(+02:00 UTC) Rome',
'Europe/San_Marino'=>'(+02:00 UTC) San Marino',
'Europe/Sarajevo'=>'(+02:00 UTC) Sarajevo',
'Europe/Skopje'=>'(+02:00 UTC) Skopje',
'Europe/Stockholm'=>'(+02:00 UTC) Stockholm',
'Europe/Tirane'=>'(+02:00 UTC) Tirane',
'Europe/Vaduz'=>'(+02:00 UTC) Vaduz',
'Europe/Vatican'=>'(+02:00 UTC) Vatican',
'Europe/Vienna'=>'(+02:00 UTC) Vienna',
'Europe/Warsaw'=>'(+02:00 UTC) Warsaw',
'Europe/Zagreb'=>'(+02:00 UTC) Zagreb',
'Europe/Zurich'=>'(+02:00 UTC) Zurich',
'Europe/Athens'=>'(+03:00 UTC) Athens',
'Europe/Bucharest'=>'(+03:00 UTC) Bucharest',
'Europe/Chisinau'=>'(+03:00 UTC) Chisinau',
'Europe/Helsinki'=>'(+03:00 UTC) Helsinki',
'Europe/Istanbul'=>'(+03:00 UTC) Istanbul',
'Europe/Kiev'=>'(+03:00 UTC) Kiev',
'Europe/Mariehamn'=>'(+03:00 UTC) Mariehamn',
 'Europe/Minsk'=>'(+03:00 UTC) Minsk',
'Europe/Moscow'=>'(+03:00 UTC) Moscow',
'Europe/Riga'=>'(+03:00 UTC) Riga',
'Europe/Simferopol'=>'(+03:00 UTC) Simferopol',
'Europe/Sofia'=>'(+03:00 UTC) Sofia',
'Europe/Tallinn'=>'(+03:00 UTC) Tallinn',
'Europe/Uzhgorod'=>'(+03:00 UTC) Uzhgorod',
'Europe/Vilnius'=>'(+03:00 UTC) Vilnius',
'Europe/Volgograd'=>'(+03:00 UTC) Volgograd',
'Europe/Zaporozhye'=>'(+03:00 UTC) Zaporozhye',
'Europe/Samara'=>'(+04:00 UTC) Samara',
 'Indian/Comoro'=>'Indian',
'Indian/Comoro'=>'(+03:00 UTC) Comoro',
'Indian/Mayotte'=>'(+03:00 UTC) Mayotte',
'Indian/Mahe'=>'(+04:00 UTC) Mahe',
'Indian/Mauritius'=>'(+04:00 UTC) Mauritius',
'Indian/Reunion'=>'(+04:00 UTC) Reunion',
'Indian/Kerguelen'=>'(+05:00 UTC) Kerguelen',
'Indian/Maldives'=>'(+05:00 UTC) Maldives',
'Indian/Cocos'=>'(+06:30 UTC) Cocos',
'Indian/Chagos'=>'(+06:00 UTC) Chagos',
'Indian/Christmas'=>'(+07:00 UTC) Christmas',
 'Pacific/Niue'=>'Pacific',
'Pacific/Niue'=>'(-11:00 UTC) Niue',
'Pacific/Pago_Pago'=>'(-11:00 UTC) Pago Pago',
'Pacific/Honolulu'=>'(-10:00 UTC) Honolulu',
'Pacific/Johnston'=>'(-10:00 UTC) Johnston',
'Pacific/Rarotonga'=>'(-10:00 UTC) Rarotonga',
'Pacific/Tahiti'=>'(-10:00 UTC) Tahiti',
'Pacific/Marquesas'=>'(-09:30 UTC) Marquesas',
'Pacific/Gambier'=>'(-09:00 UTC) Gambier',
'Pacific/Pitcairn'=>'(-08:00 UTC) Pitcairn',
'Pacific/Galapagos'=>'(-06:00 UTC) Galapagos',
'Pacific/Easter'=>'(-05:00 UTC) Easter',
'Pacific/Palau'=>'(+09:00 UTC) Palau',
'Pacific/Chuuk'=>'(+10:00 UTC) Chuuk',
'Pacific/Guam'=>'(+10:00 UTC) Guam',
'Pacific/Port_Moresby'=>'(+10:00 UTC) Port Moresby',
'Pacific/Saipan'=>'(+10:00 UTC) Saipan',
'Pacific/Norfolk'=>'(+11:30 UTC) Norfolk',
'Pacific/Bougainville'=>'(+11:00 UTC) Bougainville',
'Pacific/Efate'=>'(+11:00 UTC) Efate',
'Pacific/Guadalcanal'=>'(+11:00 UTC) Guadalcanal',
'Pacific/Kosrae'=>'(+11:00 UTC) Kosrae',
'Pacific/Noumea'=>'(+11:00 UTC) Noumea',
'Pacific/Pohnpei'=>'(+11:00 UTC) Pohnpei',
'Pacific/Auckland'=>'(+12:00 UTC) Auckland',
'Pacific/Fiji'=>'(+12:00 UTC) Fiji',
'Pacific/Funafuti'=>'(+12:00 UTC) Funafuti',
'Pacific/Kwajalein'=>'(+12:00 UTC) Kwajalein',
'Pacific/Majuro'=>'(+12:00 UTC) Majuro',
'Pacific/Nauru'=>'(+12:00 UTC) Nauru',
'Pacific/Tarawa'=>'(+12:00 UTC) Tarawa',
'Pacific/Wake'=>'(+12:00 UTC) Wake',
'Pacific/Wallis'=>'(+12:00 UTC) Wallis',
'Pacific/Chatham'=>'(+12:45 UTC) Chatham',
'Pacific/Apia'=>'(+13:00 UTC) Apia',
'Pacific/Enderbury'=>'(+13:00 UTC) Enderbury',
'Pacific/Fakaofo'=>'(+13:00 UTC) Fakaofo',
'Pacific/Tongatapu'=>'(+13:00 UTC) Tongatapu'  );

		foreach ($list as $k=>$v)
		{
			$vv=explode(') ',$v);
			$list[$k]=$vv[1].' '.$vv[0].')';
		}
		 
		return $list;
 
        
        foreach ($regions as $mask) {
            $zones = DateTimeZone::listIdentifiers($mask);
            $zones = self::prepareZones($zones);
 
            foreach ($zones as $zone) {
                $continent = $zone['continent'];
                $city = $zone['city'];
                $subcity = $zone['subcity'];
                $p = $zone['p'];
                $timeZone = $zone['time_zone'];
 
                
 
                if ($city) {
                    if ($subcity) {
                        $city = $city . '/'. $subcity;
                    }
 
                    $structure[$timeZone]= "(".$p. " UTC) " .str_replace('_',' ',$city);
                }
 
                $selectContinent = $continent;
            }
        }
 
        
 
        return $structure;
    }
	
    public static function getTimeZoneSelect($name='time_zone',$selectedZone = NULL)
    {
        $regions = array(
            'Africa' => DateTimeZone::AFRICA,
            'America' => DateTimeZone::AMERICA,
            'Antarctica' => DateTimeZone::ANTARCTICA,
            'Aisa' => DateTimeZone::ASIA,
            'Atlantic' => DateTimeZone::ATLANTIC,
            'Europe' => DateTimeZone::EUROPE,
            'Indian' => DateTimeZone::INDIAN,
            'Pacific' => DateTimeZone::PACIFIC
        );
 
        $structure = '<select name="'.$name.'">';
        
        foreach ($regions as $mask) {
            $zones = DateTimeZone::listIdentifiers($mask);
            $zones = self::prepareZones($zones);
 
            foreach ($zones as $zone) {
                $continent = $zone['continent'];
                $city = $zone['city'];
                $subcity = $zone['subcity'];
                $p = $zone['p'];
                $timeZone = $zone['time_zone'];
 
                if (!isset($selectContinent)) {
                    $structure .= '<optgroup label="'.$continent.'">';
                }
                elseif ($selectContinent != $continent) {
                    $structure .= '</optgroup><optgroup label="'.$continent.'">';
                }
 
                if ($city) {
                    if ($subcity) {
                        $city = $city . '/'. $subcity;
                    }
 
                    $structure .= "<option ".(($timeZone == $selectedZone) ? 'selected="selected "':'') . " value=\"".($timeZone)."\">(".$p. " UTC) " .str_replace('_',' ',$city)."</option>";
                }
 
                $selectContinent = $continent;
            }
        }
 
        $structure .= '</optgroup></select>';
 
        return $structure;
    }
 
    private static function prepareZones(array $timeZones)
    {
        $list = array();
        foreach ($timeZones as $zone) {
            $time = new DateTime(NULL, new DateTimeZone($zone));
            $p = $time->format('P');
            if ($p > 13) {
                continue;
            }
            $parts = explode('/', $zone);
 
            $list[$time->format('P')][] = array(
                'time_zone' => $zone,
                'continent' => isset($parts[0]) ? $parts[0] : '',
                'city' => isset($parts[1]) ? $parts[1] : '',
                'subcity' => isset($parts[2]) ? $parts[2] : '',
                'p' => $p,
            );
        }
 
        ksort($list, SORT_NUMERIC);
 
        $zones = array();
        foreach ($list as $grouped) {
            $zones = array_merge($zones, $grouped);
        }
 
        return $zones;
    }
}