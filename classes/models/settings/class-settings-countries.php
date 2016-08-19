<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

/**
 * Class Settings_countries
 * @package mp_restaurant_menu\classes\models
 */
class Settings_countries extends Model {
	protected static $instance;

	/**
	 * @return Settings_countries
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @return mixed|void
	 */
	public function get_states_list() {
		$states = array(
			'' => '',
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District of Columbia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
			'AS' => 'American Samoa',
			'CZ' => 'Canal Zone',
			'CM' => 'Commonwealth of the Northern Mariana Islands',
			'FM' => 'Federated States of Micronesia',
			'GU' => 'Guam',
			'MH' => 'Marshall Islands',
			'MP' => 'Northern Mariana Islands',
			'PW' => 'Palau',
			'PI' => 'Philippine Islands',
			'PR' => 'Puerto Rico',
			'TT' => 'Trust Territory of the Pacific Islands',
			'VI' => 'Virgin Islands',
			'AA' => 'Armed Forces - Americas',
			'AE' => 'Armed Forces - Europe, Canada, Middle East, Africa',
			'AP' => 'Armed Forces - Pacific'
		);
		return apply_filters('mprm_us_states', $states);
	}

	/**
	 * Get Provinces List
	 *
	 * @access      public
	 * @since       1.2
	 * @return      array
	 */
	public function get_provinces_list() {
		$provinces = array(
			'' => '',
			'AB' => 'Alberta',
			'BC' => 'British Columbia',
			'MB' => 'Manitoba',
			'NB' => 'New Brunswick',
			'NL' => 'Newfoundland and Labrador',
			'NS' => 'Nova Scotia',
			'NT' => 'Northwest Territories',
			'NU' => 'Nunavut',
			'ON' => 'Ontario',
			'PE' => 'Prince Edward Island',
			'QC' => 'Quebec',
			'SK' => 'Saskatchewan',
			'YT' => 'Yukon'
		);
		return apply_filters('mprm_canada_provinces', $provinces);
	}

	/**
	 * Get Australian States
	 *
	 * @since 1.6
	 * @return array $states A list of states
	 */
	public function get_australian_states_list() {
		$states = array(
			'' => '',
			'ACT' => 'Australian Capital Territory',
			'NSW' => 'New South Wales',
			'NT' => 'Northern Territory',
			'QLD' => 'Queensland',
			'SA' => 'South Australia',
			'TAS' => 'Tasmania',
			'VIC' => 'Victoria',
			'WA' => 'Western Australia'
		);
		return apply_filters('mprm_australian_states', $states);
	}

	/**
	 * Get Bangladeshi States (districts)
	 *
	 * @since 2.2.3
	 * @return array $states A list of states
	 */
	public function get_bangladeshi_states_list() {
		$states = array(
			'' => '',
			'BAG' => 'Bagerhat',
			'BAN' => 'Bandarban',
			'BAR' => 'Barguna',
			'BARI' => 'Barisal',
			'BHO' => 'Bhola',
			'BOG' => 'Bogra',
			'BRA' => 'Brahmanbaria',
			'CHA' => 'Chandpur',
			'CHI' => 'Chittagong',
			'CHU' => 'Chuadanga',
			'COM' => 'Comilla',
			'COX' => 'Cox\'s Bazar',
			'DHA' => 'Dhaka',
			'DIN' => 'Dinajpur',
			'FAR' => 'Faridpur',
			'FEN' => 'Feni',
			'GAI' => 'Gaibandha',
			'GAZI' => 'Gazipur',
			'GOP' => 'Gopalganj',
			'HAB' => 'Habiganj',
			'JAM' => 'Jamalpur',
			'JES' => 'Jessore',
			'JHA' => 'Jhalokati',
			'JHE' => 'Jhenaidah',
			'JOY' => 'Joypurhat',
			'KHA' => 'Khagrachhari',
			'KHU' => 'Khulna',
			'KIS' => 'Kishoreganj',
			'KUR' => 'Kurigram',
			'KUS' => 'Kushtia',
			'LAK' => 'Lakshmipur',
			'LAL' => 'Lalmonirhat',
			'MAD' => 'Madaripur',
			'MAG' => 'Magura',
			'MAN' => 'Manikganj',
			'MEH' => 'Meherpur',
			'MOU' => 'Moulvibazar',
			'MUN' => 'Munshiganj',
			'MYM' => 'Mymensingh',
			'NAO' => 'Naogaon',
			'NAR' => 'Narail',
			'NARG' => 'Narayanganj',
			'NARD' => 'Narsingdi',
			'NAT' => 'Natore',
			'NAW' => 'Nawabganj',
			'NET' => 'Netrakona',
			'NIL' => 'Nilphamari',
			'NOA' => 'Noakhali',
			'PAB' => 'Pabna',
			'PAN' => 'Panchagarh',
			'PAT' => 'Patuakhali',
			'PIR' => 'Pirojpur',
			'RAJB' => 'Rajbari',
			'RAJ' => 'Rajshahi',
			'RAN' => 'Rangamati',
			'RANP' => 'Rangpur',
			'SAT' => 'Satkhira',
			'SHA' => 'Shariatpur',
			'SHE' => 'Sherpur',
			'SIR' => 'Sirajganj',
			'SUN' => 'Sunamganj',
			'SYL' => 'Sylhet',
			'TAN' => 'Tangail',
			'THA' => 'Thakurgaon'
		);
		return apply_filters('mprm_bangladeshi_states', $states);
	}

	/**
	 * Get Brazil States
	 *
	 * @since 1.6
	 * @return array $states A list of states
	 */
	public function get_brazil_states_list() {
		$states = array(
			'' => '',
			'AC' => 'Acre',
			'AL' => 'Alagoas',
			'AP' => 'Amap&aacute;',
			'AM' => 'Amazonas',
			'BA' => 'Bahia',
			'CE' => 'Cear&aacute;',
			'DF' => 'Distrito Federal',
			'ES' => 'Esp&iacute;rito Santo',
			'GO' => 'Goi&aacute;s',
			'MA' => 'Maranh&atilde;o',
			'MT' => 'Mato Grosso',
			'MS' => 'Mato Grosso do Sul',
			'MG' => 'Minas Gerais',
			'PA' => 'Par&aacute;',
			'PB' => 'Para&iacute;ba',
			'PR' => 'Paran&aacute;',
			'PE' => 'Pernambuco',
			'PI' => 'Piau&iacute;',
			'RJ' => 'Rio de Janeiro',
			'RN' => 'Rio Grande do Norte',
			'RS' => 'Rio Grande do Sul',
			'RO' => 'Rond&ocirc;nia',
			'RR' => 'Roraima',
			'SC' => 'Santa Catarina',
			'SP' => 'S&atilde;o Paulo',
			'SE' => 'Sergipe',
			'TO' => 'Tocantins'
		);
		return apply_filters('mprm_brazil_states', $states);
	}

