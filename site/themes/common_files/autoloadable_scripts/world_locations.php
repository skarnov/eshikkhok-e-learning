<?php
/**
 * Area, City, Division/State, Country
 *
 * Area
 *  City
 *  Division/State
 *  Country
 *
 * City
 *  Division/State
 *  Country
 *
 * Division/State
 *  Country
 *
 * Database Structure
 *      pk_loc_id
 *      Area            NULL
 *      city            NULL
 *      State           NULL
 *      fk_city_id      0
 *      fk_state_id     0
 *      fk_country_id   0
 *
 * 1, NULL, NULL, Dhaka, 0, 0, BD
 * 2, NULL, Dhaka, NULL, 0, 1, BD
 * 3, Mirpur, NULL, NULL, 2, 1, BD
 */

$areas = array(
    'Adabor'
    ,'Mirpur'
    ,'Uttara'
    );
$cities = array();
$states = array();

$locations = array(
    array(
        'area' => '',
        'city' => '',
        'state' => '',
        'fk_city' => '',
        'fk_state' => '',
        'fk_country' => ''
        ),
    );

//---
$states = array(
    'BD' => array(
        'DHK' => 'Dhaka',
        'CTG' => 'Chittagong',
        'RAJ' => 'Rajshahi',
        'SYL' => 'Sylhet',
        'BAR' => 'Barisal',
        'KHUL' => 'Khulna',
        'COM' => 'Comilla',
        'RANG' => 'Rangpur',
        ),
    );
$cities = array(
    'DHK' => array(
        'DHAKA' => 'Dhaka',
        ),
    'CTG' => array(
        'CHITTAGONG' => 'Chittagong',
        ),
    );
$areas = array(
    'DHAKA' => array(
        '1' => 'ADABOR',
        '2' => 'AGARGAON',
        '3' => 'BADDA',
        '4' => 'AIRPORT',
        '5' => 'BANGSHAL',
        '6' => 'CANTONMENT',
        '7' => 'CHAWKBAZAR MODEL',
        '8' => 'DAKSHINKHAN',
        '9' => 'DARUS SALAM',
        '10' => 'DHANMONDI',
        '11' => 'DEMRA',
        '12' => 'KOTWALI',
        '13' => 'GENDARIA',
        '14' => 'GULSHAN',
        '15' => 'HAZARIBAGH',
        '16' => 'JATRABARI',
        '17' => 'KADAMTALI',
        '18' => 'KAFRUL',
        '19' => 'KALABAGAN',
        '20' => 'KAMRINGIR CHAR',
        '21' => 'KHILKHET',
        '22' => 'KHILGAON',
        '23' => 'LALBAGH',
        '24' => 'MIRPUR',
        '25' => 'MOHAMMADPUR',
        '26' => 'MOTIJHEEL',
        '27' => 'NEW MARKET',
        '28' => 'PALLABI',
        '29' => 'PALTAN',
        '30' => 'RAMNA',
        '31' => 'RAMPURA',
        '32' => 'SABUJHBAGH',
        '33' => 'SHAH ALI',
        '34' => 'SHAHBAGH',
        '35' => 'SHER-E-BANGLA NAGOR',
        '36' => 'SHYAMPUR',
        '37' => 'SUTRAPUR',
        '38' => 'TEJGAON',
        '39' => 'TEJGAON INDUSTRIAL AREA',
        '40' => 'TURAG',
        '41' => 'UTTAR KHAN',
        '42' => 'UTTARA',
        ),
    );