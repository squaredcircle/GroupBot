<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 11:17 PM
 */
namespace GroupBot\Command;


use GroupBot\Libraries\Converter;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class convert extends Command
{
    private $currencies = array (
        'ALL' => 'Albania Lek',
        'AFN' => 'Afghanistan Afghani',
        'ARS' => 'Argentina Peso',
        'AWG' => 'Aruba Guilder',
        'AUD' => 'Australia Dollar',
        'AZN' => 'Azerbaijan New Manat',
        'BSD' => 'Bahamas Dollar',
        'BBD' => 'Barbados Dollar',
        'BDT' => 'Bangladeshi taka',
        'BYR' => 'Belarus Ruble',
        'BZD' => 'Belize Dollar',
        'BMD' => 'Bermuda Dollar',
        'BOB' => 'Bolivia Boliviano',
        'BAM' => 'Bosnia and Herzegovina Convertible Marka',
        'BWP' => 'Botswana Pula',
        'BGN' => 'Bulgaria Lev',
        'BRL' => 'Brazil Real',
        'BND' => 'Brunei Darussalam Dollar',
        'KHR' => 'Cambodia Riel',
        'CAD' => 'Canada Dollar',
        'KYD' => 'Cayman Islands Dollar',
        'CLP' => 'Chile Peso',
        'CNY' => 'China Yuan Renminbi',
        'COP' => 'Colombia Peso',
        'CRC' => 'Costa Rica Colon',
        'HRK' => 'Croatia Kuna',
        'CUP' => 'Cuba Peso',
        'CZK' => 'Czech Republic Koruna',
        'DKK' => 'Denmark Krone',
        'DOP' => 'Dominican Republic Peso',
        'XCD' => 'East Caribbean Dollar',
        'EGP' => 'Egypt Pound',
        'SVC' => 'El Salvador Colon',
        'EUR' => 'Euro Member Countries',
        'FKP' => 'Falkland Islands (Malvinas) Pound',
        'FJD' => 'Fiji Dollar',
        'GHC' => 'Ghana Cedis',
        'GIP' => 'Gibraltar Pound',
        'GTQ' => 'Guatemala Quetzal',
        'GGP' => 'Guernsey Pound',
        'GYD' => 'Guyana Dollar',
        'HNL' => 'Honduras Lempira',
        'HKD' => 'Hong Kong Dollar',
        'HUF' => 'Hungary Forint',
        'ISK' => 'Iceland Krona',
        'INR' => 'India Rupee',
        'IDR' => 'Indonesia Rupiah',
        'IRR' => 'Iran Rial',
        'IMP' => 'Isle of Man Pound',
        'ILS' => 'Israel Shekel',
        'JMD' => 'Jamaica Dollar',
        'JPY' => 'Japan Yen',
        'JEP' => 'Jersey Pound',
        'KZT' => 'Kazakhstan Tenge',
        'KPW' => 'Korea (North) Won',
        'KRW' => 'Korea (South) Won',
        'KGS' => 'Kyrgyzstan Som',
        'LAK' => 'Laos Kip',
        'LBP' => 'Lebanon Pound',
        'LRD' => 'Liberia Dollar',
        'MKD' => 'Macedonia Denar',
        'MYR' => 'Malaysia Ringgit',
        'MUR' => 'Mauritius Rupee',
        'MXN' => 'Mexico Peso',
        'MNT' => 'Mongolia Tughrik',
        'MZN' => 'Mozambique Metical',
        'NAD' => 'Namibia Dollar',
        'NPR' => 'Nepal Rupee',
        'ANG' => 'Netherlands Antilles Guilder',
        'NZD' => 'New Zealand Dollar',
        'NIO' => 'Nicaragua Cordoba',
        'NGN' => 'Nigeria Naira',
        'NOK' => 'Norway Krone',
        'OMR' => 'Oman Rial',
        'PKR' => 'Pakistan Rupee',
        'PAB' => 'Panama Balboa',
        'PYG' => 'Paraguay Guarani',
        'PEN' => 'Peru Nuevo Sol',
        'PHP' => 'Philippines Peso',
        'PLN' => 'Poland Zloty',
        'QAR' => 'Qatar Riyal',
        'RON' => 'Romania New Leu',
        'RUB' => 'Russia Ruble',
        'SHP' => 'Saint Helena Pound',
        'SAR' => 'Saudi Arabia Riyal',
        'RSD' => 'Serbia Dinar',
        'SCR' => 'Seychelles Rupee',
        'SGD' => 'Singapore Dollar',
        'SBD' => 'Solomon Islands Dollar',
        'SOS' => 'Somalia Shilling',
        'ZAR' => 'South Africa Rand',
        'LKR' => 'Sri Lanka Rupee',
        'SEK' => 'Sweden Krona',
        'CHF' => 'Switzerland Franc',
        'SRD' => 'Suriname Dollar',
        'SYP' => 'Syria Pound',
        'TWD' => 'Taiwan New Dollar',
        'THB' => 'Thailand Baht',
        'TTD' => 'Trinidad and Tobago Dollar',
        'TRY' => 'Turkey Lira',
        'TRL' => 'Turkey Lira',
        'TVD' => 'Tuvalu Dollar',
        'UAH' => 'Ukraine Hryvna',
        'GBP' => 'United Kingdom Pound',
        'UGX' => 'Uganda Shilling',
        'USD' => 'United States Dollar',
        'UYU' => 'Uruguay Peso',
        'UZS' => 'Uzbekistan Som',
        'VEF' => 'Venezuela Bolivar',
        'VND' => 'Viet Nam Dong',
        'YER' => 'Yemen Rial',
        'ZWD' => 'Zimbabwe Dollar'
    );

    /** @var  Converter */
    private $Converter;

    /** @var  string */
    private $out;

    private $from, $to, $number;

    private function help()
    {
        $this->out .= "✍ Like this fam: "
            . "\n"
            . "\n   • `/convert` *2ft m*"
            . "\n   • `/convert` *90 deg rad*"
            . "\n   • `/convert` *5 lb*"
            . "\n"
            . "\nThe default conversion is to SI."
            . "\nYou can see all the compatible units [here](https://github.com/olifolkerd/convertor)";
        Telegram::talk($this->Message->Chat->id, $this->out);
        return true;
    }

    private function getSI($to_units)
    {
        $SI_units = ['m', 'm2', 'l', 'kg', 'mps', 'rad', 'c', 'pa', 's', 'j'];

        foreach ($SI_units as $unit) {
            if (in_array($unit, $to_units)) return $unit;
        }
        return false;
    }

    private function currency($from_amount, $from, $to)
    {
        $url = 'http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s='. $from . $to .'=X';

        if ($filehandler = @fopen($url, 'r')) {
            $data = fgets($filehandler, 4096);
            fclose($filehandler);
            $InfoData = explode(',', $data);
            $to_conversion = $InfoData[1];
            return $to_conversion * $from_amount;
        }
        return false;
    }

    private function isCurrency($unit)
    {
        return array_key_exists($unit, $this->currencies);
    }

    private function checkIfCurrencyConversion($number, $from, $to = null)
    {

    }

    private function checkIfSIConversion($number, $from, $to = null)
    {
        if (is_numeric($number)) {
            $this->Converter = new Converter();
            try {
                $to_units = $this->Converter->getUnits($from);
            } catch (\Exception $e) {
                {
                    $this->out = "Can't find that unit m8.\n\n";
                    $this->help();
                    return false;
                }
            }
            if (!isset($to)) {
                $to = $this->getSI($to_units);
                if (!isset($to)) {
                    $this->out = "Can't find that unit m8.\n\n";
                } else {
                    return true;
                }
            } else if (!in_array($to, $to_units)) {
                $this->out = "Can't find that unit m8.\n\n";
            }
        }
        $this->help();
        return false;
    }

    public function main()
    {
        $this->out = '';

        if ($this->noParams() == 3)
        {
            $number = $this->getParam();
            $from = $this->getParam(1);
            $to = $this->getParam(2);
        }
        elseif ($this->noParams() == 2)
        {
            $arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$this->getParam());
            $number = $arr[0];
            $from = $arr[1];
            $to = $this->getParam(1);
        }
        elseif ($this->isParam())
        {
            $arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$this->getParam());
            $number = $arr[0];
            $from = $arr[1];
        }
        else
        {
            return $this->help();
        }

        if ($this->checkIfSIConversion($number, $from, $to))
        {
            try {
                $this->Converter = new Converter($number, $from);
                $result = $this->Converter->to($to);
            } catch (\Exception $e) {
                $this->out = $e . "\n\n";
                return $this->help();
            }
            $this->out = "`$result` *$to*";
        }
        elseif ($this->checkIfCurrencyConversion($number, $from, $to))
        {

        }

        Telegram::talk($this->Message->Chat->id, $this->out);
        return true;
    }
}