	/**
	 * Get Bulgarian States
	 *
	 * @since 2.2.3
	 * @return array $states A list of states
	 */
	public function get_bulgarian_states_list() {
		$states = array(
			'' => '',
			'BG-01' => 'Blagoevgrad',
			'BG-02' => 'Burgas',
			'BG-08' => 'Dobrich',
			'BG-07' => 'Gabrovo',
			'BG-26' => 'Haskovo',
			'BG-09' => 'Kardzhali',
			'BG-10' => 'Kyustendil',
			'BG-11' => 'Lovech',
			'BG-12' => 'Montana',
			'BG-13' => 'Pazardzhik',
			'BG-14' => 'Pernik',
			'BG-15' => 'Pleven',
			'BG-16' => 'Plovdiv',
			'BG-17' => 'Razgrad',
			'BG-18' => 'Ruse',
			'BG-27' => 'Shumen',
			'BG-19' => 'Silistra',
			'BG-20' => 'Sliven',
			'BG-21' => 'Smolyan',
			'BG-23' => 'Sofia',
			'BG-22' => 'Sofia-Grad',
			'BG-24' => 'Stara Zagora',
			'BG-25' => 'Targovishte',
			'BG-03' => 'Varna',
			'BG-04' => 'Veliko Tarnovo',
			'BG-05' => 'Vidin',
			'BG-06' => 'Vratsa',
			'BG-28' => 'Yambol'
		);
		return apply_filters('mprm_bulgarian_states', $states);
	}

	/**
	 * Get Hong Kong States
	 *
	 * @since 1.6
	 * @return array $states A list of states
	 */
	public function get_hong_kong_states_list() {
		$states = array(
			'' => '',
			'HONG KONG' => 'Hong Kong Island',
			'KOWLOON' => 'Kowloon',
			'NEW TERRITORIES' => 'New Territories'
		);
		return apply_filters('mprm_hong_kong_states', $states);
	}

	/**
	 * Get Hungary States
	 *
	 * @since 1.6
	 * @return array $states A list of states
	 */
	public function get_hungary_states_list() {
		$states = array(
			'' => '',
			'BK' => 'Bács-Kiskun',
			'BE' => 'Békés',
			'BA' => 'Baranya',
			'BZ' => 'Borsod-Abaúj-Zemplén',
			'BU' => 'Budapest',
			'CS' => 'Csongrád',
			'FE' => 'Fejér',
			'GS' => 'Győr-Moson-Sopron',
			'HB' => 'Hajdú-Bihar',
			'HE' => 'Heves',
			'JN' => 'Jász-Nagykun-Szolnok',
			'KE' => 'Komárom-Esztergom',
			'NO' => 'Nógrád',
			'PE' => 'Pest',
			'SO' => 'Somogy',
			'SZ' => 'Szabolcs-Szatmár-Bereg',
			'TO' => 'Tolna',
			'VA' => 'Vas',
			'VE' => 'Veszprém',
			'ZA' => 'Zala'
		);
		return apply_filters('mprm_hungary_states', $states);
	}

	/**
	 * Get Japanese States
	 *
	 * @since 2.2.3
	 * @return array $states A list of states
	 */
	public function get_japanese_states_list() {
		$states = array(
			'' => '',
			'JP01' => 'Hokkaido',
			'JP02' => 'Aomori',
			'JP03' => 'Iwate',
			'JP04' => 'Miyagi',
			'JP05' => 'Akita',
			'JP06' => 'Yamagata',
			'JP07' => 'Fukushima',
			'JP08' => 'Ibaraki',
			'JP09' => 'Tochigi',
			'JP10' => 'Gunma',
			'JP11' => 'Saitama',
			'JP12' => 'Chiba',
			'JP13' => 'Tokyo',
			'JP14' => 'Kanagawa',
			'JP15' => 'Niigata',
			'JP16' => 'Toyama',
			'JP17' => 'Ishikawa',
			'JP18' => 'Fukui',
			'JP19' => 'Yamanashi',
			'JP20' => 'Nagano',
			'JP21' => 'Gifu',
			'JP22' => 'Shizuoka',
			'JP23' => 'Aichi',
			'JP24' => 'Mie',
			'JP25' => 'Shiga',
			'JP26' => 'Kyouto',
			'JP27' => 'Osaka',
			'JP28' => 'Hyougo',
			'JP29' => 'Nara',
			'JP30' => 'Wakayama',
			'JP31' => 'Tottori',
			'JP32' => 'Shimane',
			'JP33' => 'Okayama',
			'JP34' => 'Hiroshima',
			'JP35' => 'Yamaguchi',
			'JP36' => 'Tokushima',
			'JP37' => 'Kagawa',
			'JP38' => 'Ehime',
			'JP39' => 'Kochi',
			'JP40' => 'Fukuoka',
			'JP41' => 'Saga',
			'JP42' => 'Nagasaki',
			'JP43' => 'Kumamoto',
			'JP44' => 'Oita',
			'JP45' => 'Miyazaki',
			'JP46' => 'Kagoshima',
			'JP47' => 'Okinawa'
		);
		return apply_filters('mprm_japanese_states', $states);
	}

