<?php

namespace App\Core;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;
use PhpOffice\PhpSpreadsheet\Writer\Exception as PhpSpreadsheetWriterException;

/**
 * Class BillingReport
 * @package App\Core\Report\Billing
 */
class PublicEventBillingReport
{
    /** @var string|null */
    protected static $dateFrom = null;
    protected static $dateTo = null;
    protected static $result = [];

    /**
     * @throws PhpSpreadsheetException
     * @throws PhpSpreadsheetWriterException
     */
    public static function makePublicEventReport($orders, $merchant, $dates)
    {
        self::$dateFrom = $dates['date_from'];
        self::$dateTo = $dates['date_to'];

        $pathToTemplate = resource_path('templates/publicEventBillingReport.xlsx.template');
        $spreadsheet = IOFactory::load($pathToTemplate);
        $worksheet = $spreadsheet->setActiveSheetIndex(0);

        $worksheet->setTitle('Отчет Агента');

        self::header($worksheet, $merchant);
        self::body($worksheet, $orders);


        $name = self::$dateFrom . '_' . self::$dateTo . '_' . $merchant->code . '-iBT_event-billing.xlsx';

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($name) . '"');
        $writer->save('php://output');
    }

    /**
     * Возвращает сумму прописью
     * @param $num
     * @return string
     * @uses morph(...)
     */
    protected static function num2str($num): string
    {
        $nul = 'ноль';
        $ten = [
            ['','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'],
            ['','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'],
        ];
        $a20 = ['десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать'];
        $tens = [2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто'];
        $hundred = ['', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот'];
        $unit = [ // Units
            ['копейка' ,'копейки' ,'копеек', 1],
            ['рубль' ,'рубля' ,'рублей' ,0],
            ['тысяча' ,'тысячи' ,'тысяч' ,1],
            ['миллион' ,'миллиона','миллионов' ,0],
            ['миллиард','миллиарда','миллиардов',0],
        ];
        //
        [$rub, $kop] = explode('.', sprintf("%015.2f", floatval($num)));
        $out = [];
        if (intval($rub) > 0) {
            foreach (str_split($rub, 3) as $uk => $v) { // by 3 symbols
                if (!intval($v)) {
                    continue;
                }
                $uk = sizeof($unit) - $uk - 1; // unit key
                $gender = $unit[$uk][3];
                [$i1, $i2, $i3] = array_map('intval', str_split($v, 1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2 > 1) {
                    $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3]; # 20-99
                } else {
                    $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                }
                // units without rub & kop
                if ($uk > 1) {
                    $out[] = self::morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
                }
            } //foreach
        } else {
            $out[] = $nul;
        }
        $out[] = self::morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub
        $out[] = $kop . ' ' . self::morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop
        return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
    }

    /**
     * Склоняем словоформу
     * @param $n
     * @param $f1
     * @param $f2
     * @param $f5
     * @return mixed
     */
    protected static function morph($n, $f1, $f2, $f5)
    {
        $n = abs(intval($n)) % 100;
        if ($n > 10 && $n < 20) {
            return $f5;
        }
        $n %= 10;
        if ($n > 1 && $n < 5) {
            return $f2;
        }
        if ($n == 1) {
            return $f1;
        }

        return $f5;
    }

    /**
     * @param Worksheet $sheet
     *
     * @param $merchant
     * @throws PhpSpreadsheetException
     */
    protected static function header(Worksheet $sheet, $merchant)
    {
        $contractDate = self::prepareDate($merchant->contract_at);
        $topHeaderText = 'к Договору № ' . $merchant->contract_number . ' от ' . $contractDate;

        $subTitleText = 'ОТЧЕТ АГЕНТА №____ ';
        $betweenDatesText = 'по агентскому договору № ' . $merchant->contract_number . ' от ' . $contractDate;
        $reportDateText = self::prepareDate(date('Y-m-d'));

        $headerText = 'Во исполнение Агентского Договора № ' . $merchant->contract_number . ' от ' . $contractDate . ' («Договор»), ';
        $headerText .= 'заключенного между ' . $merchant->legal_name . ', именуемого в дальнейшем «Принципал», ';
        $headerText .= 'и Общество с ограниченной ответственностью "Бессовестно Талантливый" ("Комиссионер") ';
        $headerText .= 'в лице Генерального директора Адельфинской Яны Юрьевны, действующей на основании Устава, именуемого в дальнейшем «Агент», ';
        $headerText .= 'Агент предоставляет Принципалу настоящий Отчет Агента.';

        $sheet->getCell('A2')->setValue($topHeaderText);
        $sheet->getCell('D9')->setValue($subTitleText);
        $sheet->getCell('E10')->setValue($betweenDatesText);
        $sheet->getCell('I12')->setValue($reportDateText);
        $sheet->getCell('A6')->setValue($headerText);
    }

    /**
     * @param Worksheet $sheet
     * @param $orders
     * @throws PhpSpreadsheetException
     */
    protected static function body(Worksheet $sheet, $orders)
    {
        $startBillingRow = $billingTableRow = 16; //Начальная строка списка проданных товаров
//        $sumSells = $sumReward = $sumToPrincipal = 0;

        //Заполняем таблицу c продажами//

        if (count($orders) > 0) {
            //Вставляем в шаблон необходимое количество строк
            $sheet->insertNewRowBefore($startBillingRow + 1, count($orders));
            $num = 1;
            foreach ($orders as $operation) {
                $billingDate = date('d-m-Y', strtotime($operation['created_at'])); //A:B
                $eventNamesArr = [];
                foreach ($operation['basket']['items'] as $item) {
                    $eventNamesArr[] = $item->name;
                }
                $eventNames = implode(', ', $eventNamesArr);
                $pricePerUnit = $operation['count_tickets'] > 0 ? $operation['price'] / $operation['count_tickets'] : 0; //F
                $rewardPercent = 0.08;
                $reward = $operation['price'] * $rewardPercent;
                $toPrincipal = $operation['price'] - $reward;

                $sheet->getCell('A' . $billingTableRow)->setValue($num);
                $sheet->getCell('B' . $billingTableRow)->setValue($operation['number']);
                $sheet->getCell('C' . $billingTableRow)->setValue($billingDate);
                $sheet->getCell('D' . $billingTableRow)->setValue($eventNames);
                $sheet->getCell('E' . $billingTableRow)->setValue($operation['count_tickets']);
                $sheet->getCell('F' . $billingTableRow)->setValue($pricePerUnit);
                $sheet->getCell('G' . $billingTableRow)->setValue($operation['price']);

                $sheet->getCell('I' . $billingTableRow)->setValue($rewardPercent);
                $sheet->getCell('J' . $billingTableRow)->setValue('=G' . $billingTableRow . '*I' . $billingTableRow);
                $sheet->getCell('K' . $billingTableRow)->setValue('=G' . $billingTableRow . '-J' . $billingTableRow);

//                $sumSells += $operation['price']; // total in G
                $sumReward += $reward; // total in J
                $sumToPrincipal += $toPrincipal; // total in K

                $billingTableRow++;
                $num++;
            }

            $sheet->removeRow($billingTableRow);
            $lastBillingRow = $billingTableRow - 1;
            $sheet->getCell('G' . $billingTableRow)->setValue('=SUM(G' . $startBillingRow . ':G' . $lastBillingRow . ')');
            $sheet->getCell('J' . $billingTableRow)->setValue('=SUM(J' . $startBillingRow . ':J' . $lastBillingRow . ')');
            $sheet->getCell('K' . $billingTableRow)->setValue('=SUM(K' . $startBillingRow . ':K' . $lastBillingRow . ')');
        }

        $footerRow = $billingTableRow + 5;

        $sheet->getCell('K' . $footerRow)->setValue($sumToPrincipal);
        //$sheet->getCell('S'.$footerRow)->setValue((int) round($totalBillingWithNds).'+'.round($totalCorrectionToMerchant)*(-1).'+'.round($totalBillingCommission)*(-1).'+'.round($totalReturnedToMerchant)*(-1).'+'.$manualCorrections['merchant']*(-1));
        $footerRow++;
        $sheet->getCell('B' . $footerRow)->setValue('Сумма прописью: ' . self::num2str($sumToPrincipal));

        $footerRow += 2;

        $sheet->getCell('K' . $footerRow)->setValue($sumReward);
        $footerRow++;
        $sheet->getCell('B' . $footerRow)->setValue('Сумма прописью: ' . self::num2str($sumReward));
    }

    protected static function prepareDate($date): string
    {
        if (!$date) {
            return 'Н/У';
        }

        $months = [
            '1' => 'ЯНВАРЯ',
            '2' => 'ФЕВРАЛЯ',
            '3' => 'МАРТА',
            '4' => 'АПРЕЛЯ',
            '5' => 'МАЯ',
            '6' => 'ИЮНЯ',
            '7' => 'ИЮЛЯ',
            '8' => 'АВГУСТА',
            '9' => 'СЕНТЯБРЯ',
            '10' => 'ОКТЯБРЯ',
            '11' => 'НОЯБРЯ',
            '12' => 'ДЕКАБРЯ',
        ];

        $date = date_parse($date);
        $month = $months[$date['month']];

        return $date['day'] . ' ' . $month . ' ' . $date['year'] . ' Г.';
    }
}
