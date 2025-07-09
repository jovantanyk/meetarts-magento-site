<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_TwoFactorAuth
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\TwoFactorAuth\Helper;

class Countries extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array
     */
    protected $countries;
    
    /**
     * Dependency injection
     *
     */
    public function __construct()
    {
        $this->countries = [];
    }
    /**
     * Return countries name and code
     *
     * @return mixed
     */
    public function getCountries()
    {
        $countries = [
            'countries' =>
            [
                0 =>
                [
                    'code' => '7 840',
                    'name' => 'Abkhazia',
                ],
                1 =>
                [
                    'code' => '93',
                    'name' => 'Afghanistan',
                ],
                2 =>
                [
                    'code' => '355',
                    'name' => 'Albania',
                ],
                3 =>
                [
                    'code' => '213',
                    'name' => 'Algeria',
                ],
                4 =>
                [
                    'code' => '1 684',
                    'name' => 'American Samoa',
                ],
                5 =>
                [
                    'code' => '376',
                    'name' => 'Andorra',
                ],
                6 =>
                [
                    'code' => '244',
                    'name' => 'Angola',
                ],
                7 =>
                [
                    'code' => '1 264',
                    'name' => 'Anguilla',
                ],
                8 =>
                [
                    'code' => '1 268',
                    'name' => 'Antigua and Barbuda',
                ],
                9 =>
                [
                    'code' => '54',
                    'name' => 'Argentina',
                ],
                10 =>
                [
                    'code' => '374',
                    'name' => 'Armenia',
                ],
                11 =>
                [
                    'code' => '297',
                    'name' => 'Aruba',
                ],
                12 =>
                [
                    'code' => '247',
                    'name' => 'Ascension',
                ],
                13 =>
                [
                    'code' => '61',
                    'name' => 'Australia',
                ],
                14 =>
                [
                    'code' => '672',
                    'name' => 'Australian External Territories',
                ],
                15 =>
                [
                    'code' => '43',
                    'name' => 'Austria',
                ],
                16 =>
                [
                    'code' => '994',
                    'name' => 'Azerbaijan',
                ],
                17 =>
                [
                    'code' => '1 242',
                    'name' => 'Bahamas',
                ],
                18 =>
                [
                    'code' => '973',
                    'name' => 'Bahrain',
                ],
                19 =>
                [
                    'code' => '880',
                    'name' => 'Bangladesh',
                ],
                20 =>
                [
                    'code' => '1 246',
                    'name' => 'Barbados',
                ],
                21 =>
                [
                    'code' => '1 268',
                    'name' => 'Barbuda',
                ],
                22 =>
                [
                    'code' => '375',
                    'name' => 'Belarus',
                ],
                23 =>
                [
                    'code' => '32',
                    'name' => 'Belgium',
                ],
                24 =>
                [
                    'code' => '501',
                    'name' => 'Belize',
                ],
                25 =>
                [
                    'code' => '229',
                    'name' => 'Benin',
                ],
                26 =>
                [
                    'code' => '1 441',
                    'name' => 'Bermuda',
                ],
                27 =>
                [
                    'code' => '975',
                    'name' => 'Bhutan',
                ],
                28 =>
                [
                    'code' => '591',
                    'name' => 'Bolivia',
                ],
                29 =>
                [
                    'code' => '387',
                    'name' => 'Bosnia and Herzegovina',
                ],
                30 =>
                [
                    'code' => '267',
                    'name' => 'Botswana',
                ],
                31 =>
                [
                    'code' => '55',
                    'name' => 'Brazil',
                ],
                32 =>
                [
                    'code' => '246',
                    'name' => 'British Indian Ocean Territory',
                ],
                33 =>
                [
                    'code' => '1 284',
                    'name' => 'British Virgin Islands',
                ],
                34 =>
                [
                    'code' => '673',
                    'name' => 'Brunei',
                ],
                35 =>
                [
                    'code' => '359',
                    'name' => 'Bulgaria',
                ],
                36 =>
                [
                    'code' => '226',
                    'name' => 'Burkina Faso',
                ],
                37 =>
                [
                    'code' => '257',
                    'name' => 'Burundi',
                ],
                38 =>
                [
                    'code' => '855',
                    'name' => 'Cambodia',
                ],
                39 =>
                [
                    'code' => '237',
                    'name' => 'Cameroon',
                ],
                40 =>
                [
                    'code' => '1',
                    'name' => 'Canada',
                ],
                41 =>
                [
                    'code' => '238',
                    'name' => 'Cape Verde',
                ],
                42 =>
                [
                    'code' => ' 345',
                    'name' => 'Cayman Islands',
                ],
                43 =>
                [
                    'code' => '236',
                    'name' => 'Central African Republic',
                ],
                44 =>
                [
                    'code' => '235',
                    'name' => 'Chad',
                ],
                45 =>
                [
                    'code' => '56',
                    'name' => 'Chile',
                ],
                46 =>
                [
                    'code' => '86',
                    'name' => 'China',
                ],
                47 =>
                [
                    'code' => '61',
                    'name' => 'Christmas Island',
                ],
                48 =>
                [
                    'code' => '61',
                    'name' => 'Cocos-Keeling Islands',
                ],
                49 =>
                [
                    'code' => '57',
                    'name' => 'Colombia',
                ],
                50 =>
                [
                    'code' => '269',
                    'name' => 'Comoros',
                ],
                51 =>
                [
                    'code' => '242',
                    'name' => 'Congo',
                ],
                52 =>
                [
                    'code' => '243',
                    'name' => 'Congo, Dem. Rep. of (Zaire)',
                ],
                53 =>
                [
                    'code' => '682',
                    'name' => 'Cook Islands',
                ],
                54 =>
                [
                    'code' => '506',
                    'name' => 'Costa Rica',
                ],
                55 =>
                [
                    'code' => '385',
                    'name' => 'Croatia',
                ],
                56 =>
                [
                    'code' => '53',
                    'name' => 'Cuba',
                ],
                57 =>
                [
                    'code' => '599',
                    'name' => 'Curacao',
                ],
                58 =>
                [
                    'code' => '537',
                    'name' => 'Cyprus',
                ],
                59 =>
                [
                    'code' => '420',
                    'name' => 'Czech Republic',
                ],
                60 =>
                [
                    'code' => '45',
                    'name' => 'Denmark',
                ],
                61 =>
                [
                    'code' => '246',
                    'name' => 'Diego Garcia',
                ],
                62 =>
                [
                    'code' => '253',
                    'name' => 'Djibouti',
                ],
                63 =>
                [
                    'code' => '1 767',
                    'name' => 'Dominica',
                ],
                64 =>
                [
                    'code' => '1 809',
                    'name' => 'Dominican Republic',
                ],
                65 =>
                [
                    'code' => '670',
                    'name' => 'East Timor',
                ],
                66 =>
                [
                    'code' => '56',
                    'name' => 'Easter Island',
                ],
                67 =>
                [
                    'code' => '593',
                    'name' => 'Ecuador',
                ],
                68 =>
                [
                    'code' => '20',
                    'name' => 'Egypt',
                ],
                69 =>
                [
                    'code' => '503',
                    'name' => 'El Salvador',
                ],
                70 =>
                [
                    'code' => '240',
                    'name' => 'Equatorial Guinea',
                ],
                71 =>
                [
                    'code' => '291',
                    'name' => 'Eritrea',
                ],
                72 =>
                [
                    'code' => '372',
                    'name' => 'Estonia',
                ],
                73 =>
                [
                    'code' => '251',
                    'name' => 'Ethiopia',
                ],
                74 =>
                [
                    'code' => '500',
                    'name' => 'Falkland Islands',
                ],
                75 =>
                [
                    'code' => '298',
                    'name' => 'Faroe Islands',
                ],
                76 =>
                [
                    'code' => '679',
                    'name' => 'Fiji',
                ],
                77 =>
                [
                    'code' => '358',
                    'name' => 'Finland',
                ],
                78 =>
                [
                    'code' => '33',
                    'name' => 'France',
                ],
                79 =>
                [
                    'code' => '596',
                    'name' => 'French Antilles',
                ],
                80 =>
                [
                    'code' => '594',
                    'name' => 'French Guiana',
                ],
                81 =>
                [
                    'code' => '689',
                    'name' => 'French Polynesia',
                ],
                82 =>
                [
                    'code' => '241',
                    'name' => 'Gabon',
                ],
                83 =>
                [
                    'code' => '220',
                    'name' => 'Gambia',
                ],
                84 =>
                [
                    'code' => '995',
                    'name' => 'Georgia',
                ],
                85 =>
                [
                    'code' => '49',
                    'name' => 'Germany',
                ],
                86 =>
                [
                    'code' => '233',
                    'name' => 'Ghana',
                ],
                87 =>
                [
                    'code' => '350',
                    'name' => 'Gibraltar',
                ],
                88 =>
                [
                    'code' => '30',
                    'name' => 'Greece',
                ],
                89 =>
                [
                    'code' => '299',
                    'name' => 'Greenland',
                ],
                90 =>
                [
                    'code' => '1 473',
                    'name' => 'Grenada',
                ],
                91 =>
                [
                    'code' => '590',
                    'name' => 'Guadeloupe',
                ],
                92 =>
                [
                    'code' => '1 671',
                    'name' => 'Guam',
                ],
                93 =>
                [
                    'code' => '502',
                    'name' => 'Guatemala',
                ],
                94 =>
                [
                    'code' => '224',
                    'name' => 'Guinea',
                ],
                95 =>
                [
                    'code' => '245',
                    'name' => 'Guinea-Bissau',
                ],
                96 =>
                [
                    'code' => '595',
                    'name' => 'Guyana',
                ],
                97 =>
                [
                    'code' => '509',
                    'name' => 'Haiti',
                ],
                98 =>
                [
                    'code' => '504',
                    'name' => 'Honduras',
                ],
                99 =>
                [
                    'code' => '852',
                    'name' => 'Hong Kong SAR China',
                ],
                100 =>
                [
                    'code' => '36',
                    'name' => 'Hungary',
                ],
                101 =>
                [
                    'code' => '354',
                    'name' => 'Iceland',
                ],
                102 =>
                [
                    'code' => '91',
                    'name' => 'India',
                ],
                103 =>
                [
                    'code' => '62',
                    'name' => 'Indonesia',
                ],
                104 =>
                [
                    'code' => '98',
                    'name' => 'Iran',
                ],
                105 =>
                [
                    'code' => '964',
                    'name' => 'Iraq',
                ],
                106 =>
                [
                    'code' => '353',
                    'name' => 'Ireland',
                ],
                107 =>
                [
                    'code' => '972',
                    'name' => 'Israel',
                ],
                108 =>
                [
                    'code' => '39',
                    'name' => 'Italy',
                ],
                109 =>
                [
                    'code' => '225',
                    'name' => 'Ivory Coast',
                ],
                110 =>
                [
                    'code' => '1 876',
                    'name' => 'Jamaica',
                ],
                111 =>
                [
                    'code' => '81',
                    'name' => 'Japan',
                ],
                112 =>
                [
                    'code' => '962',
                    'name' => 'Jordan',
                ],
                113 =>
                [
                    'code' => '7 7',
                    'name' => 'Kazakhstan',
                ],
                114 =>
                [
                    'code' => '254',
                    'name' => 'Kenya',
                ],
                115 =>
                [
                    'code' => '686',
                    'name' => 'Kiribati',
                ],
                116 =>
                [
                    'code' => '965',
                    'name' => 'Kuwait',
                ],
                117 =>
                [
                    'code' => '996',
                    'name' => 'Kyrgyzstan',
                ],
                118 =>
                [
                    'code' => '856',
                    'name' => 'Laos',
                ],
                119 =>
                [
                    'code' => '371',
                    'name' => 'Latvia',
                ],
                120 =>
                [
                    'code' => '961',
                    'name' => 'Lebanon',
                ],
                121 =>
                [
                    'code' => '266',
                    'name' => 'Lesotho',
                ],
                122 =>
                [
                    'code' => '231',
                    'name' => 'Liberia',
                ],
                123 =>
                [
                    'code' => '218',
                    'name' => 'Libya',
                ],
                124 =>
                [
                    'code' => '423',
                    'name' => 'Liechtenstein',
                ],
                125 =>
                [
                    'code' => '370',
                    'name' => 'Lithuania',
                ],
                126 =>
                [
                    'code' => '352',
                    'name' => 'Luxembourg',
                ],
                127 =>
                [
                    'code' => '853',
                    'name' => 'Macau SAR China',
                ],
                128 =>
                [
                    'code' => '389',
                    'name' => 'Macedonia',
                ],
                129 =>
                [
                    'code' => '261',
                    'name' => 'Madagascar',
                ],
                130 =>
                [
                    'code' => '265',
                    'name' => 'Malawi',
                ],
                131 =>
                [
                    'code' => '60',
                    'name' => 'Malaysia',
                ],
                132 =>
                [
                    'code' => '960',
                    'name' => 'Maldives',
                ],
                133 =>
                [
                    'code' => '223',
                    'name' => 'Mali',
                ],
                134 =>
                [
                    'code' => '356',
                    'name' => 'Malta',
                ],
                135 =>
                [
                    'code' => '692',
                    'name' => 'Marshall Islands',
                ],
                136 =>
                [
                    'code' => '596',
                    'name' => 'Martinique',
                ],
                137 =>
                [
                    'code' => '222',
                    'name' => 'Mauritania',
                ],
                138 =>
                [
                    'code' => '230',
                    'name' => 'Mauritius',
                ],
                139 =>
                [
                    'code' => '262',
                    'name' => 'Mayotte',
                ],
                140 =>
                [
                    'code' => '52',
                    'name' => 'Mexico',
                ],
                141 =>
                [
                    'code' => '691',
                    'name' => 'Micronesia',
                ],
                142 =>
                [
                    'code' => '1 808',
                    'name' => 'Midway Island',
                ],
                143 =>
                [
                    'code' => '373',
                    'name' => 'Moldova',
                ],
                144 =>
                [
                    'code' => '377',
                    'name' => 'Monaco',
                ],
                145 =>
                [
                    'code' => '976',
                    'name' => 'Mongolia',
                ],
                146 =>
                [
                    'code' => '382',
                    'name' => 'Montenegro',
                ],
                147 =>
                [
                    'code' => '1664',
                    'name' => 'Montserrat',
                ],
                148 =>
                [
                    'code' => '212',
                    'name' => 'Morocco',
                ],
                149 =>
                [
                    'code' => '95',
                    'name' => 'Myanmar',
                ],
                150 =>
                [
                    'code' => '264',
                    'name' => 'Namibia',
                ],
                151 =>
                [
                    'code' => '674',
                    'name' => 'Nauru',
                ],
                152 =>
                [
                    'code' => '977',
                    'name' => 'Nepal',
                ],
                153 =>
                [
                    'code' => '31',
                    'name' => 'Netherlands',
                ],
                154 =>
                [
                    'code' => '599',
                    'name' => 'Netherlands Antilles',
                ],
                155 =>
                [
                    'code' => '1 869',
                    'name' => 'Nevis',
                ],
                156 =>
                [
                    'code' => '687',
                    'name' => 'New Caledonia',
                ],
                157 =>
                [
                    'code' => '64',
                    'name' => 'New Zealand',
                ],
                158 =>
                [
                    'code' => '505',
                    'name' => 'Nicaragua',
                ],
                159 =>
                [
                    'code' => '227',
                    'name' => 'Niger',
                ],
                160 =>
                [
                    'code' => '234',
                    'name' => 'Nigeria',
                ],
                161 =>
                [
                    'code' => '683',
                    'name' => 'Niue',
                ],
                162 =>
                [
                    'code' => '672',
                    'name' => 'Norfolk Island',
                ],
                163 =>
                [
                    'code' => '850',
                    'name' => 'North Korea',
                ],
                164 =>
                [
                    'code' => '1 670',
                    'name' => 'Northern Mariana Islands',
                ],
                165 =>
                [
                    'code' => '47',
                    'name' => 'Norway',
                ],
                166 =>
                [
                    'code' => '968',
                    'name' => 'Oman',
                ],
                167 =>
                [
                    'code' => '92',
                    'name' => 'Pakistan',
                ],
                168 =>
                [
                    'code' => '680',
                    'name' => 'Palau',
                ],
                169 =>
                [
                    'code' => '970',
                    'name' => 'Palestinian Territory',
                ],
                170 =>
                [
                    'code' => '507',
                    'name' => 'Panama',
                ],
                171 =>
                [
                    'code' => '675',
                    'name' => 'Papua New Guinea',
                ],
                172 =>
                [
                    'code' => '595',
                    'name' => 'Paraguay',
                ],
                173 =>
                [
                    'code' => '51',
                    'name' => 'Peru',
                ],
                174 =>
                [
                    'code' => '63',
                    'name' => 'Philippines',
                ],
                175 =>
                [
                    'code' => '48',
                    'name' => 'Poland',
                ],
                176 =>
                [
                    'code' => '351',
                    'name' => 'Portugal',
                ],
                177 =>
                [
                    'code' => '1 787',
                    'name' => 'Puerto Rico',
                ],
                178 =>
                [
                    'code' => '974',
                    'name' => 'Qatar',
                ],
                179 =>
                [
                    'code' => '262',
                    'name' => 'Reunion',
                ],
                180 =>
                [
                    'code' => '40',
                    'name' => 'Romania',
                ],
                181 =>
                [
                    'code' => '7',
                    'name' => 'Russia',
                ],
                182 =>
                [
                    'code' => '250',
                    'name' => 'Rwanda',
                ],
                183 =>
                [
                    'code' => '685',
                    'name' => 'Samoa',
                ],
                184 =>
                [
                    'code' => '378',
                    'name' => 'San Marino',
                ],
                185 =>
                [
                    'code' => '966',
                    'name' => 'Saudi Arabia',
                ],
                186 =>
                [
                    'code' => '221',
                    'name' => 'Senegal',
                ],
                187 =>
                [
                    'code' => '381',
                    'name' => 'Serbia',
                ],
                188 =>
                [
                    'code' => '248',
                    'name' => 'Seychelles',
                ],
                189 =>
                [
                    'code' => '232',
                    'name' => 'Sierra Leone',
                ],
                190 =>
                [
                    'code' => '65',
                    'name' => 'Singapore',
                ],
                191 =>
                [
                    'code' => '421',
                    'name' => 'Slovakia',
                ],
                192 =>
                [
                    'code' => '386',
                    'name' => 'Slovenia',
                ],
                193 =>
                [
                    'code' => '677',
                    'name' => 'Solomon Islands',
                ],
                194 =>
                [
                    'code' => '27',
                    'name' => 'South Africa',
                ],
                195 =>
                [
                    'code' => '500',
                    'name' => 'South Georgia and the South Sandwich Islands',
                ],
                196 =>
                [
                    'code' => '82',
                    'name' => 'South Korea',
                ],
                197 =>
                [
                    'code' => '34',
                    'name' => 'Spain',
                ],
                198 =>
                [
                    'code' => '94',
                    'name' => 'Sri Lanka',
                ],
                199 =>
                [
                    'code' => '249',
                    'name' => 'Sudan',
                ],
                200 =>
                [
                    'code' => '597',
                    'name' => 'Suriname',
                ],
                201 =>
                [
                    'code' => '268',
                    'name' => 'Swaziland',
                ],
                202 =>
                [
                    'code' => '46',
                    'name' => 'Sweden',
                ],
                203 =>
                [
                    'code' => '41',
                    'name' => 'Switzerland',
                ],
                204 =>
                [
                    'code' => '963',
                    'name' => 'Syria',
                ],
                205 =>
                [
                    'code' => '886',
                    'name' => 'Taiwan',
                ],
                206 =>
                [
                    'code' => '992',
                    'name' => 'Tajikistan',
                ],
                207 =>
                [
                    'code' => '255',
                    'name' => 'Tanzania',
                ],
                208 =>
                [
                    'code' => '66',
                    'name' => 'Thailand',
                ],
                209 =>
                [
                    'code' => '670',
                    'name' => 'Timor Leste',
                ],
                210 =>
                [
                    'code' => '228',
                    'name' => 'Togo',
                ],
                211 =>
                [
                    'code' => '690',
                    'name' => 'Tokelau',
                ],
                212 =>
                [
                    'code' => '676',
                    'name' => 'Tonga',
                ],
                213 =>
                [
                    'code' => '1 868',
                    'name' => 'Trinidad and Tobago',
                ],
                214 =>
                [
                    'code' => '216',
                    'name' => 'Tunisia',
                ],
                215 =>
                [
                    'code' => '90',
                    'name' => 'Turkey',
                ],
                216 =>
                [
                    'code' => '993',
                    'name' => 'Turkmenistan',
                ],
                217 =>
                [
                    'code' => '1 649',
                    'name' => 'Turks and Caicos Islands',
                ],
                218 =>
                [
                    'code' => '688',
                    'name' => 'Tuvalu',
                ],
                219 =>
                [
                    'code' => '1 340',
                    'name' => 'U.S. Virgin Islands',
                ],
                220 =>
                [
                    'code' => '256',
                    'name' => 'Uganda',
                ],
                221 =>
                [
                    'code' => '380',
                    'name' => 'Ukraine',
                ],
                222 =>
                [
                    'code' => '971',
                    'name' => 'United Arab Emirates',
                ],
                223 =>
                [
                    'code' => '44',
                    'name' => 'United Kingdom',
                ],
                224 =>
                [
                    'code' => '1',
                    'name' => 'United States',
                ],
                225 =>
                [
                    'code' => '598',
                    'name' => 'Uruguay',
                ],
                226 =>
                [
                    'code' => '998',
                    'name' => 'Uzbekistan',
                ],
                227 =>
                [
                    'code' => '678',
                    'name' => 'Vanuatu',
                ],
                228 =>
                [
                    'code' => '58',
                    'name' => 'Venezuela',
                ],
                229 =>
                [
                    'code' => '84',
                    'name' => 'Vietnam',
                ],
                230 =>
                [
                    'code' => '1 808',
                    'name' => 'Wake Island',
                ],
                231 =>
                [
                    'code' => '681',
                    'name' => 'Wallis and Futuna',
                ],
                232 =>
                [
                    'code' => '967',
                    'name' => 'Yemen',
                ],
                233 =>
                [
                    'code' => '260',
                    'name' => 'Zambia',
                ],
                234 =>
                [
                    'code' => '255',
                    'name' => 'Zanzibar',
                ],
                235 =>
                [
                    'code' => '263',
                    'name' => 'Zimbabwe',
                ],
            ],
        ];
        return $countries;
    }
    /**
     * Return calling code
     *
     * @param string $code
     * @return string
     */
    public function getCallingCode($code)
    {
        $countrycode = [
            'AD'=>'376',
            'AE'=>'971',
            'AF'=>'93',
            'AG'=>'1268',
            'AI'=>'1264',
            'AL'=>'355',
            'AM'=>'374',
            'AN'=>'599',
            'AO'=>'244',
            'AQ'=>'672',
            'AR'=>'54',
            'AS'=>'1684',
            'AT'=>'43',
            'AU'=>'61',
            'AW'=>'297',
            'AZ'=>'994',
            'BA'=>'387',
            'BB'=>'1246',
            'BD'=>'880',
            'BE'=>'32',
            'BF'=>'226',
            'BG'=>'359',
            'BH'=>'973',
            'BI'=>'257',
            'BJ'=>'229',
            'BL'=>'590',
            'BM'=>'1441',
            'BN'=>'673',
            'BO'=>'591',
            'BR'=>'55',
            'BS'=>'1242',
            'BT'=>'975',
            'BW'=>'267',
            'BY'=>'375',
            'BZ'=>'501',
            'CA'=>'1',
            'CC'=>'61',
            'CD'=>'243',
            'CF'=>'236',
            'CG'=>'242',
            'CH'=>'41',
            'CI'=>'225',
            'CK'=>'682',
            'CL'=>'56',
            'CM'=>'237',
            'CN'=>'86',
            'CO'=>'57',
            'CR'=>'506',
            'CU'=>'53',
            'CV'=>'238',
            'CX'=>'61',
            'CY'=>'357',
            'CZ'=>'420',
            'DE'=>'49',
            'DJ'=>'253',
            'DK'=>'45',
            'DM'=>'1767',
            'DO'=>'1809',
            'DZ'=>'213',
            'EC'=>'593',
            'EE'=>'372',
            'EG'=>'20',
            'ER'=>'291',
            'ES'=>'34',
            'ET'=>'251',
            'FI'=>'358',
            'FJ'=>'679',
            'FK'=>'500',
            'FM'=>'691',
            'FO'=>'298',
            'FR'=>'33',
            'GA'=>'241',
            'GB'=>'44',
            'GD'=>'1473',
            'GE'=>'995',
            'GH'=>'233',
            'GI'=>'350',
            'GL'=>'299',
            'GM'=>'220',
            'GN'=>'224',
            'GQ'=>'240',
            'GR'=>'30',
            'GT'=>'502',
            'GU'=>'1671',
            'GW'=>'245',
            'GY'=>'592',
            'HK'=>'852',
            'HN'=>'504',
            'HR'=>'385',
            'HT'=>'509',
            'HU'=>'36',
            'ID'=>'62',
            'IE'=>'353',
            'IL'=>'972',
            'IM'=>'44',
            'IN'=>'91',
            'IQ'=>'964',
            'IR'=>'98',
            'IS'=>'354',
            'IT'=>'39',
            'JM'=>'1876',
            'JO'=>'962',
            'JP'=>'81',
            'KE'=>'254',
            'KG'=>'996',
            'KH'=>'855',
            'KI'=>'686',
            'KM'=>'269',
            'KN'=>'1869',
            'KP'=>'850',
            'KR'=>'82',
            'KW'=>'965',
            'KY'=>'1345',
            'KZ'=>'7',
            'LA'=>'856',
            'LB'=>'961',
            'LC'=>'1758',
            'LI'=>'423',
            'LK'=>'94',
            'LR'=>'231',
            'LS'=>'266',
            'LT'=>'370',
            'LU'=>'352',
            'LV'=>'371',
            'LY'=>'218',
            'MA'=>'212',
            'MC'=>'377',
            'MD'=>'373',
            'ME'=>'382',
            'MF'=>'1599',
            'MG'=>'261',
            'MH'=>'692',
            'MK'=>'389',
            'ML'=>'223',
            'MM'=>'95',
            'MN'=>'976',
            'MO'=>'853',
            'MP'=>'1670',
            'MR'=>'222',
            'MS'=>'1664',
            'MT'=>'356',
            'MU'=>'230',
            'MV'=>'960',
            'MW'=>'265',
            'MX'=>'52',
            'MY'=>'60',
            'MZ'=>'258',
            'NA'=>'264',
            'NC'=>'687',
            'NE'=>'227',
            'NG'=>'234',
            'NI'=>'505',
            'NL'=>'31',
            'NO'=>'47',
            'NP'=>'977',
            'NR'=>'674',
            'NU'=>'683',
            'NZ'=>'64',
            'OM'=>'968',
            'PA'=>'507',
            'PE'=>'51',
            'PF'=>'689',
            'PG'=>'675',
            'PH'=>'63',
            'PK'=>'92',
            'PL'=>'48',
            'PM'=>'508',
            'PN'=>'870',
            'PR'=>'1',
            'PT'=>'351',
            'PW'=>'680',
            'PY'=>'595',
            'QA'=>'974',
            'RO'=>'40',
            'RS'=>'381',
            'RU'=>'7',
            'RW'=>'250',
            'SA'=>'966',
            'SB'=>'677',
            'SC'=>'248',
            'SD'=>'249',
            'SE'=>'46',
            'SG'=>'65',
            'SH'=>'290',
            'SI'=>'386',
            'SK'=>'421',
            'SL'=>'232',
            'SM'=>'378',
            'SN'=>'221',
            'SO'=>'252',
            'SR'=>'597',
            'ST'=>'239',
            'SV'=>'503',
            'SY'=>'963',
            'SZ'=>'268',
            'TC'=>'1649',
            'TD'=>'235',
            'TG'=>'228',
            'TH'=>'66',
            'TJ'=>'992',
            'TK'=>'690',
            'TL'=>'670',
            'TM'=>'993',
            'TN'=>'216',
            'TO'=>'676',
            'TR'=>'90',
            'TT'=>'1868',
            'TV'=>'688',
            'TW'=>'886',
            'TZ'=>'255',
            'UA'=>'380',
            'UG'=>'256',
            'US'=>'1',
            'UY'=>'598',
            'UZ'=>'998',
            'VA'=>'39',
            'VC'=>'1784',
            'VE'=>'58',
            'VG'=>'1284',
            'VI'=>'1340',
            'VN'=>'84',
            'VU'=>'678',
            'WF'=>'681',
            'WS'=>'685',
            'XK'=>'381',
            'YE'=>'967',
            'YT'=>'262',
            'ZA'=>'27',
            'ZM'=>'260',
            'ZW'=>'263'
        ];
        $key = isset($countrycode[$code])?$countrycode[$code]:"";
        return $key;
    }
}