	/**
	 * Get Chinese States
	 *
	 * @since 1.6
	 * @return array $states A list of states
	 */
	public function get_chinese_states_list() {
		$states = array(
			'' => '',
			'CN1' => 'Yunnan / &#20113;&#21335;',
			'CN2' => 'Beijing / &#21271;&#20140;',
			'CN3' => 'Tianjin / &#22825;&#27941;',
			'CN4' => 'Hebei / &#27827;&#21271;',
			'CN5' => 'Shanxi / &#23665;&#35199;',
			'CN6' => 'Inner Mongolia / &#20839;&#33945;&#21476;',
			'CN7' => 'Liaoning / &#36797;&#23425;',
			'CN8' => 'Jilin / &#21513;&#26519;',
			'CN9' => 'Heilongjiang / &#40657;&#40857;&#27743;',
			'CN10' => 'Shanghai / &#19978;&#28023;',
			'CN11' => 'Jiangsu / &#27743;&#33487;',
			'CN12' => 'Zhejiang / &#27993;&#27743;',
			'CN13' => 'Anhui / &#23433;&#24509;',
			'CN14' => 'Fujian / &#31119;&#24314;',
			'CN15' => 'Jiangxi / &#27743;&#35199;',
			'CN16' => 'Shandong / &#23665;&#19996;',
			'CN17' => 'Henan / &#27827;&#21335;',
			'CN18' => 'Hubei / &#28246;&#21271;',
			'CN19' => 'Hunan / &#28246;&#21335;',
			'CN20' => 'Guangdong / &#24191;&#19996;',
			'CN21' => 'Guangxi Zhuang / &#24191;&#35199;&#22766;&#26063;',
			'CN22' => 'Hainan / &#28023;&#21335;',
			'CN23' => 'Chongqing / &#37325;&#24198;',
			'CN24' => 'Sichuan / &#22235;&#24029;',
			'CN25' => 'Guizhou / &#36149;&#24030;',
			'CN26' => 'Shaanxi / &#38485;&#35199;',
			'CN27' => 'Gansu / &#29976;&#32899;',
			'CN28' => 'Qinghai / &#38738;&#28023;',
			'CN29' => 'Ningxia Hui / &#23425;&#22799;',
			'CN30' => 'Macau / &#28595;&#38376;',
			'CN31' => 'Tibet / &#35199;&#34255;',
			'CN32' => 'Xinjiang / &#26032;&#30086;'
		);
		return apply_filters('mprm_chinese_states', $states);
	}

	/**
	 * Get New Zealand States
	 *
	 * @since 1.6
	 * @return array $states A list of states
	 */
	public function get_new_zealand_states_list() {
		$states = array(
			'' => '',
			'AK' => 'Auckland',
			'BP' => 'Bay of Plenty',
			'CT' => 'Canterbury',
			'HB' => 'Hawke&rsquo;s Bay',
			'MW' => 'Manawatu-Wanganui',
			'MB' => 'Marlborough',
			'NS' => 'Nelson',
			'NL' => 'Northland',
			'OT' => 'Otago',
			'SL' => 'Southland',
			'TK' => 'Taranaki',
			'TM' => 'Tasman',
			'WA' => 'Waikato',
			'WR' => 'Wairarapa',
			'WE' => 'Wellington',
			'WC' => 'West Coast'
		);
		return apply_filters('mprm_new_zealand_states', $states);
	}

	/**
	 * Get Peruvian States
	 *
	 * @since 2.2.3
	 * @return array $states A list of states
	 */
	public function get_peruvian_states_list() {
		$states = array(
			'' => '',
			'CAL' => 'El Callao',
			'LMA' => 'Municipalidad Metropolitana de Lima',
			'AMA' => 'Amazonas',
			'ANC' => 'Ancash',
			'APU' => 'Apur&iacute;mac',
			'ARE' => 'Arequipa',
			'AYA' => 'Ayacucho',
			'CAJ' => 'Cajamarca',
			'CUS' => 'Cusco',
			'HUV' => 'Huancavelica',
			'HUC' => 'Hu&aacute;nuco',
			'ICA' => 'Ica',
			'JUN' => 'Jun&iacute;n',
			'LAL' => 'La Libertad',
			'LAM' => 'Lambayeque',
			'LIM' => 'Lima',
			'LOR' => 'Loreto',
			'MDD' => 'Madre de Dios',
			'MOQ' => 'Moquegua',
			'PAS' => 'Pasco',
			'PIU' => 'Piura',
			'PUN' => 'Puno',
			'SAM' => 'San Mart&iacute;n',
			'TAC' => 'Tacna',
			'TUM' => 'Tumbes',
			'UCA' => 'Ucayali'
		);
		return apply_filters('mprm_peruvian_states', $states);
	}

	/**
	 * Get Indonesian States
	 *
	 * @since 1.6
	 * @return array $states A list of states
	 */
	public function get_indonesian_states_list() {
		$states = array(
			'' => '',
			'AC' => 'Daerah Istimewa Aceh',
			'SU' => 'Sumatera Utara',
			'SB' => 'Sumatera Barat',
			'RI' => 'Riau',
			'KR' => 'Kepulauan Riau',
			'JA' => 'Jambi',
			'SS' => 'Sumatera Selatan',
			'BB' => 'Bangka Belitung',
			'BE' => 'Bengkulu',
			'LA' => 'Lampung',
			'JK' => 'DKI Jakarta',
			'JB' => 'Jawa Barat',
			'BT' => 'Banten',
			'JT' => 'Jawa Tengah',
			'JI' => 'Jawa Timur',
			'YO' => 'Daerah Istimewa Yogyakarta',
			'BA' => 'Bali',
			'NB' => 'Nusa Tenggara Barat',
			'NT' => 'Nusa Tenggara Timur',
			'KB' => 'Kalimantan Barat',
			'KT' => 'Kalimantan Tengah',
			'KI' => 'Kalimantan Timur',
			'KS' => 'Kalimantan Selatan',
			'KU' => 'Kalimantan Utara',
			'SA' => 'Sulawesi Utara',
			'ST' => 'Sulawesi Tengah',
			'SG' => 'Sulawesi Tenggara',
			'SR' => 'Sulawesi Barat',
			'SN' => 'Sulawesi Selatan',
			'GO' => 'Gorontalo',
			'MA' => 'Maluku',
			'MU' => 'Maluku Utara',
			'PA' => 'Papua',
			'PB' => 'Papua Barat'
		);
		return apply_filters('mprm_indonesia_states', $states);
	}

	/**
	 * Get Indian States
	 *
	 * @since 1.6
	 * @return array $states A list of states
	 */
	public function get_indian_states_list() {
		$states = array(
			'' => '',
			'AP' => 'Andhra Pradesh',
			'AR' => 'Arunachal Pradesh',
			'AS' => 'Assam',
			'BR' => 'Bihar',
			'CT' => 'Chhattisgarh',
			'GA' => 'Goa',
			'GJ' => 'Gujarat',
			'HR' => 'Haryana',
			'HP' => 'Himachal Pradesh',
			'JK' => 'Jammu and Kashmir',
			'JH' => 'Jharkhand',
			'KA' => 'Karnataka',
			'KL' => 'Kerala',
			'MP' => 'Madhya Pradesh',
			'MH' => 'Maharashtra',
			'MN' => 'Manipur',
			'ML' => 'Meghalaya',
			'MZ' => 'Mizoram',
			'NL' => 'Nagaland',
			'OR' => 'Orissa',
			'PB' => 'Punjab',
			'RJ' => 'Rajasthan',
			'SK' => 'Sikkim',
			'TN' => 'Tamil Nadu',
			'TG' => 'Telangana',
			'TR' => 'Tripura',
			'UT' => 'Uttarakhand',
			'UP' => 'Uttar Pradesh',
			'WB' => 'West Bengal',
			'AN' => 'Andaman and Nicobar Islands',
			'CH' => 'Chandigarh',
			'DN' => 'Dadar and Nagar Haveli',
			'DD' => 'Daman and Diu',
			'DL' => 'Delhi',
			'LD' => 'Lakshadweep',
			'PY' => 'Pondicherry (Puducherry)'
		);
		return apply_filters('mprm_indian_states', $states);
	}

