<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 25/06/2016
 * Time: 6:24 PM
 */

namespace GroupBot\Brains\Weather\Radar;


class Radar_Codes
{
    public static $image_ranges = [
        1 => '512km',
        2 => '256km',
        3 => '128km',
        4 => '64km',
    ];

    public static $radar_codes = [
        ['02', "Melbourne (Laverton)", "Vic"],
        ['03', "Wollongong (Appin)", "NSW"],
        ['04', "Newcastle", "NSW"],
        ['05', "Carnarvon", "WA"],
        ['06', "Geraldton", "WA"],
        ['07', "Wyndham", "WA"],
        ['08', "Gympie (Mt Kanigan)", "Qld"],
        ['09', "Gove", "NT"],
        ['10', "Darwin (Airport)", "NT", true],
        ['14', "Mount Gambier", "SA"],
        ['15', "Dampier", "WA"],
        ['16', "Port Hedland", "WA"],
        ['17', "Broome", "WA"],
        ['18', "Weipa", "Qld"],
        ['19', "Cairns", "Qld"],
        ['22', "Mackay", "Qld"],
        ['23', "Gladstone", "Qld"],
        ['24', "Bowen", "Qld"],
        ['25', "Alice Springs", "NT"],
        ['26', "Perth (Airport)", "WA*"],
        ['27', "Woomera", "SA"],
        ['28', "Grafton", "NSW"],
        ['29', "Learmonth", "WA"],
        ['30', "Mildura", "Vic"],
        ['31', "Albany", "WA"],
        ['32', "Esperance", "WA"],
        ['33', "Ceduna", "SA"],
        ['34', "Cairns (Airport)", "Qld", true],
        ['36', "Mornington Island (Gulf of Carpentaria)", "Qld"],
        ['37', "Hobart (Airport)", "Tas", true],
        ['39', "Halls Creek", "WA"],
        ['40', "Canberra (Captains Flat)", "NSW"],
        ['41', "Willis Island", "Qld"],
        ['42', "Katherine (Tindal)", "NT"],
        ['44', "Giles", "WA"],
        ['46', "Adelaide (Sellick’s Hill)", "SA"],
        ['48', "Kalgoorlie", "WA"],
        ['49', "Yarrawonga", "Vic"],
        ['50', "Brisbane (Marburg)", "Qld"],
        ['51', "Melbourne (Airport)", "Vic", true],
        ['52', "Northwest Tasmania (West Takone)", "Tas"],
        ['53', "Moree", "NSW"],
        ['55', "Wagga Wagga", "NSW"],
        ['56', "Longreach", "Qld"],
        ['62', "Norfolk Island", ""],
        ['63', "Darwin (Berrimah)", "NT"],
        ['64', "Adelaide (Buckland Park)", "SA"],
        ['65', "Tennant Creek", "NT"],
        ['66', "Brisbane (Mount Stapylton)", "Qld"],
        ['67', "Warrego", "Qld"],
        ['68', "Bairnsdale", "Vic"],
        ['69', "Namoi", "NSW"],
        ['70', "Perth", "WA"],
        ['71', "Sydney (Terrey Hills)", "NSW"],
        ['72', "Emerald", "Qld"],
        ['73', "Townsville", "Qld"],
        ['75', "Mount Isa", "Qld"],
        ['76', "Hobart (Mount Koonya)", "Tas"],
        ['77', "Warruwi", "NT"]
    ];
}
