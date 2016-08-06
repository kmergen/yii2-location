<?php
/**
 * Geocoding helper class.
 */

namespace kmergen\location\helpers;

use Yii;

class Geo
{

    //const $RADIUS_SEMI_MAJOR = 6378137.0;
    const RADIUS_SEMI_MAJOR = 6378137.0;

    protected static function getFlattening()
    {
        return 1 / 298.257223563;
    }

    protected static function radiusSemiminor()
    {
        return (self::RADIUS_SEMI_MAJOR * (1 - self::getFlattening()));
    }

    /**
     * @todo This function uses earth_asin_safe so is not accurate for all possible
     *   parameter combinations. This means this function doesn't work properly
     *   for high distance values. This function needs to be re-written to work properly for
     *   larger distance values. See http://drupal.org/node/821628
     */
    public static function longitudeRange($latitude, $longitude, $distance)
    {
        // Estimate the min and max longitudes within $distance of a given location.
        $long = deg2rad($longitude);
        $lat = deg2rad($latitude);
        $radius = self::earthRadius($latitude);

        $angle = $distance / $radius;
        $diff = self::asinSafe(sin($angle) / cos($lat));
        $minlong = $long - $diff;
        $maxlong = $long + $diff;
        if ($minlong < -pi()) {
            $minlong = $minlong + pi() * 2;
        }
        if ($maxlong > pi()) {
            $maxlong = $maxlong - pi() * 2;
        }
        return array(rad2deg($minlong), rad2deg($maxlong));
    }

    public static function latitudeRange($latitude, $longitude, $distance)
    {
        // Estimate the min and max latitudes within $distance of a given location.
        $long = deg2rad($longitude);
        $lat = deg2rad($latitude);
        $radius = self::earthRadius($latitude);

        $angle = $distance / $radius;
        $minlat = $lat - $angle;
        $maxlat = $lat + $angle;
        $rightangle = pi() / 2;
        if ($minlat < -$rightangle) { // wrapped around the south pole
            $overshoot = -$minlat - $rightangle;
            $minlat = -$rightangle + $overshoot;
            if ($minlat > $maxlat) {
                $maxlat = $minlat;
            }
            $minlat = -$rightangle;
        }
        if ($maxlat > $rightangle) { // wrapped around the north pole
            $overshoot = $maxlat - $rightangle;
            $maxlat = $rightangle - $overshoot;
            if ($maxlat < $minlat) {
                $minlat = $maxlat;
            }
            $maxlat = $rightangle;
        }
        return array(rad2deg($minlat), rad2deg($maxlat));
    }

    // Latitudes in all of U. S.: from -7.2 (American Samoa) to 70.5 (Alaska).
    // Latitudes in continental U. S.: from 24.6 (Florida) to 49.0 (Washington).
    // Average latitude of all U. S. zipcodes: 37.9.

    public static function earthRadius($latitude = 37.9)
    {
        //global $earth_radius_semimajor, $earth_radius_semiminor;
        // Estimate the Earth's radius at a given latitude.
        // Default to an approximate average radius for the United States.

        $lat = deg2rad($latitude);

        $x = cos($lat) / self::RADIUS_SEMI_MAJOR;
        $y = sin($lat) / self::radiusSemiminor();
        return 1 / (sqrt($x * $x + $y * $y));
    }

    /**
     * This is a helper function to avoid errors when using the asin() PHP function.
     * asin is only real for values between -1 and 1.
     * If a value outside that range is given it returns NAN (not a number), which
     * we don't want to happen.
     * So this just rounds values outside this range to -1 or 1 first.
     *
     * This means that calculations done using this function with $x outside the range
     * will not be accurate.  The alternative though is getting NAN, which is an error
     * and won't give accurate results anyway.
     */
    public static function asinSafe($x)
    {
        return asin(max(-1, min($x, 1)));
    }