	/**
	 * Get Iranian States
	 *
	 * @since 2.2.3
	 * @return array $states A list of states
	 */
	public function get_iranian_states_list() {
		$states = array(
			'' => '',
			'KHZ' => 'Khuzestan',
			'THR' => 'Tehran',
			'ILM' => 'Ilaam',
			'BHR' => 'Bushehr',
			'ADL' => 'Ardabil',
			'ESF' => 'Isfahan',
			'YZD' => 'Yazd',
			'KRH' => 'Kermanshah',
			'KRN' => 'Kerman',
			'HDN' => 'Hamadan',
			'GZN' => 'Ghazvin',
			'ZJN' => 'Zanjan',
			'LRS' => 'Luristan',
			'ABZ' => 'Alborz',
			'EAZ' => 'East Azerbaijan',
			'WAZ' => 'West Azerbaijan',
			'CHB' => 'Chaharmahal and Bakhtiari',
			'SKH' => 'South Khorasan',
			'RKH' => 'Razavi Khorasan',
			'NKH' => 'North Khorasan',
			'SMN' => 'Semnan',
			'FRS' => 'Fars',
			'QHM' => 'Qom',
			'KRD' => 'Kurdistan',
			'KBD' => 'Kohgiluyeh and BoyerAhmad',
			'GLS' => 'Golestan',
			'GIL' => 'Gilan',
			'MZN' => 'Mazandaran',
			'MKZ' => 'Markazi',
			'HRZ' => 'Hormozgan',
			'SBN' => 'Sistan and Baluchestan'
		);
		return apply_filters('mprm_iranian_states', $states);
	}

	/**
	 * Get Italian Provinces
	 *
	 * @since 2.2.3
	 * @return array $states A list of states
	 */
	public function get_italian_states_list() {
		$states = array(
			'' => '',
			'AG' => 'Agrigento',
			'AL' => 'Alessandria',
			'AN' => 'Ancona',
			'AO' => 'Aosta',
			'AR' => 'Arezzo',
			'AP' => 'Ascoli Piceno',
			'AT' => 'Asti',
			'AV' => 'Avellino',
			'BA' => 'Bari',
			'BT' => 'Barletta-Andria-Trani',
			'BL' => 'Belluno',
			'BN' => 'Benevento',
			'BG' => 'Bergamo',
			'BI' => 'Biella',
			'BO' => 'Bologna',
			'BZ' => 'Bolzano',
			'BS' => 'Brescia',
			'BR' => 'Brindisi',
			'CA' => 'Cagliari',
			'CL' => 'Caltanissetta',
			'CB' => 'Campobasso',
			'CI' => 'Caltanissetta',
			'CE' => 'Caserta',
			'CT' => 'Catania',
			'CZ' => 'Catanzaro',
			'CH' => 'Chieti',
			'CO' => 'Como',
			'CS' => 'Cosenza',
			'CR' => 'Cremona',
			'KR' => 'Crotone',
			'CN' => 'Cuneo',
			'EN' => 'Enna',
			'FM' => 'Fermo',
			'FE' => 'Ferrara',
			'FI' => 'Firenze',
			'FG' => 'Foggia',
			'FC' => 'Forli-Cesena',
			'FR' => 'Frosinone',
			'GE' => 'Genova',
			'GO' => 'Gorizia',
			'GR' => 'Grosseto',
			'IM' => 'Imperia',
			'IS' => 'Isernia',
			'SP' => 'La Spezia',
			'AQ' => 'L&apos;Aquila',
			'LT' => 'Latina',
			'LE' => 'Lecce',
			'LC' => 'Lecco',
			'LI' => 'Livorno',
			'LO' => 'Lodi',
			'LU' => 'Lucca',
			'MC' => 'Macerata',
			'MN' => 'Mantova',
			'MS' => 'Massa-Carrara',
			'MT' => 'Matera',
			'ME' => 'Messina',
			'MI' => 'Milano',
			'MO' => 'Modena',
			'MB' => 'Monza e della Brianza',
			'NA' => 'Napoli',
			'NO' => 'Novara',
			'NU' => 'Nuoro',
			'OT' => 'Olbia-Tempio',
			'OR' => 'Oristano',
			'PD' => 'Padova',
			'PA' => 'Palermo',
			'PR' => 'Parma',
			'PV' => 'Pavia',
			'PG' => 'Perugia',
			'PU' => 'Pesaro e Urbino',
			'PE' => 'Pescara',
			'PC' => 'Piacenza',
			'PI' => 'Pisa',
			'PT' => 'Pistoia',
			'PN' => 'Pordenone',
			'PZ' => 'Potenza',
			'PO' => 'Prato',
			'RG' => 'Ragusa',
			'RA' => 'Ravenna',
			'RC' => 'Reggio Calabria',
			'RE' => 'Reggio Emilia',
			'RI' => 'Rieti',
			'RN' => 'Rimini',
			'RM' => 'Roma',
			'RO' => 'Rovigo',
			'SA' => 'Salerno',
			'VS' => 'Medio Campidano',
			'SS' => 'Sassari',
			'SV' => 'Savona',
			'SI' => 'Siena',
			'SR' => 'Siracusa',
			'SO' => 'Sondrio',
			'TA' => 'Taranto',
			'TE' => 'Teramo',
			'TR' => 'Terni',
			'TO' => 'Torino',
			'OG' => 'Ogliastra',
			'TP' => 'Trapani',
			'TN' => 'Trento',
			'TV' => 'Treviso',
			'TS' => 'Trieste',
			'UD' => 'Udine',
			'VA' => 'Varesa',
			'VE' => 'Venezia',
			'VB' => 'Verbano-Cusio-Ossola',
			'VC' => 'Vercelli',
			'VR' => 'Verona',
			'VV' => 'Vibo Valentia',
			'VI' => 'Vicenza',
			'VT' => 'Viterbo'
		);
		return apply_filters('mprm_italian_states', $states);
	}

