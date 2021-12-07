<?php
require_once DIR_SYSTEM . 'storage/vendor/autoload.php';
use \PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ModelToolExcel extends Model {
    public function import($file) {
        $file_extension = ucfirst(utf8_strtolower(utf8_substr(strrchr($file, '.'), 1)));

        $reader = IOFactory::createReader($file_extension);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        return $rows;
    }

    public function export($file, $data, $style=array()){
        /**
         * style: [
         *      {
         *          selector: 'B3:B7',
         *          styleArray: array()
         *      }
         * ]
         * 
         * styleArray: {
         *      font: {
         *          bold: true
         *      }
         * }
         */

        $spreadsheet = new Spreadsheet();
        
        $sheet = $spreadsheet->getActiveSheet();
        
        foreach($style as $s){
            $sheet->getStyle($s['selector'])->applyFromArray($s['styleArray']);
        }

        $sheet->fromArray($data, NULL, 'A1');

        $writer = new Xlsx($spreadsheet);
        $writer->save($file);
    }
}
?>