    /**
     * Return the distance in meter between to locations
     */
    public static function distance($longitude1, $latitude1, $longitude2, $latitude2)
    {
        // Estimate the earth-surface distance between two locations.
        $lon1 = deg2rad($longitude1);
        $lat1 = deg2rad($latitude1);
        $lon2 = deg2rad($longitude2);
        $lat2 = deg2rad($latitude2);
        $radius = self::earthRadius(($latitude1 + $latitude2) / 2);

        $cosangle = cos($lat1) * cos($lat2) *
            (cos($lon1) * cos($lon2) + sin($lon1) * sin($lon2)) +
            sin($lat1) * sin($lat2);
        return acos($cosangle) * $radius;
    }
    
    /**
     * Return the coordinates longitude and latitude from a given zip(Postcode)
     * @param mixed (string,int) zip A postcode
     */
    public static function latlon($zip, $country = '')
    {
        $sql = "SELECT latitude,longitude FROM zipcode WHERE zip=$zip";
        $row = \Yii::$app->db->createCommand($sql)->queryOne();

        if (!empty($row)) {
            if ($row['latitude'] > 0 && $row['longitude'] > 0) {
                return ['lat' => $row['latitude'], 'lon' => $row['longitude']];
            }
        }
        return false;
    }

    /**
     * This function return the min latlon range and max latlon range for a given distance
     * @param array $latlon normaly the return value from function latlon
     * @param int the distance in kilometer or meter
     */
    public static function latlonRange($latlon, $distance, $unit = 'km')
    {
        if ($unit == 'km') {
            $distance*=1000;
        }

        $latRange = self::latitudeRange($latlon['lat'], $latlon['lon'], $distance);
        $lonRange = self::longitudeRange($latlon['lat'], $latlon['lon'], $distance);

        return ['lat_min' => $latRange[0], 'lat_max' => $latRange[1], 'lon_min' => $lonRange[0], 'lon_max' => $lonRange[1]];
    }