	/**
	 * Get Malaysian States
	 *
	 * @since 1.6
	 * @return array $states A list of states
	 */
	public function get_malaysian_states_list() {
		$states = array(
			'' => '',
			'JHR' => 'Johor',
			'KDH' => 'Kedah',
			'KTN' => 'Kelantan',
			'MLK' => 'Melaka',
			'NSN' => 'Negeri Sembilan',
			'PHG' => 'Pahang',
			'PRK' => 'Perak',
			'PLS' => 'Perlis',
			'PNG' => 'Pulau Pinang',
			'SBH' => 'Sabah',
			'SWK' => 'Sarawak',
			'SGR' => 'Selangor',
			'TRG' => 'Terengganu',
			'KUL' => 'W.P. Kuala Lumpur',
			'LBN' => 'W.P. Labuan',
			'PJY' => 'W.P. Putrajaya'
		);
		return apply_filters('mprm_malaysian_states', $states);
	}

	/**
	 * Get Mexican States
	 *
	 * @since 2.2.3
	 * @return array $states A list of states
	 */
	public function get_mexican_states_list() {
		$states = array(
			'' => '',
			'DIF' => 'Distrito Federal',
			'JAL' => 'Jalisco',
			'NLE' => 'Nuevo Le&oacute;n',
			'AGU' => 'Aguascalientes',
			'BCN' => 'Baja California Norte',
			'BCS' => 'Baja California Sur',
			'CAM' => 'Campeche',
			'CHP' => 'Chiapas',
			'CHH' => 'Chihuahua',
			'COA' => 'Coahuila',
			'COL' => 'Colima',
			'DUR' => 'Durango',
			'GUA' => 'Guanajuato',
			'GRO' => 'Guerrero',
			'HID' => 'Hidalgo',
			'MEX' => 'Edo. de M&eacute;xico',
			'MIC' => 'Michoac&aacute;n',
			'MOR' => 'Morelos',
			'NAY' => 'Nayarit',
			'OAX' => 'Oaxaca',
			'PUE' => 'Puebla',
			'QUE' => 'Quer&eacute;taro',
			'ROO' => 'Quintana Roo',
			'SLP' => 'San Luis Potos&iacute;',
			'SIN' => 'Sinaloa',
			'SON' => 'Sonora',
			'TAB' => 'Tabasco',
			'TAM' => 'Tamaulipas',
			'TLA' => 'Tlaxcala',
			'VER' => 'Veracruz',
			'YUC' => 'Yucat&aacute;n',
			'ZAC' => 'Zacatecas'
		);
		return apply_filters('mprm_mexican_states', $states);
	}

	/**
	 * Get Nepalese States (Districts)
	 *
	 * @since 2.2.3
	 * @return array $states A list of states
	 */
	public function get_nepalese_states_list() {
		$states = array(
			'' => '',
			'ILL' => 'Illam',
			'JHA' => 'Jhapa',
			'PAN' => 'Panchthar',
			'TAP' => 'Taplejung',
			'BHO' => 'Bhojpur',
			'DKA' => 'Dhankuta',
			'MOR' => 'Morang',
			'SUN' => 'Sunsari',
			'SAN' => 'Sankhuwa',
			'TER' => 'Terhathum',
			'KHO' => 'Khotang',
			'OKH' => 'Okhaldhunga',
			'SAP' => 'Saptari',
			'SIR' => 'Siraha',
			'SOL' => 'Solukhumbu',
			'UDA' => 'Udayapur',
			'DHA' => 'Dhanusa',
			'DLK' => 'Dolakha',
			'MOH' => 'Mohottari',
			'RAM' => 'Ramechha',
			'SAR' => 'Sarlahi',
			'SIN' => 'Sindhuli',
			'BHA' => 'Bhaktapur',
			'DHD' => 'Dhading',
			'KTM' => 'Kathmandu',
			'KAV' => 'Kavrepalanchowk',
			'LAL' => 'Lalitpur',
			'NUW' => 'Nuwakot',
			'RAS' => 'Rasuwa',
			'SPC' => 'Sindhupalchowk',
			'BAR' => 'Bara',
			'CHI' => 'Chitwan',
			'MAK' => 'Makwanpur',
			'PAR' => 'Parsa',
			'RAU' => 'Rautahat',
			'GOR' => 'Gorkha',
			'KAS' => 'Kaski',
			'LAM' => 'Lamjung',
			'MAN' => 'Manang',
			'SYN' => 'Syangja',
			'TAN' => 'Tanahun',
			'BAG' => 'Baglung',
			'PBT' => 'Parbat',
			'MUS' => 'Mustang',
			'MYG' => 'Myagdi',
			'AGR' => 'Agrghakanchi',
			'GUL' => 'Gulmi',
			'KAP' => 'Kapilbastu',
			'NAW' => 'Nawalparasi',
			'PAL' => 'Palpa',
			'RUP' => 'Rupandehi',
			'DAN' => 'Dang',
			'PYU' => 'Pyuthan',
			'ROL' => 'Rolpa',
			'RUK' => 'Rukum',
			'SAL' => 'Salyan',
			'BAN' => 'Banke',
			'BDA' => 'Bardiya',
			'DAI' => 'Dailekh',
			'JAJ' => 'Jajarkot',
			'SUR' => 'Surkhet',
			'DOL' => 'Dolpa',
			'HUM' => 'Humla',
			'JUM' => 'Jumla',
			'KAL' => 'Kalikot',
			'MUG' => 'Mugu',
			'ACH' => 'Achham',
			'BJH' => 'Bajhang',
			'BJU' => 'Bajura',
			'DOT' => 'Doti',
			'KAI' => 'Kailali',
			'BAI' => 'Baitadi',
			'DAD' => 'Dadeldhura',
			'DAR' => 'Darchula',
			'KAN' => 'Kanchanpur'
		);
		return apply_filters('mprm_nepalese_states', $states);
	}

	/**
	 * Get South African States
	 *
	 * @since 1.6
	 * @return array $states A list of states
	 */
	public function get_south_african_states_list() {
		$states = array(
			'' => '',
			'EC' => 'Eastern Cape',
			'FS' => 'Free State',
			'GP' => 'Gauteng',
			'KZN' => 'KwaZulu-Natal',
			'LP' => 'Limpopo',
			'MP' => 'Mpumalanga',
			'NC' => 'Northern Cape',
			'NW' => 'North West',
			'WC' => 'Western Cape'
		);
		return apply_filters('mprm_south_african_states', $states);
	}

