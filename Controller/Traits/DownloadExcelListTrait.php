<?php

namespace Trendix\AdminBundle\Controller\Traits;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

trait DownloadExcelListTrait
{
    /**
     * @Route("/download/list/", defaults={"id": 0}, name="download_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadListFile(Request $request)
    {
        // First, we need to get the data
        $form = $request->request->all();
        $parameters = explode('/;/', $form['call']);
        $class = $form['class'];
        $title = substr($this->get('translator')->trans($form['title']), 0, 30);
        $reflect  = new \ReflectionClass($class);
        $instance = $reflect->newInstanceArgs();
        $options = json_decode($form['options'], true);
        $list = $this->createList($instance, $options);
        $data = $list->getFileData();

        // Then, we have to create the excel object:
        $phpExcelObject = new Spreadsheet();

        // Writing file meta-data:
        $phpExcelObject->getProperties()->setCreator("Trendix")
            ->setLastModifiedBy("Trendix")
            ->setTitle($title)
            ->setSubject($title)
            ->setDescription($title)
            ->setKeywords($title);
        $phpExcelObject->setActiveSheetIndex(0);
        $phpExcelObject->getActiveSheet()->setTitle($title);

        // Now we run through the results to create the table:
        // First, the labels:
        $i = 1;
        foreach($data['labels'] as $col) {
            $colName =  $this->get('translator')->trans($col);
            $phpExcelObject->getActiveSheet()->setCellValue($this->numToLetter($i).'1', $colName);
            $i++;
        }
        // Then, the values
        $i = 2;
        foreach($data['data'] as $row) {
            $j = 1;
            foreach($row as $key => $cell) {
                if(!is_array($cell) && $key != 'original_id' && $key != 'actions') {
                    if(strstr($cell, 'zmdi-close') !== false) {
                        $cell = $this->get('translator')->trans('admin.no');
                    } elseif(strstr($cell, 'zmdi-check') !== false) {
                        $cell = $this->get('translator')->trans('admin.yes');
                    }
                    $phpExcelObject->getActiveSheet()->setCellValue($this->numToLetter($j).$i, $cell);
                    $j++;
                }
            }
            $i++;
        }

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = new Xlsx($phpExcelObject);
        // create the response
        $fileName = 'list.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);

        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /** Get the column name from the position
     * @param $num
     * @return mixed
     */
    private function numToLetter($num) {
        $alphabet = array( 'a', 'b', 'c', 'd', 'e',
            'f', 'g', 'h', 'i', 'j',
            'k', 'l', 'm', 'n', 'o',
            'p', 'q', 'r', 's', 't',
            'u', 'v', 'w', 'x', 'y',
            'z'
        );
        return $alphabet[$num-1];
    }
}