    /**
     * This function return the distance between to locations
     * @param array $latlon_a the first location
     * @param array $latlon_b the second location
     * @param string unit
     */
    public static function distanceBetween($latlon_a = [], $latlon_b = [], $unit = 'km')
    {

        if (!isset($latlon_a['lon']) || !isset($latlon_a['lat']) || !isset($latlon_b['lon']) || !isset($latlon_b['lat'])) {
            return false;
        }

        if ($unit != 'km' && $unit != 'mile') {
            return false;
        }

        // $conversion_factor = number to divide by to convert meters to $distance_unit
        // At this point, $distance_unit == 'km' or 'mile' and nothing else
        //$conversion_factor = ($distance_unit == 'km') ? 1000.0 : 1609.347;
        $meters = self::distance($latlon_a['lon'], $latlon_a['lat'], $latlon_b['lon'], $latlon_b['lat']);
        return ['scalar' => round($meters / (($unit == 'km') ? 1000.0 : 1609.347), 1), 'unit' => $unit];
    }

 
    /**
     * Return a list of countries in Iso format e.g DE=>Deutschland
     * @return  array of countries
     */
    public static function IsoCountryList()
    {
        $countries = array(
            'AD' => 'Andorra',
            'AE' => 'United Arab Emirates',
            'AF' => 'Afghanistan',
            'AG' => 'Antigua and Barbuda',
            'AI' => 'Anguilla',
            'AL' => 'Albania',
            'AM' => 'Armenia',
            'AN' => 'Netherlands Antilles',
            'AO' => 'Angola',
            'AQ' => 'Antarctica',
            'AR' => 'Argentina',
            'AS' => 'American Samoa',
            'AT' => Yii::t('app/iso', 'Austria'),
            'AU' => 'Australia',
            'AW' => 'Aruba',
            'AX' => 'Aland Islands',
            'AZ' => 'Azerbaijan',
            'BA' => 'Bosnia and Herzegovina',
            'BB' => 'Barbados',
            'BD' => 'Bangladesh',
            'BE' => 'Belgium',
            'BF' => 'Burkina Faso',
            'BG' => 'Bulgaria',
            'BH' => 'Bahrain',
            'BI' => 'Burundi',
            'BJ' => 'Benin',
            'BL' => 'Saint Barthélemy',
            'BM' => 'Bermuda',
            'BN' => 'Brunei',
            'BO' => 'Bolivia',
            'BR' => 'Brazil',
            'BS' => 'Bahamas',
            'BT' => 'Bhutan',
            'BV' => 'Bouvet Island',
            'BW' => 'Botswana',
            'BY' => 'Belarus',
            'BZ' => 'Belize',
            'CA' => 'Canada',
            'CC' => 'Cocos Keeling Islands',
            'CD' => 'Congo Kinshasa',
            'CF' => 'Central African Republic',
            'CG' => 'Congo Brazzaville',
            'CH' => 'Switzerland',
            'CI' => 'Ivory Coast',
            'CK' => 'Cook Islands',
            'CL' => 'Chile',
            'CM' => 'Cameroon',
            'CN' => 'China',
            'CO' => 'Colombia',
            'CR' => 'Costa Rica',
            'CU' => 'Cuba',
            'CV' => 'Cape Verde',
            'CX' => 'Christmas Island',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DE' => Yii::t('app/iso', 'Germany'),
            'DJ' => 'Djibouti',
            'DK' => 'Denmark',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'DZ' => 'Algeria',
            'EC' => 'Ecuador',
            'EE' => 'Estonia',
            'EG' => 'Egypt',
            'EH' => 'Western Sahara',
            'ER' => 'Eritrea',
            'ES' => 'Spain',
            'ET' => 'Ethiopia',
            'FI' => 'Finland',
            'FJ' => 'Fiji',
            'FK' => 'Falkland Islands',
            'FM' => 'Micronesia',
            'FO' => 'Faroe Islands',
            'FR' => 'France',
            'GA' => 'Gabon',
            'GB' => 'United Kingdom',
            'GD' => 'Grenada',
            'GE' => 'Georgia',
            'GF' => 'French Guiana',
            'GG' => 'Guernsey',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GL' => 'Greenland',
            'GM' => 'Gambia',
            'GN' => 'Guinea',
            'GP' => 'Guadeloupe',
            'GQ' => 'Equatorial Guinea',
            'GR' => 'Greece',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'GT' => 'Guatemala',
            'GU' => 'Guam',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HK' => 'Hong Kong S.A.R., China',
            'HM' => 'Heard Island and McDonald Islands',
            'HN' => 'Honduras',
            'HR' => 'Croatia',
            'HT' => 'Haiti',
            'HU' => 'Hungary',
            'ID' => 'Indonesia',
            'IE' => 'Ireland',
            'IL' => 'Israel',
            'IM' => 'Isle of Man',
            'IN' => 'India',
            'IO' => 'British Indian Ocean Territory',
            'IQ' => 'Iraq',
            'IR' => 'Iran',
            'IS' => 'Iceland',
            'IT' => 'Italy',
            'JE' => 'Jersey',
            'JM' => 'Jamaica',
            'JO' => 'Jordan',
            'JP' => 'Japan',
            'KE' => 'Kenya',
            'KG' => 'Kyrgyzstan',
            'KH' => 'Cambodia',
            'KI' => 'Kiribati',
            'KM' => 'Comoros',
            'KN' => 'Saint Kitts and Nevis',
            'KP' => 'North Korea',
            'KR' => 'South Korea',
            'KW' => 'Kuwait',
            'KY' => 'Cayman Islands',
            'KZ' => 'Kazakhstan',
            'LA' => 'Laos',
            'LB' => 'Lebanon',
            'LC' => 'Saint Lucia',
            'LI' => 'Liechtenstein',
            'LK' => 'Sri Lanka',
            'LR' => 'Liberia',
            'LS' => 'Lesotho',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'LV' => 'Latvia',
            'LY' => 'Libya',
            'MA' => 'Morocco',
            'MC' => 'Monaco',
            'MD' => 'Moldova',
            'ME' => 'Montenegro',
            'MF' => 'Saint Martin French part',
            'MG' => 'Madagascar',
            'MH' => 'Marshall Islands',
            'MK' => 'Macedonia',
            'ML' => 'Mali',
            'MM' => 'Myanmar',
            'MN' => 'Mongolia',
            'MO' => 'Macao S.A.R., China',
            'MP' => 'Northern Mariana Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MS' => 'Montserrat',
            'MT' => 'Malta',
            'MU' => 'Mauritius',
            'MV' => 'Maldives',
            'MW' => 'Malawi',
            'MX' => 'Mexico',
            'MY' => 'Malaysia',
            'MZ' => 'Mozambique',
            'NA' => 'Namibia',
            'NC' => 'New Caledonia',
            'NE' => 'Niger',
            'NF' => 'Norfolk Island',
            'NG' => 'Nigeria',
            'NI' => 'Nicaragua',
            'NL' => 'Netherlands',
            'NO' => 'Norway',
            'NP' => 'Nepal',
            'NR' => 'Nauru',
            'NU' => 'Niue',
            'NZ' => 'New Zealand',
            'OM' => 'Oman',
            'PA' => 'Panama',
            'PE' => 'Peru',
            'PF' => 'French Polynesia',
            'PG' => 'Papua New Guinea',
            'PH' => 'Philippines',
            'PK' => 'Pakistan',
            'PL' => 'Poland',
            'PM' => 'Saint Pierre and Miquelon',
            'PN' => 'Pitcairn',
            'PR' => 'Puerto Rico',
            'PS' => 'Palestinian Territory',
            'PT' => 'Portugal',
            'PW' => 'Palau',
            'PY' => 'Paraguay',
            'QA' => 'Qatar',
            'RE' => 'Reunion',
            'RO' => 'Romania',
            'RS' => 'Serbia',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'SA' => 'Saudi Arabia',
            'SB' => 'Solomon Islands',
            'SC' => 'Seychelles',
            'SD' => 'Sudan',
            'SE' => 'Sweden',
            'SG' => 'Singapore',
            'SH' => 'Saint Helena',
            'SI' => 'Slovenia',
            'SJ' => 'Svalbard and Jan Mayen',
            'SK' => 'Slovakia',
            'SL' => 'Sierra Leone',
            'SM' => 'San Marino',
            'SN' => 'Senegal',
            'SO' => 'Somalia',
            'SR' => 'Suriname',
            'ST' => 'Sao Tome and Principe',
            'SV' => 'El Salvador',
            'SY' => 'Syria',
            'SZ' => 'Swaziland',
            'TC' => 'Turks and Caicos Islands',
            'TD' => 'Chad',
            'TF' => 'French Southern Territories',
            'TG' => 'Togo',
            'TH' => 'Thailand',
            'TJ' => 'Tajikistan',
            'TK' => 'Tokelau',
            'TL' => 'Timor-Leste',
            'TM' => 'Turkmenistan',
            'TN' => 'Tunisia',
            'TO' => 'Tonga',
            'TR' => 'Turkey',
            'TT' => 'Trinidad and Tobago',
            'TV' => 'Tuvalu',
            'TW' => 'Taiwan',
            'TZ' => 'Tanzania',
            'UA' => 'Ukraine',
            'UG' => 'Uganda',
            'UM' => 'United States Minor Outlying Islands',
            'US' => 'United States',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VA' => 'Vatican',
            'VC' => 'Saint Vincent and the Grenadines',
            'VE' => 'Venezuela',
            'VG' => 'British Virgin Islands',
            'VI' => 'U.S. Virgin Islands',
            'VN' => 'Vietnam',
            'VU' => 'Vanuatu',
            'WF' => 'Wallis and Futuna',
            'WS' => 'Samoa',
            'YE' => 'Yemen',
            'YT' => 'Mayotte',
            'ZA' => 'South Africa',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        );

        // Sort the list.
        //natcasesort($countries);

        return $countries;
    }

}