	/**
	 * Get Thailand States
	 *
	 * @since 1.6
	 * @return array $states A list of states
	 */
	public function get_thailand_states_list() {
		$states = array(
			'' => '',
			'TH-37' => 'Amnat Charoen (&#3629;&#3635;&#3609;&#3634;&#3592;&#3648;&#3592;&#3619;&#3636;&#3597;)',
			'TH-15' => 'Ang Thong (&#3629;&#3656;&#3634;&#3591;&#3607;&#3629;&#3591;)',
			'TH-14' => 'Ayutthaya (&#3614;&#3619;&#3632;&#3609;&#3588;&#3619;&#3624;&#3619;&#3637;&#3629;&#3618;&#3640;&#3608;&#3618;&#3634;)',
			'TH-10' => 'Bangkok (&#3585;&#3619;&#3640;&#3591;&#3648;&#3607;&#3614;&#3617;&#3627;&#3634;&#3609;&#3588;&#3619;)',
			'TH-38' => 'Bueng Kan (&#3610;&#3638;&#3591;&#3585;&#3634;&#3628;)',
			'TH-31' => 'Buri Ram (&#3610;&#3640;&#3619;&#3637;&#3619;&#3633;&#3617;&#3618;&#3660;)',
			'TH-24' => 'Chachoengsao (&#3593;&#3632;&#3648;&#3594;&#3636;&#3591;&#3648;&#3607;&#3619;&#3634;)',
			'TH-18' => 'Chai Nat (&#3594;&#3633;&#3618;&#3609;&#3634;&#3607;)',
			'TH-36' => 'Chaiyaphum (&#3594;&#3633;&#3618;&#3616;&#3641;&#3617;&#3636;)',
			'TH-22' => 'Chanthaburi (&#3592;&#3633;&#3609;&#3607;&#3610;&#3640;&#3619;&#3637;)',
			'TH-50' => 'Chiang Mai (&#3648;&#3594;&#3637;&#3618;&#3591;&#3651;&#3627;&#3617;&#3656;)',
			'TH-57' => 'Chiang Rai (&#3648;&#3594;&#3637;&#3618;&#3591;&#3619;&#3634;&#3618;)',
			'TH-20' => 'Chonburi (&#3594;&#3621;&#3610;&#3640;&#3619;&#3637;)',
			'TH-86' => 'Chumphon (&#3594;&#3640;&#3617;&#3614;&#3619;)',
			'TH-46' => 'Kalasin (&#3585;&#3634;&#3628;&#3626;&#3636;&#3609;&#3608;&#3640;&#3660;)',
			'TH-62' => 'Kamphaeng Phet (&#3585;&#3635;&#3649;&#3614;&#3591;&#3648;&#3614;&#3594;&#3619;)',
			'TH-71' => 'Kanchanaburi (&#3585;&#3634;&#3597;&#3592;&#3609;&#3610;&#3640;&#3619;&#3637;)',
			'TH-40' => 'Khon Kaen (&#3586;&#3629;&#3609;&#3649;&#3585;&#3656;&#3609;)',
			'TH-81' => 'Krabi (&#3585;&#3619;&#3632;&#3610;&#3637;&#3656;)',
			'TH-52' => 'Lampang (&#3621;&#3635;&#3611;&#3634;&#3591;)',
			'TH-51' => 'Lamphun (&#3621;&#3635;&#3614;&#3641;&#3609;)',
			'TH-42' => 'Loei (&#3648;&#3621;&#3618;)',
			'TH-16' => 'Lopburi (&#3621;&#3614;&#3610;&#3640;&#3619;&#3637;)',
			'TH-58' => 'Mae Hong Son (&#3649;&#3617;&#3656;&#3630;&#3656;&#3629;&#3591;&#3626;&#3629;&#3609;)',
			'TH-44' => 'Maha Sarakham (&#3617;&#3627;&#3634;&#3626;&#3634;&#3619;&#3588;&#3634;&#3617;)',
			'TH-49' => 'Mukdahan (&#3617;&#3640;&#3585;&#3604;&#3634;&#3627;&#3634;&#3619;)',
			'TH-26' => 'Nakhon Nayok (&#3609;&#3588;&#3619;&#3609;&#3634;&#3618;&#3585;)',
			'TH-73' => 'Nakhon Pathom (&#3609;&#3588;&#3619;&#3611;&#3600;&#3617;)',
			'TH-48' => 'Nakhon Phanom (&#3609;&#3588;&#3619;&#3614;&#3609;&#3617;)',
			'TH-30' => 'Nakhon Ratchasima (&#3609;&#3588;&#3619;&#3619;&#3634;&#3594;&#3626;&#3637;&#3617;&#3634;)',
			'TH-60' => 'Nakhon Sawan (&#3609;&#3588;&#3619;&#3626;&#3623;&#3619;&#3619;&#3588;&#3660;)',
			'TH-80' => 'Nakhon Si Thammarat (&#3609;&#3588;&#3619;&#3624;&#3619;&#3637;&#3608;&#3619;&#3619;&#3617;&#3619;&#3634;&#3594;)',
			'TH-55' => 'Nan (&#3609;&#3656;&#3634;&#3609;)',
			'TH-96' => 'Narathiwat (&#3609;&#3619;&#3634;&#3608;&#3636;&#3623;&#3634;&#3626;)',
			'TH-39' => 'Nong Bua Lam Phu (&#3627;&#3609;&#3629;&#3591;&#3610;&#3633;&#3623;&#3621;&#3635;&#3616;&#3641;)',
			'TH-43' => 'Nong Khai (&#3627;&#3609;&#3629;&#3591;&#3588;&#3634;&#3618;)',
			'TH-12' => 'Nonthaburi (&#3609;&#3609;&#3607;&#3610;&#3640;&#3619;&#3637;)',
			'TH-13' => 'Pathum Thani (&#3611;&#3607;&#3640;&#3617;&#3608;&#3634;&#3609;&#3637;)',
			'TH-94' => 'Pattani (&#3611;&#3633;&#3605;&#3605;&#3634;&#3609;&#3637;)',
			'TH-82' => 'Phang Nga (&#3614;&#3633;&#3591;&#3591;&#3634;)',
			'TH-93' => 'Phatthalung (&#3614;&#3633;&#3607;&#3621;&#3640;&#3591;)',
			'TH-56' => 'Phayao (&#3614;&#3632;&#3648;&#3618;&#3634;)',
			'TH-67' => 'Phetchabun (&#3648;&#3614;&#3594;&#3619;&#3610;&#3641;&#3619;&#3603;&#3660;)',
			'TH-76' => 'Phetchaburi (&#3648;&#3614;&#3594;&#3619;&#3610;&#3640;&#3619;&#3637;)',
			'TH-66' => 'Phichit (&#3614;&#3636;&#3592;&#3636;&#3605;&#3619;)',
			'TH-65' => 'Phitsanulok (&#3614;&#3636;&#3625;&#3603;&#3640;&#3650;&#3621;&#3585;)',
			'TH-54' => 'Phrae (&#3649;&#3614;&#3619;&#3656;)',
			'TH-83' => 'Phuket (&#3616;&#3641;&#3648;&#3585;&#3655;&#3605;)',
			'TH-25' => 'Prachin Buri (&#3611;&#3619;&#3634;&#3592;&#3637;&#3609;&#3610;&#3640;&#3619;&#3637;)',
			'TH-77' => 'Prachuap Khiri Khan (&#3611;&#3619;&#3632;&#3592;&#3623;&#3610;&#3588;&#3637;&#3619;&#3637;&#3586;&#3633;&#3609;&#3608;&#3660;)',
			'TH-85' => 'Ranong (&#3619;&#3632;&#3609;&#3629;&#3591;)',
			'TH-70' => 'Ratchaburi (&#3619;&#3634;&#3594;&#3610;&#3640;&#3619;&#3637;)',
			'TH-21' => 'Rayong (&#3619;&#3632;&#3618;&#3629;&#3591;)',
			'TH-45' => 'Roi Et (&#3619;&#3657;&#3629;&#3618;&#3648;&#3629;&#3655;&#3604;)',
			'TH-27' => 'Sa Kaeo (&#3626;&#3619;&#3632;&#3649;&#3585;&#3657;&#3623;)',
			'TH-47' => 'Sakon Nakhon (&#3626;&#3585;&#3621;&#3609;&#3588;&#3619;)',
			'TH-11' => 'Samut Prakan (&#3626;&#3617;&#3640;&#3607;&#3619;&#3611;&#3619;&#3634;&#3585;&#3634;&#3619;)',
			'TH-74' => 'Samut Sakhon (&#3626;&#3617;&#3640;&#3607;&#3619;&#3626;&#3634;&#3588;&#3619;)',
			'TH-75' => 'Samut Songkhram (&#3626;&#3617;&#3640;&#3607;&#3619;&#3626;&#3591;&#3588;&#3619;&#3634;&#3617;)',
			'TH-19' => 'Saraburi (&#3626;&#3619;&#3632;&#3610;&#3640;&#3619;&#3637;)',
			'TH-91' => 'Satun (&#3626;&#3605;&#3641;&#3621;)',
			'TH-17' => 'Sing Buri (&#3626;&#3636;&#3591;&#3627;&#3660;&#3610;&#3640;&#3619;&#3637;)',
			'TH-33' => 'Sisaket (&#3624;&#3619;&#3637;&#3626;&#3632;&#3648;&#3585;&#3625;)',
			'TH-90' => 'Songkhla (&#3626;&#3591;&#3586;&#3621;&#3634;)',
			'TH-64' => 'Sukhothai (&#3626;&#3640;&#3650;&#3586;&#3607;&#3633;&#3618;)',
			'TH-72' => 'Suphan Buri (&#3626;&#3640;&#3614;&#3619;&#3619;&#3603;&#3610;&#3640;&#3619;&#3637;)',
			'TH-84' => 'Surat Thani (&#3626;&#3640;&#3619;&#3634;&#3625;&#3598;&#3619;&#3660;&#3608;&#3634;&#3609;&#3637;)',
			'TH-32' => 'Surin (&#3626;&#3640;&#3619;&#3636;&#3609;&#3607;&#3619;&#3660;)',
			'TH-63' => 'Tak (&#3605;&#3634;&#3585;)',
			'TH-92' => 'Trang (&#3605;&#3619;&#3633;&#3591;)',
			'TH-23' => 'Trat (&#3605;&#3619;&#3634;&#3604;)',
			'TH-34' => 'Ubon Ratchathani (&#3629;&#3640;&#3610;&#3621;&#3619;&#3634;&#3594;&#3608;&#3634;&#3609;&#3637;)',
			'TH-41' => 'Udon Thani (&#3629;&#3640;&#3604;&#3619;&#3608;&#3634;&#3609;&#3637;)',
			'TH-61' => 'Uthai Thani (&#3629;&#3640;&#3607;&#3633;&#3618;&#3608;&#3634;&#3609;&#3637;)',
			'TH-53' => 'Uttaradit (&#3629;&#3640;&#3605;&#3619;&#3604;&#3636;&#3605;&#3606;&#3660;)',
			'TH-95' => 'Yala (&#3618;&#3632;&#3621;&#3634;)',
			'TH-35' => 'Yasothon (&#3618;&#3650;&#3626;&#3608;&#3619;)'
		);
		return apply_filters('mprm_thailand_states', $states);
	}

