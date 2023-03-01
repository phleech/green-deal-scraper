<?php

set_time_limit(600);

require "vendor/autoload.php";
use PHPHtmlParser\Dom;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

const COMPANY_INSTALLERS_ONLY = 2;

const SERVICE_AIR_SOURCE_HEAT_PUMPS = 186;
const SERVICE_BIOMASS_BOILERS = 86;
const SERVICE_BIOMASS_ROOM_HEATERS = 87;
const SERVICE_CAVITY_WALL_INSULATION = 88;
const SERVICE_CHILLERS = 89;
const SERVICE_CYLINDER_THERMOSTATS = 90;
const SERVICE_DRAUGHT_PROOFING = 91;
const SERVICE_DUCT_INSULATION = 92;
const SERVICE_EXTERNAL_WALL_INSULATION_SYSTEMS = 93;
const SERVICE_FAN_ASSISTED_REPLACEMENT_STORAGE_HEATERS = 94;
const SERVICE_FLUE_GAS_HEAT_RECOVERY_DEVICES = 95;
const SERVICE_GAS_FIRED_CONDENSING_BOILERS = 96;
const SERVICE_GROUND_SOURCE_HEAT_PUMPS = 97;
const SERVICE_HEATING_CONTROLS = 98;
const SERVICE_HEATING_VENTILATION_AND_AC_CONTROLS = 99;
const SERVICE_HIGH_PERFORMANCE_EXTERNAL_DOORS = 100;
const SERVICE_HOT_WATER_CONTROLS = 101;
const SERVICE_HOT_WATER_CYLINDER_INSULATION = 102;
const SERVICE_HOT_WATER_SHOWERS = 103;
const SERVICE_HOT_WATER_SYSTEMES = 104;
const SERVICE_HOT_WATER_TAPS = 105;
const SERVICE_INTERNAL_WALL_INSULATION_SYSTEMS = 106;
const SERVICE_LIGHTING_SYSTEMS_FITTINGS_AND_CONTROLS = 107;
const SERVICE_LOFT_OR_RAFTER_INSULATION = 108;
const SERVICE_MECHANICAL_VENTILATION_WITH_HEAT_RECOVERY = 109;
const SERVICE_MICRO_COMBINED_HEAT_AND_POWER = 110;
const SERVICE_MICRO_WIND_GENERATION = 111;
const SERVICE_OIL_FILED_CONDENSING_BOILERS = 112;
const SERVICE_PHOTOVOLTAICS = 113;
const SERVICE_PIPE_WORK_INSULATION = 114;
const SERVICE_RADIANT_HEATING = 115;
const SERVICE_REPLACEMENT_GLAZING = 116;
const SERVICE_ROOF_INSULATION = 117;
const SERVICE_ROOM_IN_ROOF_INSULATION = 118;
const SERVICE_SEALING_IMPROVEMENTS = 119;
const SERVICE_SECONDARY_GLAZING = 120;
const SERVICE_SOLAR_BLINDS_SHUTTERS_AND_SHADING_DEVICES = 121;
const SERVICE_SOLAR_WATER_HEATING = 122;
const SERVICE_TRANSPIRED_SOLAR_COLLECTORS = 123;
const SERVICE_UNDER_FLOOR_HEATING = 124;
const SERVICE_UNDER_FLOOR_INSULATION = 125;
const SERVICE_VARIABLE_SPEED_DEVICES_FOR_FANS_AND_PUMPS = 256;
const SERVICE_WARM_AIR_UNITS = 257;
const SERVICE_WASTE_WATER_RECOVERY_DEVICES_ATTACHED_TO_SHOWERS = 128;
const SERVICE_WATER_SOURCE_HEAT_PUMPS = 129;

$client = new Client();
$request = new Request(
    'POST',
    'https://gdorb.beis.gov.uk/green-deal-participant-register/?limit=1000&order_list=alpha',
    [
        'Content-Type' => 'application/x-www-form-urlencoded',
    ],
    http_build_query([
        'company-type' => COMPANY_INSTALLERS_ONLY,
        'singleService' => [
            SERVICE_WATER_SOURCE_HEAT_PUMPS
        ],
        'location' => null,
        'miles' => 10,
        'certification' => null,
        'keyword' => null,
        'search-page' => 1,
    ])
);

$dom = new Dom();
$dom->loadFromUrl('', null, $client, $request);
$rows = $dom->find('.mcsResultsTable > tbody > tr');
$ids = [];

foreach ($rows as $row) {
    $link = $row->find('td')[0]->find('a')->getAttribute('href');
    //?p=393&installer_id=4093

    $matches = [];
    preg_match('/installer_id=(\d+)/', $link, $matches);
    $ids[] = $matches[1];
}

$data = [];

foreach($ids as $id) {
    $item = ['url' => "https://gdorb.beis.gov.uk/installer-profile/?installer_id=$id"];
    $dom = new Dom();

    try {
        $dom->loadFromUrl($item['url']);
    } catch (ConnectException $e) {
        //rarely fails, but when it does then try once more
        $dom->loadFromUrl($item['url']);
    }

    $html = $dom->find('.mcsColumnsTwoOne')[0];

    $elements = $html->find('h1, p');

    foreach ($elements as $el) {
        $tag = $el->getTag()->name();

        if ($tag === 'h1') {
           $item['name'] = $el->text;
        }

        if ($tag === 'p') {
            if (strpos($el->text, 'Telephone: ') === 0) {
                $item['phone'] = substr($el->text, 11);
            }

            if (strpos($el->text, 'Email: ') === 0) {
                $item['email'] = $el->find('a')[0]->text;
            }

            if (count($el->find('br')) && empty($item['address'])) {
                $item['address'] = str_replace('<br />', ', ', $el->innerHtml);
            }
        }
    }

    $data[] = $item;
}

?>

<html>
    <head>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
    </style>
    </head>
    <body>
        <table>
            <thead>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Address</th>
                <th>Link</th>
            </thead>
            <tbody>
            <?php foreach ($data as $row) { ?>
                <tr>
                    <td><?=$row['name']?></td>
                    <td><?=$row['phone']?></td>
                    <td><?=$row['email']?></td>
                    <td><?=$row['address']?></td>
                    <td><?=$row['url']?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </body>
</html>