	/**
	 * Get Turkey States
	 *
	 * @since 2.2.3
	 * @return array $states A list of states
	 */
	public function get_turkey_states_list() {
		$states = array(
			'' => '',
			'TR01' => 'Adana',
			'TR02' => 'Ad&#305;yaman',
			'TR03' => 'Afyon',
			'TR04' => 'A&#287;r&#305;',
			'TR05' => 'Amasya',
			'TR06' => 'Ankara',
			'TR07' => 'Antalya',
			'TR08' => 'Artvin',
			'TR09' => 'Ayd&#305;n',
			'TR10' => 'Bal&#305;kesir',
			'TR11' => 'Bilecik',
			'TR12' => 'Bing&#246;l',
			'TR13' => 'Bitlis',
			'TR14' => 'Bolu',
			'TR15' => 'Burdur',
			'TR16' => 'Bursa',
			'TR17' => '&#199;anakkale',
			'TR18' => '&#199;ank&#305;kesir',
			'TR19' => '&#199;orum',
			'TR20' => 'Denizli',
			'TR21' => 'Diyarbak&#305;r',
			'TR22' => 'Edirne',
			'TR23' => 'Elaz&#305;&#287;',
			'TR24' => 'Erzincan',
			'TR25' => 'Erzurum',
			'TR26' => 'Eski&#351;ehir',
			'TR27' => 'Gaziantep',
			'TR28' => 'Giresun',
			'TR29' => 'G&#252;m&#252;&#351;hane',
			'TR30' => 'Hakkari',
			'TR31' => 'Hatay',
			'TR32' => 'Isparta',
			'TR33' => '&#304;&#231;el',
			'TR34' => '&#304;stanbul',
			'TR35' => '&#304;zmir',
			'TR36' => 'Kars',
			'TR37' => 'Kastamonu',
			'TR38' => 'Kayseri',
			'TR39' => 'K&#305;rklareli',
			'TR40' => 'K&#305;r&#351;ehir',
			'TR41' => 'Kocaeli',
			'TR42' => 'Konya',
			'TR43' => 'K&#252;tahya',
			'TR44' => 'Malatya',
			'TR45' => 'Manisa',
			'TR46' => 'Kahramanmara&#351;',
			'TR47' => 'Mardin',
			'TR48' => 'Mu&#287;la',
			'TR49' => 'Mu&#351;',
			'TR50' => 'Nev&#351;ehir',
			'TR51' => 'Ni&#287;de',
			'TR52' => 'Ordu',
			'TR53' => 'Rize',
			'TR54' => 'Sakarya',
			'TR55' => 'Samsun',
			'TR56' => 'Siirt',
			'TR57' => 'Sinop',
			'TR58' => 'Sivas',
			'TR59' => 'Tekirda&#287;',
			'TR60' => 'Tokat',
			'TR61' => 'Trabzon',
			'TR62' => 'Tunceli',
			'TR63' => '&#350;anl&#305;urfa',
			'TR64' => 'U&#351;ak',
			'TR65' => 'Van',
			'TR66' => 'Yozgat',
			'TR67' => 'Zonguldak',
			'TR68' => 'Aksaray',
			'TR69' => 'Bayburt',
			'TR70' => 'Karaman',
			'TR71' => 'K&#305;r&#305;kkale',
			'TR72' => 'Batman',
			'TR73' => '&#350;&#305;rnak',
			'TR74' => 'Bart&#305;n',
			'TR75' => 'Ardahan',
			'TR76' => 'I&#287;d&#305;r',
			'TR77' => 'Yalova',
			'TR78' => 'Karab&#252;k',
			'TR79' => 'Kilis',
			'TR80' => 'Osmaniye',
			'TR81' => 'D&#252;zce'
		);
		return apply_filters('mprm_turkey_states', $states);
	}

	/**
	 * Get Spain States
	 *
	 * @since 2.2
	 * @return array $states A list of states
	 */
	public function get_spain_states_list() {
		$states = array(
			'' => '',
			'C' => __('A Coru&ntilde;a', 'mp-restaurant-menu'),
			'VI' => __('Araba', 'mp-restaurant-menu'),
			'AB' => __('Albacete', 'mp-restaurant-menu'),
			'A' => __('Alicante', 'mp-restaurant-menu'),
			'AL' => __('Almer&iacute;a', 'mp-restaurant-menu'),
			'O' => __('Asturias', 'mp-restaurant-menu'),
			'AV' => __('&Aacute;vila', 'mp-restaurant-menu'),
			'BA' => __('Badajoz', 'mp-restaurant-menu'),
			'PM' => __('Baleares', 'mp-restaurant-menu'),
			'B' => __('Barcelona', 'mp-restaurant-menu'),
			'BU' => __('Burgos', 'mp-restaurant-menu'),
			'CC' => __('C&aacute;ceres', 'mp-restaurant-menu'),
			'CA' => __('C&aacute;diz', 'mp-restaurant-menu'),
			'S' => __('Cantabria', 'mp-restaurant-menu'),
			'CS' => __('Castell&oacute;n', 'mp-restaurant-menu'),
			'CE' => __('Ceuta', 'mp-restaurant-menu'),
			'CR' => __('Ciudad Real', 'mp-restaurant-menu'),
			'CO' => __('C&oacute;rdoba', 'mp-restaurant-menu'),
			'CU' => __('Cuenca', 'mp-restaurant-menu'),
			'GI' => __('Girona', 'mp-restaurant-menu'),
			'GR' => __('Granada', 'mp-restaurant-menu'),
			'GU' => __('Guadalajara', 'mp-restaurant-menu'),
			'SS' => __('Gipuzkoa', 'mp-restaurant-menu'),
			'H' => __('Huelva', 'mp-restaurant-menu'),
			'HU' => __('Huesca', 'mp-restaurant-menu'),
			'J' => __('Ja&eacute;n', 'mp-restaurant-menu'),
			'LO' => __('La Rioja', 'mp-restaurant-menu'),
			'GC' => __('Las Palmas', 'mp-restaurant-menu'),
			'LE' => __('Le&oacute;n', 'mp-restaurant-menu'),
			'L' => __('Lleida', 'mp-restaurant-menu'),
			'LU' => __('Lugo', 'mp-restaurant-menu'),
			'M' => __('Madrid', 'mp-restaurant-menu'),
			'MA' => __('M&aacute;laga', 'mp-restaurant-menu'),
			'ML' => __('Melilla', 'mp-restaurant-menu'),
			'MU' => __('Murcia', 'mp-restaurant-menu'),
			'NA' => __('Navarra', 'mp-restaurant-menu'),
			'OR' => __('Ourense', 'mp-restaurant-menu'),
			'P' => __('Palencia', 'mp-restaurant-menu'),
			'PO' => __('Pontevedra', 'mp-restaurant-menu'),
			'SA' => __('Salamanca', 'mp-restaurant-menu'),
			'TF' => __('Santa Cruz de Tenerife', 'mp-restaurant-menu'),
			'SG' => __('Segovia', 'mp-restaurant-menu'),
			'SE' => __('Sevilla', 'mp-restaurant-menu'),
			'SO' => __('Soria', 'mp-restaurant-menu'),
			'T' => __('Tarragona', 'mp-restaurant-menu'),
			'TE' => __('Teruel', 'mp-restaurant-menu'),
			'TO' => __('Toledo', 'mp-restaurant-menu'),
			'V' => __('Valencia', 'mp-restaurant-menu'),
			'VA' => __('Valladolid', 'mp-restaurant-menu'),
			'BI' => __('Bizkaia', 'mp-restaurant-menu'),
			'ZA' => __('Zamora', 'mp-restaurant-menu'),
			'Z' => __('Zaragoza', 'mp-restaurant-menu')
		);
		return apply_filters('mprm_spain_states', $states);
	}
}